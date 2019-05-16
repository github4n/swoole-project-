<?php
namespace App\Websocket\User\Recharge;

use App\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 充值
 *  User/Recharge/BankRecharge {"route_id":1,"launch_money":500,"from_name":"唐三","from_type":"1"}
 *
 * */

class BankRecharge extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }

        $data = $context->getData();
        $mysqlStaff = $config->data_staff;
        $mysql_user = $config->data_user;
        $dael_key = $context->getInfo("DealKey");
        $mysql = $config->__get("data_".$dael_key);
        $cache = $config->cache_app;
        
        $route_id = isset($data["route_id"]) ? $data["route_id"] : '';
        $launch_money = isset($data["launch_money"]) ? $data['launch_money'] : '';
        $from_name = isset($data["from_name"]) ? $data["from_name"] : "";
        $from_type = isset($data["from_type"]) ? $data["from_type"] : "";

        if(empty($route_id)){
            $context->reply(["status"=>301,"msg"=>"请选择的付款通道"]);
            return;
        }
        if(!is_numeric($route_id)){
            $context->reply(["status"=>205,"msg"=>"请选择付款通道"]);
            return;
        }
        
        if(!is_numeric($launch_money)) {
            $context->reply(["status"=>212,"msg"=>"请输入充值金额"]);
            return;
        }
        if(empty($from_name)) {
            $context->reply(["status"=>300,"msg"=>"请输入真实姓名"]);
            return;
        }
        //验证
        $preg = '/^[\x{4e00}-\x{9fa5}]{2,6}$/u';
        if (!preg_match($preg, $from_name)) {
            $context->reply(['status' => 301, 'msg' => '请输入2-6个汉字']);
            return;
        }
        if(empty($from_type)){
            $context->reply(["status"=>302,"msg"=>"请选择转账类型"]);
            return;
        }
        if(!is_numeric($from_type)){
            $context->reply(["status"=>302,"msg"=>"请选择正确转账类型"]);
            return;
        }

        //获取用户收款银行的信息
        $sql = "SELECT passage_id,passage_name,min_money,max_money,bank_name,bank_branch,account_number,account_name,coupon_rate,coupon_max,coupon_audit_rate,coupon_times FROM deposit_route_bank_intact where route_id=:route_id";
        $param = [":route_id"=>$route_id];
        $info = [];
        foreach ($mysqlStaff->query($sql,$param) as $row){
            $info = $row;
        }
        if(empty($info)){
            $context->reply(["status"=>302,"msg"=>"请选择付款方"]);
            return;
        }

        if($launch_money<$info["min_money"] ){
           $context->reply(["status"=>305,"msg"=>'充值金额不能少于'.$info["min_money"]]);
           return;
        }
        if( $launch_money>$info["max_money"]){
            $context->reply(["status"=>306,"msg"=>'充值金额不能多余'.$info["max_money"]]);
            return;
        }

        $today_start = strtotime("today");
        $today_end = $today_start+86400;
        //获取用户今日充值的总次数及上一次入款的时间
        $deposit_info = [];
        $sql = "select count(deposit_serial) as deposit_count,max(launch_time) as max_time from deposit_launch where user_id=:user_id and launch_time between $today_start and $today_end";
        foreach ($mysql->query($sql,[":user_id"=>$context->getInfo("UserId")]) as $row){
            $deposit_info = $row;
        }
        if(!empty($deposit_info)){
            if($deposit_info["deposit_count"]>$cache->hget("SiteSetting","deposit_count_day")){
                $context->reply(["status"=>301,"msg"=>"今日入款次数已用完"]);
                return;
            }
            if($deposit_info["max_time"]>time()-$cache->hget("SiteSetting","deposit_interval")){
                $context->reply(["status"=>301,"msg"=>"请勿频繁操作，稍后再来"]);
                return;
            }
        }

        //获取用户的真实姓名
        $sql = "SELECT account_name FROM bank_info WHERE user_id=:user_id";
        $param = [":user_id"=>$context->getInfo("UserId")];
        foreach ($mysql_user->query($sql,$param) as $row){
            $account_name =  $row["account_name"];
        }

        //判断该层级用户是否享有优惠的权限
        $auth = $context->getInfo("Auth");
        if(in_array("promotion_stop",json_decode($auth))){
            $coupon_money = 0;
        }else{
            //获取用户的今日的优惠金额和次数
            $coupon_info = [];
            $sql = "select sum(coupon_money) as coupon_money,count(deposit_serial) as coupon_num from deposit_bank_intact where user_id=:user_id and launch_time between $today_start and $today_end and coupon_money>0";
            foreach ($mysql->query($sql,[":user_id"=>$context->getInfo("UserId")]) as $row){
                $coupon_info = $row;
            }
            if(empty($coupon_info)){
                $coupon_money = ($launch_money*$info['coupon_rate'])/100;
            }else{
                if($coupon_info["coupon_money"] >= $info['coupon_max'] || $coupon_info["coupon_num"] >= $info['coupon_times']){
                    $coupon_money = 0;
                }else{
                    if($coupon_info["coupon_money"]+($launch_money*$info['coupon_rate'])/100 > $info['coupon_max']){
                        $coupon_money =  $info['coupon_max']-$coupon_info["coupon_money"];
                    }else{
                        $coupon_money =  ($launch_money*$info['coupon_rate'])/100;
                    }
                }
            }
        }

        $sql = "INSERT INTO deposit_launch SET user_id=:user_id, user_key=:user_key, passage_id=:passage_id, account_name=:account_name, passage_name=:passage_name, layer_id=:layer_id, launch_money=:launch_money, launch_device=:launch_device,coupon_money=:coupon_money,coupon_audit_rate=:coupon_audit_rate";
        $param = [
            ":user_id" => $context->getInfo("UserId"),
            ":user_key"=>$context->getInfo("UserKey"),
            ":account_name"=>empty($account_name)?'':$account_name,
            ":passage_id"=>$info["passage_id"],
            ":passage_name"=>$info["passage_name"],
            ":layer_id" => $context->getInfo("LayerId"),
            ":launch_device" => $context->getInfo("LoginDevice"),
            ":launch_money"=>$launch_money,
            ":coupon_money"=>$coupon_money,
            ":coupon_audit_rate"=>$info["coupon_audit_rate"]
        ];

        $deposit_serial = '';
        try{
            $mysql->execute($sql,$param);
            $sql = 'SELECT serial_last("deposit") as deposit_serial';
            foreach ($mysql->query($sql) as $row){
                $deposit_serial = $row['deposit_serial'];
            }
        }catch (\PDOException $e){
            $context->reply(["status"=>401,"msg"=>"申请失败"]);
            throw new \PDOException($e);
        }
        $sql = "INSERT INTO deposit_bank SET deposit_serial=:deposit_serial, from_type=:from_type,from_name=:from_name, to_bank_name=:to_bank_name, to_bank_branch=:to_bank_branch, to_account_number=:to_account_number, to_account_name=:to_account_name";
        $params = [
            ":deposit_serial"=>$deposit_serial,
            ":from_type"=>$from_type,
            ":from_name"=>$from_name,
            ":to_bank_name"=>$info["bank_name"],
            ":to_bank_branch"=>$info["bank_branch"],
            ":to_account_number"=>$info["account_number"],
            ":to_account_name"=>$info["account_name"],
        ];
        try{
            $mysql->execute($sql,$params);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"申请失败2"]);
            throw new \PDOException($e);
        }
        $context->reply(["status"=>200,"msg"=>"充值成功"]);
        $layer_id = $context->getInfo("LayerId");
        //通知站点有新的入款消息
        $taskAdapter = new \Lib\Task\Adapter($config->cache_app);
        $taskAdapter->plan('User/Notice', ["data" =>["msg"=>"有新的入款消息","layer_id"=>$layer_id,"money"=>$launch_money,"type"=>1]]);
    }
}