<?php
namespace App\Websocket\User\Agent;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--代理中心--下级报表
 * User/Agent/SubReport {"date":"today"}
 * */

class SubReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }

        
        $data = $context->getData();
        $user_id = $context->getInfo("UserId");
        $invite_code = $context->getInfo("InviteCode");
        $mysql = $config->data_report;
        if(empty($invite_code)){
            $context->reply(["status"=>204,"msg"=>"请先升级为代理"]);
            return;
        }
        $day_time = $data["date"]?:"";
        
        if($day_time == "today"){
            $date = intval(date("Ymd",strtotime("today")));
            $sql = "select broker_1_rate,broker_2_rate,broker_3_rate,broker_1_user,broker_2_user,broker_3_user,brokerage_1,".
                "brokerage_2,brokerage_3,broker_1_bet,broker_2_bet,broker_3_bet from daily_user_brokerage where daily=:daily and user_id=:user_id";
            $param = [":daily"=>$date,":user_id"=>$user_id];
        }elseif($day_time =="yesterday"){
            $date = intval(date("Ymd",strtotime("yesterday")));
            $sql = "select broker_1_rate,broker_2_rate,broker_3_rate,broker_1_user,broker_2_user,broker_3_user,brokerage_1,".
                "brokerage_2,brokerage_3,broker_1_bet,broker_2_bet,broker_3_bet from daily_user_brokerage where daily=:daily and user_id=:user_id";
            $param = [":daily"=>$date,":user_id"=>$user_id];
        }elseif($day_time =="this_week"){
            //本周
            $date = intval(date("oW",strtotime("this week")));
            $sql = "select broker_1_user,broker_2_user,broker_3_user,brokerage_1,".
                "brokerage_2,brokerage_3,broker_1_bet,broker_2_bet,broker_3_bet from weekly_user_brokerage where weekly=:weekly and user_id=:user_id";
            $param = [":weekly"=>$date,":user_id"=>$user_id];
            //本周比例
            $start = intval(date('Ymd',strtotime(' -1 monday')));
            $end =  intval(date("Ymd",strtotime("today")));
            $sqls = "select max(broker_1_rate) as broker_1_rate,max(broker_2_rate) as broker_2_rate,max(broker_3_rate) as broker_3_rate ".
                "from daily_user_brokerage where daily between $start and $end and user_id=:user_id";
            $params = [":user_id"=>$user_id];

        }elseif($day_time =="last_week"){
            //上周
            $date = intval(date("oW",strtotime("last week")));
            $sql = "select broker_1_user,broker_2_user,broker_3_user,brokerage_1,".
                "brokerage_2,brokerage_3,broker_1_bet,broker_2_bet,broker_3_bet from weekly_user_brokerage where weekly=:weekly and user_id=:user_id";
            $param = [":weekly"=>$date,":user_id"=>$user_id];
            //上周比例
            $start = intval(date('Ymd',strtotime(' -2 monday')));
            $end =  intval(date("Ymd",strtotime("-1 sunday")));
            $sqls = "select max(broker_1_rate) as broker_1_rate,max(broker_2_rate) as broker_2_rate,max(broker_3_rate) as broker_3_rate ".
                "from daily_user_brokerage where daily between $start and $end and user_id=:user_id";
            $params = [":user_id"=>$user_id];

        }elseif($day_time == "this_month"){
            //本月
            $date = intval(date("Ym",strtotime("this month")));
            $sql = "select broker_1_user,broker_2_user,broker_3_user,brokerage_1,".
                "brokerage_2,brokerage_3,broker_1_bet,broker_2_bet,broker_3_bet from monthly_user_brokerage where monthly=:monthly and user_id=:user_id";
            $param = [":monthly"=>$date,":user_id"=>$user_id];
            //本月比例
            $start = intval(date('Ym01',strtotime(' today')));
            $end =  intval(date("Ymd",strtotime("today")));
            $sqls = "select max(broker_1_rate) as broker_1_rate,max(broker_2_rate) as broker_2_rate,max(broker_3_rate) as broker_3_rate ".
                "from daily_user_brokerage where daily between $start and $end and user_id=:user_id";
            $params = [":user_id"=>$user_id];

        }elseif($day_time == "last_month"){
            $date = intval(date("Ym",strtotime("-1 month")));
            //上月
            $sql = "select broker_1_user,broker_2_user,broker_3_user,brokerage_1,".
                "brokerage_2,brokerage_3,broker_1_bet,broker_2_bet,broker_3_bet from monthly_user_brokerage where monthly=:monthly and user_id=:user_id";
            $param = [":monthly"=>$date,":user_id"=>$user_id];
            //上月比例
            $start = intval(date('Ym01',strtotime(' -1 month')));
            $end =  intval(date("Ymt",strtotime("-1 month")));
            $sqls = "select max(broker_1_rate) as broker_1_rate,max(broker_2_rate) as broker_2_rate,max(broker_3_rate) as broker_3_rate ".
                "from daily_user_brokerage where daily between $start and $end and user_id=:user_id";
            $params = [":user_id"=>$user_id];
        }else{
            //昨天
            $context->reply(["status"=>204,"msg"=>"参数错误"]);
            return;
        }
        $list = iterator_to_array($mysql->query($sql,$param));
        if(!empty($sqls) && !empty($params)){
            foreach ($mysql->query($sqls,$params) as $rows){
                $broker_1_rate = $rows["broker_1_rate"];
                $broker_2_rate = $rows["broker_2_rate"];
                $broker_3_rate = $rows["broker_3_rate"];
            }
        }
        $lists = [];
        $brokerage = 0;
        $count_bet = 0;
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $lists["broker_1_rate"] = !empty($val["broker_1_rate"]) ? $val["broker_1_rate"] :$broker_1_rate;
                $lists["broker_2_rate"] = !empty($val["broker_2_rate"]) ? $val["broker_2_rate"] :$broker_2_rate;
                $lists["broker_3_rate"] = !empty($val["broker_3_rate"]) ? $val["broker_3_rate"] :$broker_3_rate;
                $lists["broker_1_user"] = !empty($val["broker_1_user"]) ? $val["broker_1_user"] : 0;
                $lists["broker_2_user"] = !empty($val["broker_2_user"]) ? $val["broker_2_user"] : 0;
                $lists["broker_3_user"] = !empty($val["broker_3_user"]) ? $val["broker_3_user"] : 0;
                $lists["brokerage_1"] = !empty($val["brokerage_1"]) ? $val["brokerage_1"] : 0;
                $lists["brokerage_2"] = !empty($val["brokerage_2"]) ? $val["brokerage_2"] : 0;
                $lists["brokerage_3"] = !empty($val["brokerage_3"]) ? $val["brokerage_3"] : 0;
                $lists["broker_1_bet"] = !empty($val["broker_1_bet"]) ? $val["broker_1_bet"] : 0;
                $lists["broker_2_bet"] = !empty($val["broker_2_bet"]) ? $val["broker_2_bet"] : 0;
                $lists["broker_3_bet"] = !empty($val["broker_3_bet"]) ? $val["broker_3_bet"] : 0;
                $brokerage = $val["brokerage_1"] + $val["brokerage_2"] + $val["brokerage_3"];
                $count_bet = $val["broker_1_bet"] + $val["broker_2_bet"] + $val["broker_3_bet"];
            }
        }else{
            $lists["broker_1_rate"] = 0;
            $lists["broker_2_rate"] = 0;
            $lists["broker_3_rate"] = 0;
            $lists["broker_1_user"] = 0;
            $lists["broker_2_user"] = 0;
            $lists["broker_3_user"] = 0;
            $lists["brokerage_1"] = 0;
            $lists["brokerage_2"] =  0;
            $lists["brokerage_3"] = 0;
            $lists["broker_1_bet"] =  0;
            $lists["broker_2_bet"] = 0;
            $lists["broker_3_bet"] =  0;
            $brokerage = 0;
            $count_bet = 0;
        }
        $context->reply(["status"=>200,"msg"=>"获取成功","brokerage"=>$brokerage,"count_bet"=>$count_bet,"list"=>$lists]);
    }
}