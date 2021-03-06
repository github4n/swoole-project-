<?php

/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-21
 * Time: 下午6:30
 */

namespace Site\Websocket\Member\Inspect;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/*
 * Member/Inspect/MemberVerify  {"user_key":"账号名","start_time":"2019-01-10 10:32:25","end_time":"","deal_type":"deposit_finish,withdraw,staff_deposit,staff_withdraw,bet_settle,bet_set","broker_type":"1/2/3/4"}
 */

class MemberVerify extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $StaffGrade = $context->getInfo("StaffGrade");
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("user_audit", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }

        $dealType = [
            'bet_set' => '投注',
            'bet_settle' => '彩票结算',
            'deposit_finish' => '入款',
            'withdraw_finish' => '出款',
            'staff_deposit' => '手工入款',
            'staff_withdraw' => '手工出款',
            'subsidy_deliver' => '投注反水',
            'brokerage_deliver' => '代理佣金',
            'external_fg_settle' => 'FG平台结算'
        ];

        $param = $context->getData();
        $user_key = isset($param['user_key']) ? $param['user_key'] : '';
        if (empty($user_key)) {
            $context->reply(['status' => 404, 'msg' => '请输入您要搜索的会员账号']);
            return;
        }
        $userMysql = $config->data_user;
        $agentMysql = $config->data_staff;
        $mysqlReport = $config->data_report;
        $staffId = $context->getInfo('StaffId');

        //1个人,2一级下线,3二级下线,4三级下线
        $broker_type = isset($param['broker_type']) ? $param['broker_type'] : 1;
        $start_time = isset($param['start_time']) ? $param['start_time'] : '';
        $deal_typeTranslation = isset($param['deal_type']) ? $param['deal_type'] : '';
        $end_time = isset($param['end_time']) ? $param['end_time'] : '';
        $deal_type = "  ";
        $user_key = " AND user_key = '" . $user_key . "'";

        if ($start_time && $end_time) {
            $start = strtotime(date("Ymd",strtotime($start_time)). " 00:00:00");
            $end = strtotime(date("Ymd",strtotime($end_time)). " 23:59:59");
            $time = " AND deal_time BETWEEN " . $start . " AND " . $end;
            $staff_withdraw_time = " AND withdraw_time BETWEEN " . $start . " AND " . $end;
            $time_report = " AND daily BETWEEN " . date('Ymd', $start) . " AND " . date('Ymd', $end);
            $regist_time = " and register_time between ".$start ." and ".$end;

        }

        if (!empty($deal_typeTranslation)) {
            if (strpos($deal_typeTranslation, 'external_fg_settle') !== false) {
                $deal_type = " AND (deal_type = 'external_import_success' OR deal_type = 'external_import_launch' OR deal_type = 'external_import_fungaming'"
<<<<<<< .mine
                        . " OR deal_type = 'external_import_failure' OR deal_type = 'external_export_success' OR deal_type = 'external_export_launch'"
                        . " OR deal_type = 'external_export_fungaming' OR deal_type = 'external_export_failure' OR deal_type = 'Fexternal_audit_fungaming' OR deal_type = 'external_audit')";
||||||| .r13118
                        . " OR deal_type = 'external_import_failure' OR deal_type = 'external_export_success' OR deal_type = 'external_export_launch'"
                        . " OR deal_type = 'external_export_fungaming' OR deal_type = 'external_export_failure' OR deal_type = 'external_audit_fungaming' OR deal_type = 'external_audit')";
=======
                    . " OR deal_type = 'external_import_failure' OR deal_type = 'external_export_success' OR deal_type = 'external_export_launch'"
                    . " OR deal_type = 'external_export_fungaming' OR deal_type = 'external_export_failure' OR deal_type = 'external_audit_fungaming' OR deal_type = 'external_audit')";
>>>>>>> .r13419
            } elseif (strpos($deal_typeTranslation, 'bet_settle') !== false) {
                $deal_type = " AND (deal_type = 'bet_settle')";
            } elseif (strpos($deal_typeTranslation, 'bet_set') !== false) {
                $deal_type = " AND (deal_type = 'bet_normal' OR deal_type = 'bet_chase')";
            } elseif (strpos($deal_typeTranslation, 'deposit_finish') !== false) {
                $deal_type = " AND (deal_type = 'deposit_finish'  OR deal_type = 'deposit_launch' OR deal_type = 'deposit_simple' OR deal_type = 'deposit_gateway')";
            } elseif (strpos($deal_typeTranslation, 'withdraw_finish') !== false) {
                $deal_type = " AND (deal_type = 'withdraw_finish' OR deal_type = 'withdraw_cancel' OR deal_type = 'withdraw_accept' OR  deal_type = 'withdraw_launch' )";
            } elseif (strpos($deal_typeTranslation, 'staff_deposit') !== false) {
                $deal_type = " AND (deal_type = 'staff_deposit')";
            } elseif (strpos($deal_typeTranslation, 'staff_withdraw') !== false) {
                $deal_type = " AND (deal_type = 'staff_withdraw')";
            } elseif (strpos($deal_typeTranslation, 'subsidy_deliver') !== false) {
                $deal_type = " AND (deal_type = 'subsidy_deliver')";
            } elseif (strpos($deal_typeTranslation, 'brokerage_deliver') !== false) {
                $deal_type = " AND (deal_type = 'brokerage_deliver')";
            }
        }
        //求账号下及会员

        switch ($StaffGrade)
        {
            case 0:
                if(!empty($MasterId)){
                    $accout_sql = "select group_concat(layer_id) as layer_list from staff_layer where staff_id=:staff_id";
                    $layer_lists = 0;

                    foreach ($agentMysql->query($accout_sql,[":staff_id"=>$context->getInfo('StaffId')]) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }else{
                    $accout_sql = "select group_concat(layer_id) as layer_list from layer_info";
                    foreach ($userMysql->query($accout_sql) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }
                $sql = "SELECT group_concat(agent_id) as agent_list FROM staff_struct_agent";
                foreach($agentMysql->query($sql) as $row ) {
                    $agent_list = $row['agent_list'];
                }
                break;
            case 1:
                if(!empty($MasterId)){
                    $accout_sql = "select group_concat(layer_id) as layer_list from staff_layer where staff_id=:staff_id";
                    $layer_lists = 0;

                    foreach ($agentMysql->query($accout_sql,[":staff_id"=>$context->getInfo('StaffId')]) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }else{
                    $accout_sql = "select group_concat(layer_id) as layer_list from layer_info";
                    foreach ($userMysql->query($accout_sql) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }
                $sql = "SELECT group_concat(agent_id) as agent_list FROM staff_struct_agent WHERE major_id='$staffId'";
                foreach($agentMysql->query($sql) as $row ) {
                    $agent_list = $row['agent_list'];
                }
                break;
            case 2:
                if(!empty($MasterId)){
                    $accout_sql = "select group_concat(layer_id) as layer_list from staff_layer where staff_id=:staff_id";
                    $layer_lists = 0;

                    foreach ($agentMysql->query($accout_sql,[":staff_id"=>$context->getInfo('StaffId')]) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }else{
                    $accout_sql = "select group_concat(layer_id) as layer_list from layer_info";
                    foreach ($userMysql->query($accout_sql) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }
                $sql = "SELECT group_concat(agent_id) as agent_list FROM staff_struct_agent WHERE minor_id='$staffId'";
                foreach($agentMysql->query($sql) as $row ) {
                    $layer_lists = $row['agent_list'];
                }
                break;
            case 3:
                if(!empty($MasterId)){
                    $accout_sql = "select group_concat(layer_id) as layer_list from staff_layer where staff_id=:staff_id";
                    $layer_lists = 0;

                    foreach ($agentMysql->query($accout_sql,[":staff_id"=>$context->getInfo('StaffId')]) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }else{
                    $accout_sql = "select group_concat(layer_id) as layer_list from layer_info";
                    foreach ($userMysql->query($accout_sql) as $row){
                        $layer_lists = $row["layer_list"];
                    }
                }
                $agent_list = $staffId;
                break;
        }
         //核对用户信息
        $userInfo = [];
        $sql = "SELECT user_id,invite_code,deal_key FROM user_info_intact WHERE agent_id in ($agent_list) and layer_id in ($layer_lists) ".$user_key;
        foreach ($userMysql->query($sql) as $row){
            $userInfo = $row;
        }
        if(empty($userInfo)){
            $context->reply(["status"=>300,"msg"=>"未查询到改用户信息"]);
            return;
        }
        if(!empty($userInfo["invite_code"])) {
            $is_agent = 1;
        }else{
            $is_agent = 0;
        }
        //个人统计数据
        $count_deal_sql = "select sum(wager_amount) as wager_amount,sum(bonus_amount) as bonus_amount,sum(deposit_amount) as deposit_amount,sum(withdraw_amount) as withdraw_amount,sum(subsidy_amount) as subsidy_amount,sum(coupon_amount) as coupon_amount from daily_user where 1=1 " . $user_key . $time_report;
        foreach ($mysqlReport->query($count_deal_sql) as $val) {
            $count_data = $val;
        }
        $count_datas = [
            "wager_amount" => empty($count_data["wager_amount"]) ? 0 : $count_data["wager_amount"],
            "bonus_amount" => empty($count_data["bonus_amount"]) ? 0 : $count_data["bonus_amount"],
            "deposit_amount" => empty($count_data["deposit_amount"]) ? 0 : $count_data["deposit_amount"],
            "withdraw_amount" => empty($count_data["withdraw_amount"]) ? 0 : $count_data["withdraw_amount"],
            "subsidy_amount" => empty($count_data["subsidy_amount"]) ? 0 : $count_data["subsidy_amount"],
            "coupon_amount" => empty($count_data["coupon_amount"]) ? 0 : $count_data["coupon_amount"],
        ];
        $subordinate = [];
        $subordinates = [];
        $user_id= $userInfo["user_id"];
        if ($is_agent ==1) {
            $subordinate_sql = "select sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,sum(wager_amount) as wager_amount,sum(deposit_amount) as deposit_amount,sum(withdraw_amount) as withdraw_amount,sum(coupon_amount) as coupon_amount from daily_user where (broker_1_id = $user_id or broker_2_id = $user_id or broker_3_id = $user_id)".$time_report;
            foreach ($mysqlReport->query($subordinate_sql) as $val) {
                $subordinate = $val;
            }
            $subordinates["bet_amount"]= empty($subordinate["bet_amount"]) ? 0 : $subordinate["bet_amount"];
            $subordinates["bonus_amount"]=empty($subordinate["bonus_amount"]) ? 0 : $subordinate["bonus_amount"];
            $subordinates["wager_amount"]=empty($subordinate["wager_amount"]) ? 0 : $subordinate["wager_amount"];
            $subordinates["deposit_amount"]=empty($subordinate["deposit_amount"]) ? 0 : $subordinate["deposit_amount"];
            $subordinates["withdraw_amount"]=empty($subordinate["withdraw_amount"]) ? 0 : $subordinate["withdraw_amount"];
            $subordinates["coupon_amount"]=empty($subordinate["coupon_amount"]) ? 0 : $subordinate["coupon_amount"];
            //下级人数
            $subSql = "select user_id from user_info_intact where (broker_1_id = $user_id or broker_2_id = $user_id or broker_3_id = $user_id)" .$regist_time;
            $context->reply($subSql);
            $subordinates["user_count"] = $userMysql->execute($subSql);
        }
        $user_ids = 0;
        //会员个人信息
        switch ($broker_type) {
            //会员个人
            case 1:
                $user_ids = $user_id;
                break;
            //一级下线
            case 2:
                $broker_sql = "select group_concat(user_id) as user_list from user_info_intact where broker_1_id = '$user_id'";
                foreach ($userMysql->query($broker_sql) as $id) {
                    $user_ids = $id['user_list'];
                }
                break;
            //二级下线
            case 3:
                $broker_sql = "select group_concat(user_id) as user_list from user_info_intact where broker_2_id = '$user_id'";
                foreach ($userMysql->query($broker_sql) as $id) {
                    $user_ids = $id['user_list'];
                }
                break;
            //三级下线
            case 4:
                $broker_sql = "select group_concat(user_id) as user_list from user_info_intact where broker_3_id = '$user_id'";
                foreach ($userMysql->query($broker_sql) as $id) {
                    $user_ids = $id['user_list'];
                }
                break;
        }
        if(empty($user_ids)){
            $user_ids = 0;
        }
        $dealSql = "select deal_serial,user_key,layer_id,account_name,deal_type,vary_money,new_money,summary,deal_time from deal where user_id IN ($user_ids)" . $deal_type . $time . " order by deal_time desc";
        $user_count_sql = "select count(user_id) as user_count from deal where user_id in ($user_ids)" . $deal_type . $time . " group by user_id";
        //入款
        $deposit_count_sql = "select sum(vary_money) as vary_money from deal where user_id in ($user_ids) and (deal_type = 'deposit_finish' or deal_type = 'staff_deposit') " . $time;
        //投注
        $bet_money_sql = "select sum(vary_money) as vary_money from deal where user_id in ($user_ids) and (deal_type = 'bet_normal' or deal_type = 'bet_chase') " . $time;
        //反水
        $rebate_rate_sql = "select sum(vary_money) as vary_money from deal where user_id in ($user_ids) and deal_type = 'subsidy_deliver'" . $time;
        //派彩
        $bonus_sql = "select sum(vary_money) as vary_money from deal where user_id in ($user_ids) and deal_type = 'bet_settle' " . $time;
        //佣金
        $commission_sql = "select sum(vary_money) as vary_money from deal where user_id in ($user_ids) and deal_type = 'brokerage_deliver'" . $time;
        //出款
        $withdraw_count_sql = "select sum(vary_money) as vary_money from deal where user_id in ($user_ids) and (deal_type = 'withdraw_finish' or deal_type = 'staff_withdraw') " . $time;
        //手工出款
        $staff_withdraw = "select sum(money) as money from staff_withdraw where user_id in ($user_ids) ".$staff_withdraw_time;

        $deal_key = $userInfo["deal_key"];
        $mysqlDeal = $config->__get("data_".$deal_key) ;
        $list = iterator_to_array($mysqlDeal->query($dealSql));
        $data = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                $type = $item['deal_type'];

                switch ($type) {
                    case 'deposit_finish' :
                        $tag = '入款';
                        break;
                    case 'withdraw_finish' :
                        $tag = '出款';
                        break;
                    case 'staff_withdraw' :
                        $tag = '手工出款';
                        break;
                    case 'bet_settle' :
                        $tag = '彩票结算';
                        break;
                    case 'bet_normal' :
                    case 'bet_chase' :
                        $tag = '投注';
                        break;
                    case 'staff_deposit' :
                        $tag = '手工入款';
                        break;
                    case 'subsidy_deliver':
                        $tag = '投注反水';
                        break;
                    case 'brokerage_deliver':
                        $tag = '代理返佣';
                        break;
                    case 'withdraw_launch':
                        $tag = '申请提现';
                        break;

                    case 'withdraw_reject':
                        $tag = '拒绝提现';
                        break;

                    case 'withdraw_lock':
                        $tag = '入款加锁';
                        break;

                    case 'withdraw_cancel':
                        $tag = '出款失败';
                        break;

                    case 'withdraw_accept':
                        $tag = '允许出款';
                        break;

                    case 'external_import_success':
                        $tag = '外接口转入成功';
                        break;
                    case 'external_import_launch':
                        $tag = '从第三方转入额度';
                        break;
                    case 'external_import_fungaming':
                        $tag = '从FunGaming转入';
                        break;
                    case 'external_import_failure':
                        $tag = '第三方转入失败';
                        break;
                    case 'external_export_success':
                        $tag = '第三方转出成功';
                        break;
                    case 'external_export_launch':
                        $tag = '转出额度到第三方平台';
                        break;
                    case 'external_export_fungaming':
                        $tag = '转出到FunGaming';
                        break;
                    case 'external_export_failure':
                        $tag = '转出到第三方平台失败';
                        break;
                    case 'external_audit':
                        $tag = '三方平台打码稽核';
                        break;
                    case 'external_audit_fungaming':
                        $tag = 'FunGaming平台打码';
                        break;
                    case 'deposit_simple':
                        $tag = '快捷入款';
                        break;
                    case 'deposit_launch':
                        $tag = '入款申请';
                        break;
                    case 'deposit_gateway':
                        $tag = '三方入款';
                        break;
                    default :
                        $tag = '';
                        break;
                }
                $values = [
                    'deal_serial' => $item['deal_serial'],
                    'user_key' => $item['user_key'],
                    'layer_name' => empty($context->getInfo($item['layer_id'])) ? "层级已删除" : $context->getInfo($item['layer_id']),
                    'deal_type' => $tag,
                    'account_name' => empty($item['account_name']) ? "" : $item['account_name'],
                    'detail' => json_decode($item['summary']),
                    'vary_money' => $item['vary_money'],
                    'new_money' => $item['new_money'],
                    'deal_time' => $item['deal_time']
                ];
                $data[] = $values;
            }
        }
        //统计数据
        $user_count = 0;
        $deposit_count = 0;
        $bet_money = 0;
        $rebate_rate = 0;
        $bonus = 0;
        $commission = 0;
        $withdraw_count = 0;
        $user_count += $mysqlDeal->execute($user_count_sql);
        foreach ($mysqlDeal->query($deposit_count_sql) as $val) {
            $deposit_count += abs($val['vary_money']);
        }
        
        foreach ($mysqlDeal->query($bet_money_sql) as $val) {
            $bet_money += abs($val['vary_money']);
        }
        foreach ($mysqlDeal->query($bonus_sql) as $val) {
            $bonus += abs($val['vary_money']);
        }
        foreach ($mysqlDeal->query($withdraw_count_sql) as $val) {
            $withdraw_count += abs($val['vary_money']);
        }
        foreach ($mysqlDeal->query($staff_withdraw) as $val) {
            $withdraw_count += $val['money'];
        }
        foreach ($mysqlDeal->query($rebate_rate_sql) as $val) {
            $rebate_rate += abs($val['vary_money']);
        }
        foreach ($mysqlDeal->query($commission_sql) as $val) {
            $commission += abs($val['vary_money']);
        }
        $context->reply([
            'status' => 200,
            'msg' => '成功',
            //会员统计数据
            'count_data' => $count_datas,
            //会员下级数据
            'subordinate' => $subordinates,
            //是否代理
            'is_agent' => $is_agent,
            //交易类型下拉框数据
            'dealType' => $dealType,
            //所有匹配数据
            'data' => $data,
            //总计人数
            'user_count' => $user_count,
            //充值
            'deposit_money' => $deposit_count,
            //投注
            'bet_money' => $bet_money,
            //反水
            'rebate_rate' => $rebate_rate,
            //提现
            'withdraw_money' => $withdraw_count,
            //返佣
            'commission' => $commission,
            //中奖
            'bonus' => $bonus,
        ]);
    }

}
