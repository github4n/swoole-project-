<?php
namespace App\Websocket\User\Agent;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--代理中心--代理报表
 * User/Agent/AgentReport {"date":"this_week"}
 * yesterday,this_week,last_week,this_month,last_month
 * */

class AgentReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }
        $invite_code = $context->getInfo('InviteCode');
        if(empty($invite_code)){
            $context->reply(["status"=>204,"msg"=>"当前登录账号不是代理账号"]);
            return;
        }
        $data = $context->getData();
        $user_id = $context->getInfo("UserId");
        $mysql = $config->data_report;
        $usermysql = $config->data_user;
        $date = isset($data["date"]) ? $data["date"] : "";
        $list = [];
        $brokerage = [];
        if($date == "today"){
            $beginToday=strtotime(date('Y-m-d'.'00:00:00',time()));
            $endToday=strtotime(date('Y-m-d'.'00:00:00',time()+3600*24));
            $sql = "select sum(is_today_register) as first_register,sum(bonus_amount) as bonus,".
                "sum(coupon_amount) as coupon,sum(withdraw_amount) as withdraw,sum(deposit_amount) as deposit,".
                "sum(bet_amount) as bet,sum(is_first_deposit>0) as first_deposit,sum(bet_amount>0) as user_count ".
                "from daily_user where broker_1_id=:user_id and daily between $beginToday and $endToday";
            foreach ($mysql->query($sql,[':user_id'=>$user_id]) as $rows){
                $list = $rows;
            }
            //下级用户
            $usersql = "select count(user_id) as sub_users from  user_info_intact where (broker_1_id = :user_id or broker_2_id = :user_id or broker_3_id = :user_id) and register_time between $beginToday and $endToday";
            foreach ($usermysql -> query($usersql,[':user_id' => $user_id]) as $v) {
                $list['sub_users'] = $v['sub_users'];
            }
            //下级佣金
            $sql = "SELECT brokerage FROM daily_user_brokerage WHERE user_id=:user_id AND daily between $beginToday and $endToday";
            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $brokerage = $row;
            }

        }elseif($date == "yesterday"){
            $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
            $sql = "select sum(is_today_register) as first_register,sum(bonus_amount) as bonus,".
                "sum(coupon_amount) as coupon,sum(withdraw_amount) as withdraw,sum(deposit_amount) as deposit,".
                "sum(bet_amount) as bet,sum(is_first_deposit>0) as first_deposit,sum(bet_amount>0) as user_count ".
                "from daily_user where broker_1_id=:user_id and daily between $beginYesterday and $endYesterday";
            foreach ($mysql->query($sql,[':user_id'=>$user_id]) as $rows){
                $list = $rows;
            }
            //下级用户
            $usersql = "select count(user_id) as sub_users from  user_info_intact where (broker_1_id = :user_id or broker_2_id = :user_id or broker_3_id = :user_id) and register_time between $beginYesterday and $endYesterday";
            foreach ($usermysql -> query($usersql,[':user_id' => $user_id]) as $v) {
                $list['sub_users'] = $v['sub_users'];
            }

            //下级佣金
            $sql = "SELECT brokerage FROM daily_user_brokerage WHERE user_id=:user_id AND daily between $beginYesterday and $endYesterday";
            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $brokerage = $row;
            }

        }
        elseif($date == "this_week"){
            $beginThisweek = strtotime(date('Y-m-d', strtotime('this week')));
            $endThisweek =  strtotime(date('Y-m-d', strtotime('last day next week +1 day')));
            $sql = "select sum(bonus_amount) as bonus,".
                "sum(coupon_amount) as coupon,sum(withdraw_amount) as withdraw,sum(deposit_amount) as deposit,".
                "sum(bet_amount) as bet,sum(bet_amount>0) as user_count ".
                "from daily_user where broker_1_id=:user_id and daily between $beginThisweek and $endThisweek";
            foreach ($mysql->query($sql,[':user_id'=>$user_id]) as $rows){
                $list = $rows;
            }
            //下级用户
            $usersql = "select count(user_id) as sub_users from  user_info_intact where (broker_1_id = :user_id or broker_2_id = :user_id or broker_3_id = :user_id) and register_time between $beginThisweek and $endThisweek";
            foreach ($usermysql -> query($usersql,[':user_id' => $user_id]) as $v) {
                $list['sub_users'] = $v['sub_users'];
            }

            //下级佣金
            $sql = "SELECT brokerage FROM daily_user_brokerage WHERE user_id=:user_id AND daily between $beginThisweek and $endThisweek";

            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $brokerage = $row;
            }
            //本周注册人数
            $sql = "select sum(is_today_register) as first_register,sum(is_first_deposit>0) as first_deposit "."from daily_user where user_id=:user_id and daily between $beginThisweek and $beginThisweek";
           foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
               $list["first_register"] = $row["first_register"];
               $list["first_deposit"] = $row["first_deposit"];
           }
        }
        elseif ($date == "last_week"){
            $beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
            $endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
            $sql = "select sum(bonus_amount) as bonus,".
                "sum(coupon_amount) as coupon,sum(withdraw_amount) as withdraw,sum(deposit_amount) as deposit,".
                "sum(bet_amount) as bet,sum(bet_amount>0) as user_count ".
                "from daily_user where broker_1_id=:user_id and daily between $beginLastweek and $endLastweek";
            foreach ($mysql->query($sql,[':user_id'=>$user_id]) as $rows){
                $list = $rows;
            }
            //上周下级人数
            $usersql = "select count(user_id) as sub_users from  user_info_intact where (broker_1_id = :user_id or broker_2_id = :user_id or broker_3_id = :user_id) and register_time between $beginLastweek and $endLastweek";
            foreach ($usermysql -> query($usersql,[':user_id' => $user_id]) as $v) {
                $list['sub_users'] = $v['sub_users'];
            }

            //下级佣金
            $sql = "SELECT brokerage FROM daily_user_brokerage WHERE user_id=:user_id AND daily between $beginLastweek and $endLastweek";
            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $brokerage = $row;
            }

            //上周注册人数
            $sql = "select sum(is_today_register) as first_register,sum(is_first_deposit>0) as first_deposit "."from daily_user where user_id=:user_id and daily between $beginLastweek and $endLastweek";

            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $list["first_register"] = $row["first_register"];
                $list["first_deposit"] = $row["first_deposit"];
            }

        }elseif ($date == "this_month"){
            $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
            $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
            $sql = "select sum(bonus_amount) as bonus,".
                "sum(coupon_amount) as coupon,sum(withdraw_amount) as withdraw,sum(deposit_amount) as deposit,".
                "sum(bet_amount) as bet,sum(bet_amount>0) as user_count ".
                "from daily_user where broker_1_id=:user_id and daily between $beginThismonth and $endThismonth";
            foreach ($mysql->query($sql,[':user_id'=>$user_id]) as $rows){
                $list = $rows;
            }
            //下级用户
            $usersql = "select count(user_id) as sub_users from  user_info_intact where (broker_1_id = :user_id or broker_2_id = :user_id or broker_3_id = :user_id) and register_time between $beginThismonth and $endThismonth";
            foreach ($usermysql -> query($usersql,[':user_id' => $user_id]) as $v) {
                $list['sub_users'] = $v['sub_users'];
            }
            //下级佣金
            $sql = "SELECT brokerage FROM daily_user_brokerage WHERE user_id=:user_id AND daily between $beginThismonth and $endThismonth";
            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $brokerage = $row;
            }
            //本月注册人数
            $sql = "select sum(is_today_register) as first_register,sum(is_first_deposit>0) as first_deposit "."from daily_user where user_id=:user_id and daily between $beginThismonth and $endThismonth";
            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $list["first_register"] = $row["first_register"];
                $list["first_deposit"] = $row["first_deposit"];
            }
            
        }elseif($date == "last_month"){
            $lastMonthBegin = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
            $lastMonthEnd = mktime(23, 59, 59, date("m"), 0, date("Y")); //上月结束时间戳
            $sql = "select sum(bonus_amount) as bonus,".
                "sum(coupon_amount) as coupon,sum(withdraw_amount) as withdraw,sum(deposit_amount) as deposit,".
                "sum(bet_amount) as bet,sum(bet_amount>0) as user_count ".
                "from daily_user where broker_1_id=:user_id and daily between $lastMonthBegin and $lastMonthEnd";
            foreach ($mysql->query($sql,[':user_id'=>$user_id]) as $rows){
                $list = $rows;
            }
            //下级用户
            $usersql = "select count(user_id) as sub_users from  user_info_intact where (broker_1_id = :user_id or broker_2_id = :user_id or broker_3_id = :user_id) and register_time between $lastMonthBegin and $lastMonthEnd";
            foreach ($usermysql -> query($usersql,[':user_id' => $user_id]) as $v) {
                $list['sub_users'] = $v['sub_users'];
            }
            //下级佣金
            $sql = "SELECT brokerage FROM daily_user_brokerage WHERE user_id=:user_id AND daily between $lastMonthBegin and $lastMonthEnd";
            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $brokerage = $row;
            }
            //上月注册人数
            $sql = "select sum(is_today_register) as first_register,sum(is_first_deposit>0) as first_deposit "."from daily_user where user_id=:user_id and daily between $lastMonthBegin and $lastMonthEnd";
            foreach ($mysql->query($sql,[":user_id"=>$user_id]) as $row){
                $list["first_register"] = $row["first_register"];
                $list["first_deposit"] = $row["first_deposit"];
            }
        }else{
            $context->reply(["status"=>203,"msg"=>"参数错误"]);
            return;
        }
        if(!empty($list)){
            $data_list = [
                "yesterday_user_new"=>!empty($list["first_register"]) ? $list["first_register"] : 0,
                "yesterday_user"=>!empty($list["sub_users"]) ? $list["sub_users"] : 0,
                "bet_count"=>!empty($list["bet"]) ? $list["bet"] : 0,
                "bonus"=>!empty($list["bonus"]) ? $list["bonus"] : 0,
                "coupon"=>!empty($list["coupon"]) ? $list["coupon"] : 0,
                "withdraw"=>!empty($list["withdraw"]) ? $list["withdraw"] : 0,
                "deposit"=>!empty($list["deposit"]) ? $list["deposit"] : 0,
                "bet"=>!empty($list["bet"]) ? $list["bet"] : 0,
                "first_deposit"=>!empty($list["first_deposit"]) ? $list["first_deposit"] : 0,
                "user_count"=>!empty($list["user_count"]) ? $list["user_count"] : 0];
        }else{
            $data_list = ["yesterday_user_new"=>0,"yesterday_user"=>0,"bet_count"=>0,"bonus"=>0,"coupon"=>0,"withdraw"=>0,"deposit"=>0,"bet"=>0,"first_deposit"=>0,"user_count"=>0];
        }
        if(!empty($brokerage)){
            $data_list += ["brokerage"=>$brokerage["brokerage"]];
        }else{
            $data_list += ["brokerage"=>0];
        }

        $context->reply(["status"=>200,"msg"=>"获取成功","data"=>$data_list]);
    }
}