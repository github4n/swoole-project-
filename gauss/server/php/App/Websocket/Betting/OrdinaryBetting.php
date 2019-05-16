<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-18
 * Time: 上午9:51
 */

namespace App\Websocket\Betting;
use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;
use App\Websocket\Betting\Common\CheckRuleList;
/*
 * 用户普通投注
 * Betting/OrdinaryBetting  {"period":"20181229632","game_key":"tiktok_fast","rule_list":[{"play_key":"tiktok_any2","number":["tiktok_any2_2","tiktok_any2_3","tiktok_any2_1"],"price":"5","quantity":"3","rebate_rate":"2"}],"mutiple":"1"}
 *
 * Betting/OrdinaryBetting {"game_key":"ladder_fast","rule_list":[{"play_key":"ladder_from","number":["ladder_from_1"],"price":"10","quantity":"1","rebate_rate":"2"}],"period":"20181231404","multiple":"1"}
 */
class OrdinaryBetting extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {

        $params = $context->getData();
        $deal_key = $context->getInfo("DealKey");
        $mysql = $config->__get("data_" . $deal_key);
        $layer_id = $context->getInfo("LayerId");
        $user_id = $context->getInfo("UserId");
        $user_key = $context->getInfo('UserKey');
        $account_name = !empty($context->getInfo('AccountName')) ? $context->getInfo('AccountName') : '';
        $period = isset($params['period']) ? $params['period'] : '';
        $game_key = isset($params['game_key']) ? $params['game_key'] : '';
        $rule_list = isset($params['rule_list']) ? $params['rule_list'] : '';
        $multiple = isset($params['multiple']) ? $params['multiple'] : 1;

        if (empty($period)) {
            $context->reply(['status'=>401,'msg'=>'请选号']);
            return;
        }

        if (empty($game_key)) {
            $context->reply(['status'=>402,'msg'=>'请选号']);
            return;
        }

        if (empty($rule_list)) {
            $context->reply(['status'=>405,'msg'=>'请选号']);
            return;
        }

        if (empty($multiple) || !is_numeric($multiple) || $multiple < 0 || $multiple > 1) {
            $context->reply(['status'=>411,'msg'=>'投注倍数有误']);
            return;
        }

        $lottery_mysql = $config->data_public;
        $mysql_user = $config->data_user;
        //判断该层级的用户余额是否被冻结
        $auth = $context->getInfo("Auth");
        if(!empty($auth)){
            if(in_array("bet_stop",json_decode($auth)) || in_array("balance_freeze",json_decode($auth))){
                $context->reply(["status"=>230,"msg"=>"该账号禁止投注,请联系客服"]);
                return;

            }
        }

        $auth_sql = "select operate_key from layer_permit where layer_id = '$layer_id'";
        $authArray = [];
        foreach ($mysql_user->query($auth_sql) as $row) {
            $authArray = $row['operate_key'];
        }
        if(!empty($authArray)){
            if(in_array("bet_stop",$authArray) || in_array("balance_freeze",$authArray)){
                $context->reply(["status"=>300,"msg"=>"账户余额冻结或者禁止投注,请联系客服"]);
                return;
            }
        }
        
        //检查期号是否在开盘范围内的sql
        $check_period_sql = "SELECT start_time,stop_time FROM lottery_period WHERE game_key='$game_key' AND period= '$period'";

        foreach ($lottery_mysql->query($check_period_sql) as $row) {
            $check_data = $row;
        }
        //查询开盘及封盘时间
        if (!isset($check_data)){
            $context->reply(['status'=>430,'msg'=>'封盘中,请等待']);
            return;
        }
        //校验期号是否在合法范围`
        if (time() < $check_data['start_time']) {
            $context->reply(['status'=>431,'msg'=>'封盘中,请等待']);
            return;
        }

        if (time() > $check_data['stop_time']) {
            $context->reply(['status'=>432,'msg'=>'封盘中,请等待']);
            return;
        }

        //获取最小投注额,最大投注额,最大返点
        $mysqlStaff = $config->data_staff;
        $rebate_sql = "SELECT rebate_max,acceptable FROM lottery_game WHERE game_key='$game_key'";
        $rebate_max = 0;
        $acceptable = 1;
        foreach ($mysqlStaff->query($rebate_sql) as $v) {
            $rebate_max = $v['rebate_max'];
            $acceptable = $v["acceptable"];
        }
        if($acceptable == 0){
            $context->reply(["status"=>440,"msg"=>"该彩票暂停销售"]);
            return;
        }
        //取model_key
        $model_key = substr($game_key,0,strpos($game_key,"_"));
        //投注内容具体参数判断
        $ruleArray = [];
        //实例化校验ｒｕｌｅ类
        $checkModel = new CheckRuleList();
        $money = array();
        foreach ($rule_list as $key=>$rule) {
            $play_key = isset($rule['play_key']) ? $rule['play_key'] : '';
            if ($model_key != substr($play_key,0,strpos($play_key,"_"))) {
                $context->reply(['status'=>447,'msg'=>'请选号']);
                return;
            }
            //检查投注号码及拆分多注内容的结果
            $result = $checkModel->checkRule($rule,$config);
            //成功
            if ($result['status'] == 200) {
                $ruleArray[] = $result['data'];
            } else {
                $context->reply($result);
                return;
            }
            $check_betSql = "select bet_min,bet_max,acceptable from lottery_game_play where game_key = '$game_key' and play_key = '$play_key'";
            foreach ($mysqlStaff->query($check_betSql) as $ruleMoney) {
                //单注金额最大最小值监测
                if ($rule['price'] > $ruleMoney['bet_max'] || $rule['price'] < $ruleMoney['bet_min']) {
                    $context->reply(['status'=>432,'msg'=>'投注金额有误,请重新投注']);
                    return;
                }
                //判断玩法是否关闭
                if($ruleMoney["acceptable"] == 0){
                    $context->reply(["status"=>410,"msg"=>"该彩票暂停销售"]);
                    return;
                }
            }

            //判断返点不能超过最大值
            if (!isset($rule['rebate_rate']) || $rule['rebate_rate'] > $rebate_max || $rule['rebate_rate'] < 0) {
                $context->reply(['status'=>411,'msg'=>'投注返点有误,请重新投注']);
                return;
            }
            //检测该层级用户是否是禁止返点
            if(!empty($authArray)){
                if(in_array("rebate_prohibit",$authArray)){
                    $rule['rebate_rate'] = 0;

                }
            }
            
            $money[] = $rule['price'] * $rule['quantity'];
        }
        //计算投注总金额
        $money = array_sum($money) * $multiple;

        $sql = "select money from account where user_id = '$user_id'";
        $balance = 0;
        foreach ($mysql->query($sql) as $row){
            $balance = $row['money'];
        }

        //检测下注金额是否大于余额
        if ($money > $balance) {
            $context->reply(['status' => 412, 'msg' => '余额不足']);
            return;
        }

        $param = [
            ":layer_id"=>$layer_id,
            ":user_id"=>$user_id,
            ":period"=>$period,
            ":user_key"=>$user_key,
            ":game_key"=>$game_key,
            ":rule_list"=> json_encode($ruleArray),
            ":multiple"=>$multiple,
            ":account_name"=>$account_name
        ];

        //入库sql
        $sql = "insert into bet_normal set user_id=:user_id,layer_id=:layer_id,user_key=:user_key,game_key=:game_key,rule_list=:rule_list,period=:period,multiple=:multiple,account_name=:account_name";
        try{
        //执行
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"投注失败"]);
            throw new \PDOException($e);
        }
        $context->reply(['status'=>200,'msg'=>'投注成功']);
        $id = $context->clientId();
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('User/Balance', ['user_id' => $user_id,'deal_key'=>$deal_key,'id'=>$id]);
    }

}