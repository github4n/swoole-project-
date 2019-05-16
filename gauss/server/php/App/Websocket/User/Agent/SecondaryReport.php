<?php
namespace App\Websocket\User\Agent;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--代理中心--下级报表--二级
 * User/Agent/SecondaryReport {"date":"today"}
 * date:yesterday;today,thisWeek;lastWeek;thisMonth;lastMonth
 * */

class SecondaryReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }
        
        $data = $context->getData();
        $date = isset($data['date'])?$data['date']:'';
        $userId = $context->getInfo('UserId');
        //查询当前用户的二级下线
        $mysql = $config->data_user;
        $sql = 'SELECT user_id,user_key,deal_key,layer_id FROM user_info_intact WHERE broker_2_id=:broker_2_id';
        $param = [':broker_2_id' => $userId];
        foreach ($mysql->query($sql, $param) as $row) {
            $primaryList[] = $row;
        }
        $reportMysql = $config->data_report;
        $reportList = [];
        $primaryList = [];
        $AllNum = 0;
        $AddNum = 0;
        $Num = 0;
        switch ($date) {
            case 'today':
                $today = strtotime('today');
                $today_start = $today;
                $today_stop = strtotime(date('Ymd', $today) . ' 23:59:59');

                //查询当前用户的一级下线
                $sql = 'SELECT user_id,user_key,deal_key,layer_id FROM user_info_intact WHERE broker_1_id=:broker_1_id AND register_time BETWEEN :today_start AND :today_stop';
                $param = [
                    ':broker_1_id' => $userId,
                    ':today_start' => $today_start,
                    ':today_stop' => $today_stop
                ];
                $primaryList = [];
                foreach ($mysql->query($sql,$param) as $row)
                {
                    $primaryList[] = $row;
                }

                //今日新增人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS todayAdd FROM user_info WHERE broker_2_id=:broker_2_id AND register_time BETWEEN :today_start AND :today_stop';
                $params = [
                    ':broker_2_id' => $userId,
                    ':today_start' => $today_start,
                    ':today_stop' => $today_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AddNum = isset($row['todayAdd'])?$row['todayAdd']:0;
                }

                //今日下级总人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS todayAll FROM user_info WHERE broker_2_id=:broker_2_id AND register_time<=:today_stop';
                $params = [
                    'broker_2_id' => $userId,
                    ':today_stop' => $today_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AllNum = isset($row['todayAll'])?$row['todayAll']:0;
                }

                //今日下级人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS todayNum FROM user_info WHERE login_time BETWEEN :today_start AND :today_stop AND broker_2_id=:broker_2_id';
                $params = [
                    ':today_start' => $today_start,
                    ':today_stop' => $today_stop,
                    ':broker_2_id' => $userId
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $Num = isset($row['todayNum'])?$row['todayNum']:0;
                }

                //二级下线的当天佣金
                //提成比例
                $rate_sql = 'SELECT broker_2_rate FROM daily_user_brokerage WHERE daily=:daily AND user_id=:user_id';
                $param = [
                    ':daily' => date('Ymd', $today),
                    ':user_id' => $userId
                ];
                foreach ($reportMysql->query($rate_sql, $param) as $row) {
                    $broker_rate = $row['broker_2_rate'];
                }
                $broker_rate = isset($broker_rate)?$broker_rate:0;

                //查询下线的当天打码量
                $bet_sql = 'SELECT SUM(wager_amount)  AS bet_all,daily FROM daily_user WHERE user_id=:user_id AND daily=:daily';

                foreach ($primaryList as $k => $v)
                {
                    $reportList[$k]['user_key'] = $v['user_key'];
                    $bet_param = [
                        ':user_id' => $v['user_id'],
                        ':daily' => date('Ymd', $today)
                    ];

                    foreach ($reportMysql->query($bet_sql, $bet_param) as $bet)
                    {
                        $reportList[$k]['bet_all'] = $bet['bet_all'];
                        $reportList[$k]['time'] = date('Y-m-d', strtotime($bet['daily']));
                    }

                    $reportList[$k]['brokerage'] = substr(sprintf("%.3f", $broker_rate * $reportList[$k]['bet_all']), 0, -1);
                    $reportList[$k]['broker_rate'] = $broker_rate;
                }

                $context->reply(['status' => 200,'msg' => '获取成功',
                    'AddNum' => $AddNum,
                    'AllNum' => $AllNum,
                    'Num' => $Num,
                    'list' => $reportList
                ]);
                break;
            case 'thisWeek':
                //本周新增人数
                $today = strtotime('today');
                $thisWeek = strtotime('this week');
                $thisWeek_start = strtotime(date('Ymd', $thisWeek) . ' 00:00:00');
                $thisWeek_stop = strtotime(date('Ymd', $thisWeek) . ' 00:00:00') + 6 * 86400;

                //查询当前用户的一级下线
                $sql = 'SELECT user_id,user_key,deal_key,layer_id FROM user_info_intact WHERE broker_1_id=:broker_1_id AND register_time BETWEEN :thisWeek_start AND :thisWeek_stop';
                $param = [
                    ':broker_1_id' => $userId,
                    ':thisWeek_start' => $thisWeek_start,
                    ':thisWeek_stop' => $thisWeek_stop
                ];
                $primaryList = [];
                foreach ($mysql->query($sql,$param) as $row)
                {
                    $primaryList[] = $row;
                }

                $sql = 'SELECT COUNT(DISTINCT user_id) AS thisWeekAdd FROM user_info WHERE broker_2_id=:broker_2_id AND register_time BETWEEN :thisWeek_start AND :thisWeek_stop';
                //$context->reply($sql);
                $params = [
                    ':broker_2_id' => $userId,
                    ':thisWeek_start' => $thisWeek_start,
                    ':thisWeek_stop' => $thisWeek_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AddNum = isset($row['thisWeekAdd'])?$row['thisWeekAdd']:0;
                }

                //本周下级总人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS thisWeekAll FROM user_info WHERE broker_2_id=:broker_2_id AND register_time<=:thisWeek_stop';
                $params = [
                    'broker_2_id' => $userId,
                    ':thisWeek_stop' => $thisWeek_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AllNum = isset($row['thisWeekAll'])?$row['thisWeekAll']:0;
                }

                //本周下级人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS thisWeekNum FROM user_info WHERE login_time BETWEEN :thisWeek_start AND :thisWeek_stop AND broker_2_id=:broker_2_id';
                $params = [
                    ':thisWeek_start' => $thisWeek_start,
                    ':thisWeek_stop' => $thisWeek_stop,
                    ':broker_2_id' => $userId
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $Num = isset($row['thisWeekNum'])?$row['thisWeekNum']:0;
                }

                //二级下线的本周佣金

                //提成比例
                $rate_sql = 'SELECT broker_2_rate FROM daily_user_brokerage WHERE daily=:daily AND user_id=:user_id';
                $rate_param = [
                    ':daily' => date('Ymd', $today),
                    ':user_id' => $userId
                ];
                //$context->reply($rate_sql);
                foreach ($reportMysql->query($rate_sql, $rate_param) as $rate) {
                    $broker_rate = $rate['broker_2_rate'];
                }
                $broker_rate = isset($broker_rate)?$broker_rate:0;

                //查询下线的本周打码量
                $bet_sql = 'SELECT SUM(wager_amount) AS bet_all,weekly FROM weekly_user WHERE user_id=:user_id AND weekly=:weekly';

                foreach ($primaryList as $k => $v)
                {
                    $reportList[$k]['user_key'] = isset($v['user_key']) ? $v['user_key'] : '0';
                    $bet_param = [
                        ':user_id' => $v['user_id'],
                        ':weekly' => date('oW', $thisWeek)
                    ];
                    foreach ($reportMysql->query($bet_sql, $bet_param) as $bet)
                    {
                        $reportList[$k]['bet_all'] = empty($bet['bet_amount']) ? 0 : $bet['bet_amount'];
                        $reportList[$k]['time'] = empty($bet['weekly']) ? date('Y-m-d', strtotime('this week')) : date('Y-m-d', strtotime($bet['weekly']));
                    }

                    $reportList[$k]['brokerage'] = substr(sprintf("%.3f", $broker_rate * $reportList[$k]['bet_all']), 0, -1);
                    $reportList[$k]['broker_rate'] = $broker_rate;
                }

                $context->reply(['status' => 200,'msg' => '获取成功',
                    'AddNum' => $AddNum,
                    'AllNum' => $AllNum,
                    'Num' => $Num,
                    'list' => $reportList
                ]);
                break;
            case 'lastWeek':
                //上周新增人数
                $lastWeek = strtotime('last week');
                $lastWeek_start = strtotime(date('Ymd', $lastWeek) . ' 00:00:00');
                $lastWeek_stop = $lastWeek_start + 6 * 86400;

                //查询当前用户的一级下线
                $sql = 'SELECT user_id,user_key,deal_key,layer_id FROM user_info_intact WHERE broker_1_id=:broker_1_id AND register_time BETWEEN :lastWeek_start AND :lastWeek_stop';
                $param = [
                    ':broker_1_id' => $userId,
                    ':lastWeek_start' => $lastWeek_start,
                    ':lastWeek_stop' => $lastWeek_stop
                ];
                $primaryList = [];
                foreach ($mysql->query($sql,$param) as $row)
                {
                    $primaryList[] = $row;
                }

                $sql = 'SELECT COUNT(DISTINCT user_id) AS lastWeekAdd FROM user_info WHERE broker_2_id=:broker_2_id AND register_time BETWEEN :lastWeek_start AND :lastWeek_stop';
                $params = [
                    ':broker_2_id' => $userId,
                    ':lastWeek_start' => $lastWeek_start,
                    ':lastWeek_stop' => $lastWeek_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AddNum = isset($row['lastWeekAdd'])?$row['lastWeekAdd']:0;
                }

                //上周下级总人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS lastWeekAll FROM user_info WHERE broker_2_id=:broker_2_id AND register_time<=:lastWeek_stop';
                $params = [
                    'broker_2_id' => $userId,
                    ':lastWeek_stop' => $lastWeek_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AllNum = isset($row['lastWeekAll'])?$row['lastWeekAll']:0;
                }

                //上周下级人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS lastWeekNum FROM user_info WHERE login_time BETWEEN :lastWeek_start AND :lastWeek_stop AND broker_2_id=:broker_2_id';
                $params = [
                    ':lastWeek_start' => $lastWeek_start,
                    ':lastWeek_stop' => $lastWeek_stop,
                    ':broker_2_id' => $userId
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $Num = isset($row['lastWeekNum'])?$row['lastWeekNum']:0;
                }

                //二级下线的本周佣金

                //提成比例
                $rate_sql = 'SELECT broker_2_rate FROM daily_user_brokerage WHERE daily=:daily AND user_id=:user_id';
                $rate_param = [
                    ':daily' => date('Ymd', $lastWeek),
                    ':user_id' => $userId
                ];
                //$context->reply($rate_sql);
                foreach ($reportMysql->query($rate_sql, $rate_param) as $rate) {
                    $broker_rate = $rate['broker_2_rate'];
                }
                $broker_rate = isset($broker_rate)?$broker_rate:0;

                //查询下线的上周打码量
                $bet_sql = 'SELECT SUM(wager_amount) AS bet_all,weekly FROM weekly_user WHERE user_id=:user_id AND weekly=:weekly';

                foreach ($primaryList as $k => $v)
                {
                    $reportList[$k]['user_key'] = isset($v['user_key']) ? $v['user_key'] : '0';
                    $bet_param = [
                        ':user_id' => $v['user_id'],
                        ':weekly' => date('oW', $lastWeek)
                    ];
                    foreach ($reportMysql->query($bet_sql, $bet_param) as $bet)
                    {
                        $reportList[$k]['bet_all'] = empty($bet['bet_amount']) ? 0 : $bet['bet_amount'];
                        $reportList[$k]['time'] = empty($bet['weekly']) ? date('Y-m-d', strtotime('this week')) : date('Y-m-d', strtotime($bet['weekly']));
                    }

                    $reportList[$k]['brokerage'] = substr(sprintf("%.3f", $broker_rate * $reportList[$k]['bet_all']), 0, -1);
                    $reportList[$k]['broker_rate'] = $broker_rate;
                }

                $context->reply(['status' => 200,'msg' => '获取成功',
                    'AddNum' => $AddNum,
                    'AllNum' => $AllNum,
                    'Num' => $Num,
                    'list' => $reportList
                ]);
                break;
            case 'thisMonth':
                //本月新增人数
                $thisMonth = strtotime(date('Ym', time()));
                $thisMonth_start = strtotime(date('Ym', $thisMonth) . '01 00:00:00');
                $thisMonth_stop = strtotime(date('Ymt', $thisMonth) . ' 23:59:59');

                //查询当前用户的一级下线
                $sql = 'SELECT user_id,user_key,deal_key,layer_id FROM user_info_intact WHERE broker_1_id=:broker_1_id AND register_time BETWEEN :thisMonth_start AND :thisMonth_stop';
                $param = [
                    ':broker_1_id' => $userId,
                    ':thisMonth_start' => $thisMonth_start,
                    ':thisMonth_stop' => $thisMonth_stop
                ];
                $primaryList = [];
                foreach ($mysql->query($sql,$param) as $row)
                {
                    $primaryList[] = $row;
                }

                $sql = 'SELECT COUNT(DISTINCT user_id) AS thisMonthAdd FROM user_info WHERE broker_2_id=:broker_2_id AND register_time BETWEEN :thisMonth_start AND :thisMonth_stop';
                $params = [
                    ':broker_2_id' => $userId,
                    ':thisMonth_start' => $thisMonth_start,
                    ':thisMonth_stop' => $thisMonth_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AddNum = isset($row['thisMonthAdd'])?$row['thisMonthAdd']:0;
                }

                //本月下级总人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS thisMonthAll FROM user_info WHERE broker_2_id=:broker_2_id AND register_time<=:thisMonth_stop';
                $params = [
                    'broker_2_id' => $userId,
                    ':thisMonth_stop' => $thisMonth_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AllNum = isset($row['thisMonthAll'])?$row['thisMonthAll']:0;
                }

                //本月下级人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS thisMonthNum FROM user_info WHERE login_time BETWEEN :thisMonth_start AND :thisMonth_stop AND broker_2_id=:broker_2_id';
                $params = [
                    ':thisMonth_start' => $thisMonth_start,
                    ':thisMonth_stop' => $thisMonth_stop,
                    ':broker_2_id' => $userId
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $Num = isset($row['thisMonthNum'])?$row['thisMonthNum']:0;
                }

                //二级下线的本月佣金

                //提成比例
                $rate_sql = 'SELECT broker_2_rate FROM daily_user_brokerage WHERE daily=:daily AND user_id=:user_id';
                $rate_param = [
                    ':daily' => date('Ymd', $thisMonth),
                    ':user_id' => $userId
                ];
                //$context->reply($rate_sql);
                foreach ($reportMysql->query($rate_sql, $rate_param) as $rate) {
                    $broker_rate['broker_rate'] = $rate['broker_2_rate'];
                }
                $broker_rate = isset($broker_rate)?$broker_rate:0;

                //查询下线的本月打码量
                $bet_sql = 'SELECT SUM(wager_amount) AS bet_all,monthly FROM monthly_user WHERE user_id=:user_id AND monthly=:monthly';

                foreach ($primaryList as $k => $v)
                {
                    $reportList[$k]['user_key'] = isset($v['user_key']) ? $v['user_key'] : '0';
                    $bet_param = [
                        ':user_id' => $v['user_id'],
                        ':monthly' => date('Ym', $thisMonth)
                    ];
                    foreach ($reportMysql->query($bet_sql, $bet_param) as $bet)
                    {
                        $reportList[$k]['bet_all'] = empty($bet['bet_amount']) ? 0 : $bet['bet_amount'];
                        $reportList[$k]['time'] = empty($bet['monthly']) ? date('Y-m-d', strtotime('-1 month')) : date('Y-m-d', strtotime($bet['monthly']));
                    }

                    $reportList[$k]['brokerage'] = substr(sprintf("%.3f", $broker_rate * $reportList[$k]['bet_all']), 0, -1);
                    $reportList[$k]['broker_rate'] = $broker_rate;
                }

                $context->reply(['status' => 200,'msg' => '获取成功',
                    'AddNum' => $AddNum,
                    'AllNum' => $AllNum,
                    'Num' => $Num,
                    'list' => $reportList
                ]);
                break;
            case 'lastMonth':
                //上月新增人数
                $lastMonth = strtotime('last month');
                $lastMonth_start = strtotime(date('Ym', $lastMonth) . '01 00:00:00');
                $lastMonth_stop = strtotime(date('Ymt', $lastMonth) . '23:59:59');

                //查询当前用户的一级下线
                $sql = 'SELECT user_id,user_key,deal_key,layer_id FROM user_info_intact WHERE broker_1_id=:broker_1_id AND register_time BETWEEN :lastMonth_start AND :lastMonth_stop';
                $param = [
                    ':broker_1_id' => $userId,
                    ':lastMonth_start' => $lastMonth_start,
                    ':lastMonth_stop' => $lastMonth_stop
                ];
                $primaryList = [];
                foreach ($mysql->query($sql,$param) as $row)
                {
                    $primaryList[] = $row;
                }

                $sql = 'SELECT COUNT(DISTINCT user_id) AS lastMonthAdd FROM user_info WHERE broker_2_id=:broker_2_id AND register_time BETWEEN :lastMonth_start AND :lastMonth_stop';
                $params = [
                    ':broker_2_id' => $userId,
                    ':lastMonth_start' => $lastMonth_start,
                    ':lastMonth_stop' => $lastMonth_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AddNum = isset($row['lastMonthAdd'])?$row['lastMonthAdd']:0;
                }

                //上月下级总人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS lastMonthAll FROM user_info WHERE broker_2_id=:broker_2_id AND register_time<=:lastMonth_stop';
                $params = [
                    'broker_2_id' => $userId,
                    ':lastMonth_stop' => $lastMonth_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AllNum = isset($row['lastMonthAll'])?$row['lastMonthAll']:0;
                }

                //本月上级人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS lastMonthNum FROM user_info WHERE login_time BETWEEN :lastMonth_start AND :lastMonth_stop AND broker_2_id=:broker_2_id';
                $params = [
                    ':lastMonth_start' => $lastMonth_start,
                    ':lastMonth_stop' => $lastMonth_stop,
                    ':broker_2_id' => $userId
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $Num = isset($row['lastMonthNum'])?$row['lastMonthNum']:0;
                }

                //二级下线的上月佣金

                //提成比例
                $rate_sql = 'SELECT broker_2_rate FROM daily_user_brokerage WHERE daily=:daily AND user_id=:user_id';
                $rate_param = [
                    ':daily' => date('Ymd', $lastMonth),
                    ':user_id' => $userId
                ];
                //$context->reply($rate_sql);
                foreach ($reportMysql->query($rate_sql, $rate_param) as $rate) {
                    $broker_rate = $rate['broker_2_rate'];
                }
                $broker_rate = isset($broker_rate)?$broker_rate:0;

                //查询下线的上月打码量
                $bet_sql = 'SELECT SUM(wager_amount) AS bet_all,monthly FROM monthly_user WHERE user_id=:user_id AND monthly=:monthly';

                foreach ($primaryList as $k => $v)
                {
                    $reportList[$k]['user_key'] = isset($v['user_key']) ? $v['user_key'] : '0';
                    $bet_param = [
                        ':user_id' => $v['user_id'],
                        ':monthly' => date('Ym', $lastMonth)
                    ];
                    foreach ($reportMysql->query($bet_sql, $bet_param) as $bet)
                    {
                        $reportList[$k]['bet_all'] = empty($bet['bet_amount']) ? 0 : $bet['bet_amount'];
                        $reportList[$k]['time'] = empty($bet['monthly']) ? date('Y-m-d', strtotime('-1 month')) : date('Y-m-d', strtotime($bet['monthly']));
                    }

                    $reportList[$k]['brokerage'] = substr(sprintf("%.3f", $broker_rate * $reportList[$k]['bet_all']), 0, -1);
                    $reportList[$k]['broker_rate'] = $broker_rate;
                }

                $context->reply(['status' => 200,'msg' => '获取成功',
                    'AddNum' => $AddNum,
                    'AllNum' => $AllNum,
                    'Num' => $Num,
                    'list' => $reportList
                ]);
                break;
            default:
                $yesterday = strtotime('yesterday');
                $yesterday_start = $yesterday;
                $yesterday_stop = strtotime(date('Ymd', $yesterday) . ' 23:59:59');

                //查询当前用户的一级下线
                $sql = 'SELECT user_id,user_key,deal_key,layer_id FROM user_info_intact WHERE broker_1_id=:broker_1_id AND register_time BETWEEN :yesterday_start AND :yesterday_stop';
                $param = [
                    ':broker_1_id' => $userId,
                    ':yesterday_start' => $yesterday_start,
                    ':yesterday_stop' => $yesterday_stop
                ];
                $primaryList = [];
                foreach ($mysql->query($sql,$param) as $row)
                {
                    $primaryList[] = $row;
                }

                //昨日新增人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS yesterdayAdd FROM user_info WHERE broker_2_id=:broker_2_id AND register_time BETWEEN :yesterday_start AND :yesterday_stop';
                $params = [
                    ':broker_2_id' => $userId,
                    ':yesterday_start' => $yesterday_start,
                    ':yesterday_stop' => $yesterday_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AddNum = isset($row['yesterdayAdd'])?$row['yesterdayAdd']:0;
                }

                //昨日下级总人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS yesterdayAll FROM user_info WHERE broker_2_id=:broker_2_id AND register_time<=:yesterday_stop';
                $params = [
                    'broker_2_id' => $userId,
                    ':yesterday_stop' => $yesterday_stop
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $AllNum = isset($row['yesterdayAll'])?$row['yesterdayAll']:0;
                }

                //昨日下级人数
                $sql = 'SELECT COUNT(DISTINCT user_id) AS yesterdayNum FROM user_info WHERE login_time BETWEEN :yesterday_start AND :yesterday_stop AND broker_2_id=:broker_2_id';
                $params = [
                    ':yesterday_start' => $yesterday_start,
                    ':yesterday_stop' => $yesterday_stop,
                    ':broker_2_id' => $userId
                ];
                foreach ($mysql->query($sql, $params) as $row) {
                    $Num = isset($row['yesterdayNum'])?$row['yesterdayNum']:0;
                }

                //二级别下线的当天佣金
                //提成比例
                $rate_sql = 'SELECT broker_2_rate FROM daily_user_brokerage WHERE daily=:daily AND user_id=:user_id';
                $param = [
                    ':daily' => date('Ymd', $yesterday),
                    ':user_id' => $userId
                ];
                foreach ($reportMysql->query($rate_sql, $param) as $row) {
                    $broker_rate = $row['broker_2_rate'];
                }
                $broker_rate = isset($broker_rate)?$broker_rate:0;
                //查询下线的当天佣金以及打码量
                $bet_sql = 'SELECT SUM(wager_amount) AS bet_all,daily  FROM daily_user WHERE user_id=:user_id AND daily=:daily';

                foreach ($primaryList as $k => $v)
                {
                    $reportList[$k]['user_key'] = isset($v['user_key']) ? $v['user_key'] : '0';
                    $bet_param = [
                        ':user_id' => $v['user_id'],
                        ':daily' => date('Ymd', $yesterday)
                    ];
                    foreach ($reportMysql->query($bet_sql, $bet_param) as $bet)
                    {
                        $reportList[$k]['bet_all'] = empty($bet['bet_amount']) ? 0 : $bet['bet_amount'];
                        $reportList[$k]['time'] = empty($bet['daily']) ? date('Y-m-d', strtotime('-1 month')) : date('Y-m-d', strtotime($bet['daily']));
                    }
                    $reportList[$k]['brokerage'] = substr(sprintf("%.3f", $broker_rate * $reportList[$k]['bet_all']), 0, -1);
                    $reportList[$k]['broker_rate'] = $broker_rate;
                }
                $context->reply(['status' => 200,'msg' => '获取成功',
                    'AddNum' => $AddNum,
                    'AllNum' => $AllNum,
                    'Num' => $Num,
                    'list' => $reportList
                ]);
        }
    }
        
}
