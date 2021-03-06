<?php

namespace App\Websocket\User\PersonalReport;

use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

/*
 * 我的--个人报表
 * User/PersonalReport/PersonalReport {"date":"yesterday"}
 * 不传参数为今日报表,today,yesterday,thisWeek,lastWeek,thisMonth,lastMonth
 * */

class PersonalReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }
        
        $userId = $context->getInfo('UserId');
        //连接数据库
        $param = $context->getData();
        $date = isset($param['time']) ? $param['time']:'today';

        $mysqlReport = $config->data_report;
       switch ($date)
       {
           case 'yesterday':
               $daily = date('Ymd',strtotime('-1 day'));
               $data_sql = "select deposit_amount,withdraw_amount,wager_amount AS bet_amount,bonus_amount,rebate_amount,subsidy_amount,coupon_amount,profit_amount from daily_user where user_id='$userId' and daily = '$daily'";
               $commission_sql = "select brokerage from daily_user_brokerage where user_id = '$userId' and daily = '$daily'";
               $result = [];
               foreach ($mysqlReport->query($data_sql) as $item) {
                   $result = $item;
               }
               //佣金
               $brokerage = 0;
               foreach ($mysqlReport->query($commission_sql) as $value) {
                   $brokerage = $value['brokerage'];
               }
               $data = [
                   'deposit' => isset($result['deposit_amount']) ? substr(sprintf("%.3f",$result['deposit_amount']),0,-1) : 0,
                   'withdraw' => isset($result['withdraw_amount']) ? substr(sprintf("%.3f",$result['withdraw_amount']),0,-1) : 0,
                   'bet' => isset($result['bet_amount']) ? substr(sprintf("%.3f",$result['bet_amount']),0,-1) : 0,
                   'bonus' => isset($result['bonus_amount']) ? substr(sprintf("%.3f",$result['bonus_amount']),0,-1) : 0,
                   'rebate' => isset($result['rebate_amount']) ? substr(sprintf("%.3f",$result['rebate_amount']),0,-1) : 0,
                   'coupon' => isset($result['coupon_amount']) ? substr(sprintf("%.3f",$result['coupon_amount']),0,-1) : 0,
                   'brokerage' => substr(sprintf("%.3f",$brokerage),-1),
                   'subsidy' => isset($result['subsidy_amount']) ? substr(sprintf("%.3f",$result['subsidy_amount']),0,-1) : 0,
                   'profit_amount' => isset($result['profit_amount']) ? substr(sprintf("%.3f",$result['profit_amount']),0,-1):0,
               ];
               break;
           case 'thisWeek':
               $weekly = date('oW',strtotime('this week'));
               $data_sql = "select deposit_amount,withdraw_amount,wager_amount AS bet_amount,bonus_amount,rebate_amount,subsidy_amount,coupon_amount,profit_amount from weekly_user where user_id='$userId' and weekly = '$weekly'";
               $commission_sql = "select brokerage from weekly_user_brokerage where user_id = '$userId' and weekly = '$weekly'";
               $result = [];
               foreach ($mysqlReport->query($data_sql) as $item) {
                   $result = $item;
               }
               //佣金
               $brokerage = 0;
               foreach ($mysqlReport->query($commission_sql) as $value) {
                   $brokerage = $value['brokerage'];
               }
               $data = [
                   'deposit' => isset($result['deposit_amount']) ? substr(sprintf("%.3f",$result['deposit_amount']),0,-1) : 0,
                   'withdraw' => isset($result['withdraw_amount']) ? substr(sprintf("%.3f",$result['withdraw_amount']),0,-1) : 0,
                   'bet' => isset($result['bet_amount']) ? substr(sprintf("%.3f",$result['bet_amount']),0,-1) : 0,
                   'bonus' => isset($result['bonus_amount']) ? substr(sprintf("%.3f",$result['bonus_amount']),0,-1) : 0,
                   'rebate' => isset($result['rebate_amount']) ? substr(sprintf("%.3f",$result['rebate_amount']),0,-1) : 0,
                   'coupon' => isset($result['coupon_amount']) ? substr(sprintf("%.3f",$result['coupon_amount']),0,-1) : 0,
                   'brokerage' => substr(sprintf("%.3f",$brokerage),-1),
                   'subsidy' => isset($result['subsidy_amount']) ? substr(sprintf("%.3f",$result['subsidy_amount']),0,-1) : 0,
                   'profit_amount' => isset($result['profit_amount']) ? substr(sprintf("%.3f",$result['profit_amount']),0,-1):0,
               ];
               break;
           case 'lastWeek':
               $weekly = date('oW',strtotime('-1 week'));
               $data_sql = "select deposit_amount,withdraw_amount,wager_amount AS bet_amount,bonus_amount,rebate_amount,subsidy_amount,coupon_amount,profit_amount from weekly_user where user_id='$userId' and weekly = '$weekly'";
               $commission_sql = "select brokerage from weekly_user_brokerage where user_id = '$userId' and weekly = '$weekly'";
               $result = [];
               foreach ($mysqlReport->query($data_sql) as $item) {
                   $result = $item;
               }
               //佣金
               $brokerage = 0;
               foreach ($mysqlReport->query($commission_sql) as $value) {
                   $brokerage = $value['brokerage'];
               }
               $data = [
                   'deposit' => isset($result['deposit_amount']) ? substr(sprintf("%.3f",$result['deposit_amount']),0,-1) : 0,
                   'withdraw' => isset($result['withdraw_amount']) ? substr(sprintf("%.3f",$result['withdraw_amount']),0,-1) : 0,
                   'bet' => isset($result['bet_amount']) ? substr(sprintf("%.3f",$result['bet_amount']),0,-1) : 0,
                   'bonus' => isset($result['bonus_amount']) ? substr(sprintf("%.3f",$result['bonus_amount']),0,-1) : 0,
                   'rebate' => isset($result['rebate_amount']) ? substr(sprintf("%.3f",$result['rebate_amount']),0,-1) : 0,
                   'coupon' => isset($result['coupon_amount']) ? substr(sprintf("%.3f",$result['coupon_amount']),0,-1) : 0,
                   'brokerage' => substr(sprintf("%.3f",$brokerage),-1),
                   'subsidy' => isset($result['subsidy_amount']) ? substr(sprintf("%.3f",$result['subsidy_amount']),0,-1) : 0,
                   'profit_amount' => isset($result['profit_amount']) ? substr(sprintf("%.3f",$result['profit_amount']),0,-1):0,
               ];
               break;
           case 'thisMonth':
               $monthly = date('Ym',strtotime('this month'));
               $data_sql = "select deposit_amount,withdraw_amount,wager_amount AS bet_amount,bonus_amount,rebate_amount,subsidy_amount,coupon_amount,profit_amount from monthly_user where user_id='$userId' and monthly = '$monthly'";
               $commission_sql = "select brokerage from monthly_user_brokerage where user_id = '$userId' and monthly = '$monthly'";
               $result = [];
               foreach ($mysqlReport->query($data_sql) as $item) {
                   $result = $item;
               }
               //佣金
               $brokerage = 0;
               foreach ($mysqlReport->query($commission_sql) as $value) {
                   $brokerage = $value['brokerage'];
               }
               $data = [
                   'deposit' => isset($result['deposit_amount']) ? substr(sprintf("%.3f",$result['deposit_amount']),0,-1) : 0,
                   'withdraw' => isset($result['withdraw_amount']) ? substr(sprintf("%.3f",$result['withdraw_amount']),0,-1) : 0,
                   'bet' => isset($result['bet_amount']) ? substr(sprintf("%.3f",$result['bet_amount']),0,-1) : 0,
                   'bonus' => isset($result['bonus_amount']) ? substr(sprintf("%.3f",$result['bonus_amount']),0,-1) : 0,
                   'rebate' => isset($result['rebate_amount']) ? substr(sprintf("%.3f",$result['rebate_amount']),0,-1) : 0,
                   'coupon' => isset($result['coupon_amount']) ? substr(sprintf("%.3f",$result['coupon_amount']),0,-1) : 0,
                   'brokerage' => substr(sprintf("%.3f",$brokerage),-1),
                   'subsidy' => isset($result['subsidy_amount']) ? substr(sprintf("%.3f",$result['subsidy_amount']),0,-1) : 0,
                   'profit_amount' => isset($result['profit_amount']) ? substr(sprintf("%.3f",$result['profit_amount']),0,-1):0,
               ];
               break;
           case 'lastMonth':
               $monthly = date('Ym',strtotime('-1 month'));
               $context->reply($monthly);
               $data_sql = "select deposit_amount,withdraw_amount,wager_amount AS bet_amount,bonus_amount,rebate_amount,subsidy_amount,coupon_amount,profit_amount from monthly_user where user_id='$userId' and monthly = '$monthly'";
               $commission_sql = "select brokerage from monthly_user_brokerage where user_id = '$userId' and monthly = '$monthly'";
               $result = [];
               foreach ($mysqlReport->query($data_sql) as $item) {
                   $result = $item;
               }
               //佣金
               $brokerage = 0;
               foreach ($mysqlReport->query($commission_sql) as $value) {
                   $brokerage = $value['brokerage'];
               }
               $data = [
                   'deposit' => isset($result['deposit_amount']) ? substr(sprintf("%.3f",$result['deposit_amount']),0,-1) : 0,
                   'withdraw' => isset($result['withdraw_amount']) ? substr(sprintf("%.3f",$result['withdraw_amount']),0,-1) : 0,
                   'bet' => isset($result['bet_amount']) ? substr(sprintf("%.3f",$result['bet_amount']),0,-1) : 0,
                   'bonus' => isset($result['bonus_amount']) ? substr(sprintf("%.3f",$result['bonus_amount']),0,-1) : 0,
                   'rebate' => isset($result['rebate_amount']) ? substr(sprintf("%.3f",$result['rebate_amount']),0,-1) : 0,
                   'coupon' => isset($result['coupon_amount']) ? substr(sprintf("%.3f",$result['coupon_amount']),0,-1) : 0,
                   'brokerage' => substr(sprintf("%.3f",$brokerage),-1),
                   'subsidy' => isset($result['subsidy_amount']) ? substr(sprintf("%.3f",$result['subsidy_amount']),0,-1) : 0,
                   'profit_amount' => isset($result['profit_amount']) ? substr(sprintf("%.3f",$result['profit_amount']),0,-1):0,
               ];
               break;
           default:
               $daily = date('Ymd',strtotime('today'));
               $data_sql = "select deposit_amount,withdraw_amount,wager_amount AS bet_amount,bonus_amount,rebate_amount,subsidy_amount,coupon_amount,profit_amount from daily_user where user_id='$userId' and daily = '$daily'";
               $commission_sql = "select brokerage from daily_user_brokerage where user_id = '$userId' and daily = '$daily'";
               $result = [];
               foreach ($mysqlReport->query($data_sql) as $item) {
                   $result = $item;
               }
               //佣金
               $brokerage = 0;
               foreach ($mysqlReport->query($commission_sql) as $value) {
                   $brokerage = $value['brokerage'];
               }
               $data = [
                   'deposit' => isset($result['deposit_amount']) ? substr(sprintf("%.3f",$result['deposit_amount']),0,-1) : 0,
                   'withdraw' => isset($result['withdraw_amount']) ? substr(sprintf("%.3f",$result['withdraw_amount']),0,-1) : 0,
                   'bet' => isset($result['bet_amount']) ? substr(sprintf("%.3f",$result['bet_amount']),0,-1) : 0,
                   'bonus' => isset($result['bonus_amount']) ? substr(sprintf("%.3f",$result['bonus_amount']),0,-1) : 0,
                   'rebate' => isset($result['rebate_amount']) ? substr(sprintf("%.3f",$result['rebate_amount']),0,-1) : 0,
                   'coupon' => isset($result['coupon_amount']) ? substr(sprintf("%.3f",$result['coupon_amount']),0,-1) : 0,
                   'brokerage' => substr(sprintf("%.3f",$brokerage),-1),
                   'subsidy' => isset($result['subsidy_amount']) ? substr(sprintf("%.3f",$result['subsidy_amount']),0,-1) : 0,
                   'profit_amount' => isset($result['profit_amount']) ? substr(sprintf("%.3f",$result['profit_amount']),0,-1):0,
               ];
               break;
       }
        $context->reply(['status' => 200,'msg' => '获取成功', 'data' => $data]);


    }
}