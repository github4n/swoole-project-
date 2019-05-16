<?php
/**
 * OperatingReport.php
 *
 * @description   报表查询 - 经营统计报表
 * @Author  Luis
 * @date  2019-04-07
 * @links  ReportQuery/OperatingReport {"date":"2019-04-01"}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
namespace Site\Websocket\ReportQuery;
use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class OperatingReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config) {
        $staffId = $context->getInfo('StaffId'); // 员工Id
        $masterId = $context->getInfo('MasterId'); // 主帐号Id
        $staffGrade = $context->getInfo('StaffGrade'); // 员工等级
        $masterId = $masterId > 0 ? $masterId : $staffId;
        // 检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array('report_money', $auth) || $staffGrade == 3) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        $mysqlreport = $config->data_report;
        $mysqlstaff = $config->data_staff;
        $data = $context->getData();
        $date = isset($data['date']) ? $data['date'] : '';
        $param = [];
        $total = [];
        $dataLottery = [];
        $externaldata = ['fg' => [], 'ag' => [], 'lb' => [], 'ky' => []];
        $level = $staffGrade == 0 ? 'major_id' : ($staffGrade == 1 ? 'minor_id' : 'agent_id');
        $thislevel = $staffGrade == 0 ? '' : ($staffGrade == 1 ? 'major_id' : 'minor_id');
        // 查询用户下的会员人数
        $paramAgent = [];
        if($staffGrade > 0) {
            $sqlAgent = 'Select `' . $level . '` As `staff_id`,`agent_id` From `staff_struct_agent` Where `' . $thislevel . '` = :staffId';
            $paramAgent = [':staffId' => $staffId];
        } else {
            $sqlAgent = 'Select `' . $level . '` As `staff_id`,`agent_id` From `staff_struct_agent`';
        }
        $userAll = [];
        $mysqlUser = $config->data_user;
        if($date == "thisWeek"){
            $registertime = strtotime(date('Y-m-d',(time()+(7-(date('w',time())==0?7:date('w',time())))*24*3600)))+86399;
        } elseif($date == "lastWeek"){
            $registertime = strtotime(date('Y-m-d',strtotime('-1 sunday', time())))+86399;
        } elseif($date == "thisMonth"){
            $registertime = strtotime(date('Y-m-d',strtotime(date('Y-m', time()).'-'.date('t', time()).' 00:00:00')))+86399;
        } elseif($date == "lastMonth"){
            $registertime = strtotime(date('Y-m-d',strtotime(date('Y-m', time()).'-01 00:00:00')))-1;
        } else{
            $registertime = strtotime($date)+86399;
        }
        foreach($mysqlstaff->query($sqlAgent, $paramAgent) as $v) {
            // 初始
            if(!isset($userAll[$v['staff_id']])) {
                $userAll[$v['staff_id']] = 0;
            }
            $parm = [];
            $sql = 'Select Count(`user_id`) As `count` From `user_info_intact` Where `agent_id` = :agentId and register_time <= :registertime';
            $parm[':agentId']  = $v['agent_id'];
            $parm[':registertime'] = $registertime;
            $userCount = iterator_to_array($mysqlUser->query($sql, $parm));
            $userAll[$v['staff_id']] += $userCount[0]['count'];
        }

        switch ($staffGrade){
            case 0:
                $levelsql = "SELECT major_id As `staff_id`,major_name As `staff_name` FROM `staff_struct_major` where 1=1";
                break;
            case 1:
                $levelsql = "SELECT minor_id As `staff_id`,minor_name As `staff_name` FROM `staff_struct_minor` where major_id = :staffId";
                $param[':staffId'] = $staffId;
                break;
            case 2:
                $levelsql = "SELECT agent_id As `staff_id`,agent_name As `staff_name` FROM `staff_struct_agent` where minor_id = :staffId";
                $param[':staffId'] = $staffId;
                break;
        }
        $staffArray = iterator_to_array($mysqlstaff->query($levelsql, $param));
        $staffIdArray = implode(',', array_unique(array_column($staffArray, 'staff_id')));

        $param = [];
        if(!empty($staffArray)) {
            if($date == "yesterday"){
                $time = intval(date("Ymd",strtotime("yesterday")));
                $totalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(Round(sum(wager_amount),2),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,sum(coupon_amount) as coupon_amount,sum(subsidy_amount) as subsidy_amount,sum(brokerage_amount) as brokerage_amount,Round(sum(rebate_amount),2) as rebate_amount from daily_staff where Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . "  ";
                $lotterysql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from daily_staff_lottery where  Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . " ";
                $lotteryusersql = "SELECT ".$level." as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as bet_user from daily_user_lottery where  Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . " ";
                $externalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,interface_key,sum(wager_amount - bonus_amount) as profit_amount from daily_staff_external where Find_In_Set($level, :staffDate) and daily = :time GROUP BY interface_key,daily," . $level . "";
                $exusersql = "SELECT " . $level . " as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as exbet_user,interface_key from daily_user_external where Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . ",interface_key";
                $param[':time'] = $time;
                $param[':staffDate'] = empty($staffIdArray)?"":$staffIdArray;
            } elseif ($date == "thisWeek"){
                $time = intval(date("oW",strtotime("today")));
                $totalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,sum(coupon_amount) as coupon_amount,sum(subsidy_amount) as subsidy_amount,sum(brokerage_amount) as brokerage_amount,Round(sum(rebate_amount),2) as rebate_amount from weekly_staff where Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . "  ";
                $lotterysql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from weekly_staff_lottery where  Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . " ";
                $lotteryusersql = "SELECT ".$level." as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as bet_user,count(distinct (if(wager_amount>0,user_id,null))) as wager_user from weekly_user_lottery where  Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . " ";
                $exusersql = "SELECT " . $level . " as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as exbet_user,interface_key from weekly_user_external where Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . ",interface_key";
                $externalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,interface_key,sum(wager_amount - bonus_amount) as profit_amount from weekly_staff_external where Find_In_Set($level, :staffDate) and weekly = :time GROUP BY interface_key,weekly," . $level . "";
                $param[':time'] = $time;
                $param[':staffDate'] = empty($staffIdArray)?"":$staffIdArray;
            } elseif ($date == "lastWeek"){
                $time = intval(date("oW",strtotime("-1 week")));
                $totalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,sum(coupon_amount) as coupon_amount,sum(subsidy_amount) as subsidy_amount,sum(brokerage_amount) as brokerage_amount,Round(sum(rebate_amount),2) as rebate_amount from weekly_staff where Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . "  ";
                $lotterysql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from weekly_staff_lottery where  Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . " ";
                $lotteryusersql = "SELECT ".$level." as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as bet_user from weekly_user_lottery where  Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . " ";
                $externalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,interface_key,sum(wager_amount - bonus_amount) as profit_amount from weekly_staff_external where Find_In_Set($level, :staffDate) and weekly = :time GROUP BY interface_key,weekly," . $level . "";
                $exusersql = "SELECT " . $level . " as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as exbet_user,interface_key from weekly_user_external where Find_In_Set($level, :staffDate) and weekly = :time GROUP BY weekly," . $level . ",interface_key";
                $param[':time'] = $time;
                $param[':staffDate'] = empty($staffIdArray)?"":$staffIdArray;
            } elseif ($date == "thisMonth"){
                $time = intval(date("Ym",strtotime("today")));
                $totalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,sum(coupon_amount) as coupon_amount,sum(subsidy_amount) as subsidy_amount,sum(brokerage_amount) as brokerage_amount,Round(sum(rebate_amount),2) as rebate_amount from monthly_staff where Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . "  ";
                $lotterysql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from monthly_staff_lottery where  Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . " ";
                $lotteryusersql = "SELECT ".$level." as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as bet_user from monthly_user_lottery where  Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . " ";
                $externalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,interface_key,sum(wager_amount - bonus_amount) as profit_amount from monthly_staff_external where Find_In_Set($level, :staffDate) and monthly = :time GROUP BY interface_key,monthly," . $level . "";
                $exusersql = "SELECT " . $level . " as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as exbet_user,interface_key from monthly_user_external where Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . ",interface_key";
                $param[':time'] = $time;
                $param[':staffDate'] = empty($staffIdArray)?"":$staffIdArray;
            } elseif($date == "lastMonth"){
                $time = intval(date("Ym",strtotime("-1 month")));
                $totalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,sum(coupon_amount) as coupon_amount,sum(subsidy_amount) as subsidy_amount,sum(brokerage_amount) as brokerage_amount,Round(sum(rebate_amount),2) as rebate_amount from monthly_staff where Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . "  ";
                $lotterysql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from monthly_staff_lottery where  Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . " ";
                $lotteryusersql = "SELECT ".$level." as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as bet_user from monthly_user_lottery where  Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . " ";
                $externalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,interface_key,sum(wager_amount - bonus_amount) as profit_amount from monthly_staff_external where Find_In_Set($level, :staffDate) and monthly = :time GROUP BY interface_key,monthly," . $level . "";
                $exusersql = "SELECT " . $level . " as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as exbet_user,interface_key from monthly_user_external where Find_In_Set($level, :staffDate) and monthly = :time GROUP BY monthly," . $level . ",interface_key";
                $param[':time'] = $time;
                $param[':staffDate'] = empty($staffIdArray)?"":$staffIdArray;
            } elseif($date == "today"){
                $time = intval(date("Ymd",strtotime("today")));
                $totalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user ,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,sum(coupon_amount) as coupon_amount,sum(subsidy_amount) as subsidy_amount,sum(brokerage_amount) as brokerage_amount,Round(sum(rebate_amount),2) as rebate_amount from daily_staff where Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . "  ";
                $lotterysql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from daily_staff_lottery where  Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . " ";
                $lotteryusersql = "SELECT ".$level." as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as bet_user from daily_user_lottery where  Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . " ";
                $externalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,interface_key,sum(wager_amount - bonus_amount) as profit_amount from daily_staff_external where Find_In_Set($level, :staffDate) and daily = :time GROUP BY interface_key,daily," . $level . "";
                $exusersql = "SELECT " . $level . " as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as exbet_user,interface_key from daily_user_external where Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . ",interface_key";
                $param[':time'] = $time;
                $param[':staffDate'] = empty($staffIdArray)?"":$staffIdArray;
            } else {
                $time = intval(date("Ymd",strtotime($date)));
                $totalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user ,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,sum(coupon_amount) as coupon_amount,sum(subsidy_amount) as subsidy_amount,sum(brokerage_amount) as brokerage_amount,Round(sum(rebate_amount),2) as rebate_amount from daily_staff where Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . "  ";
                $lotterysql = "SELECT " . $level . "  As `staff_id`,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from daily_staff_lottery where  Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . " ";
                $lotteryusersql = "SELECT ".$level." as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as bet_user from daily_user_lottery where  Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . " ";
                $externalsql = "SELECT " . $level . "  As `staff_id`,sum(bet_user) as bet_user,sum(wager_count) as bet_count,Round(sum(bet_amount),2) as bet_amount,Round(sum(wager_amount),2) as wager_amount,Round(sum(bonus_amount),2) as bonus_amount,0 as coupon_amount,0 as brokerage_amount,sum(subsidy_amount) as subsidy_amount,interface_key,sum(wager_amount - bonus_amount) as profit_amount from daily_staff_external where Find_In_Set($level, :staffDate) and daily = :time GROUP BY interface_key,daily," . $level . "";
                $exusersql = "SELECT " . $level . " as staff_id,count(distinct (if(wager_amount>0,user_id,null))) as exbet_user,interface_key from daily_user_external where Find_In_Set($level, :staffDate) and daily = :time GROUP BY daily," . $level . ",interface_key";
                $param[':time'] = $time;
                $param[':staffDate'] = empty($staffIdArray)?"":$staffIdArray;
            }

            // 查询返回数据
            $totalDate = [];
            $lotteryDate = [];
            $lotteryUser = [];
            $externalDate = [];
            $externalUser = [];
            $user_all = isset($userAll[$v['staff_id']]) ? $userAll[$v['staff_id']] :0;
            foreach($mysqlreport->query($lotteryusersql,$param) as $v){
                $lotteryUser[$v['staff_id']] = $v;
                $bet_user[$v['staff_id']] = $v['bet_user'];
            }
            foreach($mysqlreport->query($exusersql,$param) as $v){
                $externalUser[$v['staff_id']] = $v;
                $exbet_user[$v['staff_id']] = $v['exbet_user'];
            }

            foreach($mysqlreport->query($totalsql,$param) as $v) {
                $totalDate[$v['staff_id']] = $v;
            }
            foreach($mysqlreport->query($lotterysql,$param) as $v) {
                $lotteryDate[$v['staff_id']] = $v;
                $lotteryDate[$v['staff_id']]['bet_user'] = isset($bet_user[$v['staff_id']]) ? $bet_user[$v['staff_id']] : 0;
            }

            foreach($mysqlreport->query($externalsql,$param) as $v) {
                $externalDate[$v['staff_id']][$v['interface_key']] = $v;
                $externalDate[$v['staff_id']][$v['interface_key']]['bet_user'] = isset($exbet_user[$v['staff_id']]) ? $exbet_user[$v['staff_id']] : 0;
            }

            // 默认数据数组
            $array = [
                'rebate_amount' => '0',
                'bet_amount' => '0',
                'bet_count' => '0',
                'bet_user' => '0',
                'bonus_amount' => '0',
                'brokerage_amount' => '0',
                'coupon_amount' => '0',
                'profit_amount' => '0',
                'subsidy_amount' => '0',
                'wager_amount' => '0'
            ];

            $z = 0;
            foreach($staffArray as $v) {
                // 全站
                $total[$z] = $array;
                $user_all = isset($userAll[$v['staff_id']]) ? $userAll[$v['staff_id']] :0;
                $total[$z]['staff_name'] = $v['staff_name'];
                if(isset($totalDate[$v['staff_id']])) {
                    $total[$z] = array_merge($total[$z], $totalDate[$v['staff_id']]);
                } 
                $total[$z]['rebate_amount'] = $this->intercept_num($total[$z]['rebate_amount']);
                $total[$z]['profit_amount'] = $this->intercept_num($total[$z]['wager_amount']-$total[$z]['bonus_amount']-$total[$z]['rebate_amount']);
                $total[$z]['bet_amount'] = $this->intercept_num($total[$z]['bet_amount']);
                $total[$z]['wager_amount'] = $this->intercept_num($total[$z]['wager_amount']);
                $total[$z]['bonus_amount'] = $this->intercept_num($total[$z]['bonus_amount']);
                $total[$z]['coupon_amount'] = $this->intercept_num($total[$z]['coupon_amount']);
                $total[$z]['subsidy_amount'] = $this->intercept_num($total[$z]['subsidy_amount']);
                $total[$z]['brokerage_amount'] = $this->intercept_num($total[$z]['brokerage_amount']);
                $total[$z]['user_all'] = $user_all;
                unset($total[$z]['staff_id']);

                // 彩票
                $dataLottery[$z] = $array;
                $dataLottery[$z]['staff_name'] = $v['staff_name'];
                if(isset($lotteryDate[$v['staff_id']])) {
                    $dataLottery[$z] = array_merge($dataLottery[$z], $lotteryDate[$v['staff_id']]);
                }
                $dataLottery[$z]['user_all'] = $user_all;
                $dataLottery[$z]['bet_amount'] = $this->intercept_num($dataLottery[$z]['bet_amount']);
                $dataLottery[$z]['wager_amount'] = $this->intercept_num($dataLottery[$z]['wager_amount']);
                $dataLottery[$z]['bonus_amount'] = $this->intercept_num($dataLottery[$z]['bonus_amount']);
                $dataLottery[$z]['coupon_amount'] = $this->intercept_num($dataLottery[$z]['coupon_amount']);
                $dataLottery[$z]['subsidy_amount'] = $this->intercept_num($dataLottery[$z]['subsidy_amount']);
                $dataLottery[$z]['brokerage_amount'] = '';
                $dataLottery[$z]['profit_amount'] = $this->intercept_num($dataLottery[$z]['profit_amount']);
                unset($dataLottery[$z]['staff_id']);

                // KY
                $externaldata['ky'][$z] = $array;
                $externaldata['ky'][$z]['staff_name'] = $v['staff_name'];
                if(!empty($externalDate[$v['staff_id']]['ky'])) {
                    $externaldata['ky'][$z] = array_merge($externaldata['ky'][$z], $externalDate[$v['staff_id']]['ky']);
                } 
                $externaldata['ky'][$z]['user_all'] = $user_all;
                $externaldata['ky'][$z]['bet_amount'] = $this->intercept_num($externaldata['ky'][$z]['bet_amount']);
                $externaldata['ky'][$z]['wager_amount'] = $this->intercept_num($externaldata['ky'][$z]['wager_amount']);
                $externaldata['ky'][$z]['bonus_amount'] = $this->intercept_num($externaldata['ky'][$z]['bonus_amount']);
                $externaldata['ky'][$z]['coupon_amount'] = $this->intercept_num($externaldata['ky'][$z]['coupon_amount']);
                $externaldata['ky'][$z]['subsidy_amount'] = $this->intercept_num($externaldata['ky'][$z]['subsidy_amount']);
                $externaldata['ky'][$z]['brokerage_amount'] = '';
                $externaldata['ky'][$z]['profit_amount'] = $this->intercept_num($externaldata['ky'][$z]['profit_amount']);
                unset($externaldata['ky'][$z]['staff_id']);

                // FG
                $externaldata['fg'][$z] = $array;
                $externaldata['fg'][$z]['staff_name'] = $v['staff_name'];
                if(!empty($externalDate[$v['staff_id']]['fg'])) {
                    $externaldata['fg'][$z] = array_merge($externaldata['fg'][$z], $externalDate[$v['staff_id']]['fg']);
                } 
                $externaldata['fg'][$z]['user_all'] =$user_all;
                $externaldata['fg'][$z]['bet_amount'] = $this->intercept_num($externaldata['fg'][$z]['bet_amount']);
                $externaldata['fg'][$z]['wager_amount'] = $this->intercept_num($externaldata['fg'][$z]['wager_amount']);
                $externaldata['fg'][$z]['bonus_amount'] = $this->intercept_num($externaldata['fg'][$z]['bonus_amount']);
                $externaldata['fg'][$z]['coupon_amount'] = $this->intercept_num($externaldata['fg'][$z]['coupon_amount']);
                $externaldata['fg'][$z]['subsidy_amount'] = $this->intercept_num($externaldata['fg'][$z]['subsidy_amount']);
                $externaldata['fg'][$z]['brokerage_amount'] = '';
                $externaldata['fg'][$z]['profit_amount'] = $this->intercept_num($externaldata['fg'][$z]['profit_amount']);
                unset($externaldata['fg'][$z]['staff_id']);

                // AG
                $externaldata['ag'][$z] = $array;
                $externaldata['ag'][$z]['staff_name'] = $v['staff_name'];
                if(!empty($externalDate[$v['staff_id']]['ag'])) {
                    $externaldata['ag'][$z] = array_merge($externaldata['ag'][$z], $externalDate[$v['staff_id']]['ag']);
                } 
                $externaldata['ag'][$z]['user_all'] = $user_all;
                $externaldata['ag'][$z]['bet_amount'] = $this->intercept_num($externaldata['ag'][$z]['bet_amount']);
                $externaldata['ag'][$z]['wager_amount'] = $this->intercept_num($externaldata['ag'][$z]['wager_amount']);
                $externaldata['ag'][$z]['bonus_amount'] = $this->intercept_num($externaldata['ag'][$z]['bonus_amount']);
                $externaldata['ag'][$z]['coupon_amount'] = $this->intercept_num($externaldata['ag'][$z]['coupon_amount']);
                $externaldata['ag'][$z]['subsidy_amount'] = $this->intercept_num($externaldata['ag'][$z]['subsidy_amount']);
                $externaldata['ag'][$z]['brokerage_amount'] = '';
                $externaldata['ag'][$z]['profit_amount'] = $this->intercept_num($externaldata['ag'][$z]['profit_amount']);
                unset($externaldata['ag'][$z]['staff_id']);

                // LB
                $externaldata['lb'][$z] = $array;
                $externaldata['lb'][$z]['staff_name'] = $v['staff_name'];
                if(!empty($externalDate[$v['staff_id']]['lb'])) {
                    $externaldata['lb'][$z] = array_merge($externaldata['lb'][$z], $externalDate[$v['staff_id']]['lb']);
                } 
                $externaldata['lb'][$z]['user_all'] = $user_all;
                $externaldata['lb'][$z]['bet_amount'] = $this->intercept_num($externaldata['lb'][$z]['bet_amount']);
                $externaldata['lb'][$z]['wager_amount'] = $this->intercept_num($externaldata['lb'][$z]['wager_amount']);
                $externaldata['lb'][$z]['bonus_amount'] = $this->intercept_num($externaldata['lb'][$z]['bonus_amount']);
                $externaldata['lb'][$z]['coupon_amount'] = $this->intercept_num($externaldata['lb'][$z]['coupon_amount']);
                $externaldata['lb'][$z]['subsidy_amount'] = $this->intercept_num($externaldata['lb'][$z]['subsidy_amount']);
                $externaldata['lb'][$z]['brokerage_amount'] = '';
                $externaldata['lb'][$z]['profit_amount'] = $this->intercept_num($externaldata['lb'][$z]['profit_amount']);
                unset($externaldata['lb'][$z]['staff_id']);

                $z++;
            }
        }

        $context->reply(['status' => 200, 'msg' => '获取数据成功', 'list' => [
            [
                'list_data' => $total,
                'list_key' => 'total',
                'list_name' => '全站',
            ],
            [
                'list_data' => $dataLottery,
                'list_key' => 'lottery',
                'list_name' => '共赢彩票',
            ],
            [
                'list_data' => $externaldata['fg'],
                'list_key' => 'fg',
                'list_name' => 'FG电子',
            ],
            [
                'list_data' => $externaldata['ag'],
                'list_key' => 'ag',
                'list_name' => 'AG视讯',
            ],
            [
                'list_data' => $externaldata['ky'],
                'list_key' => 'ky',
                'list_name' => '开元棋牌',
            ],
            [
                'list_data' => $externaldata['lb'],
                'list_key' => 'lb',
                'list_name' => 'lebo体育',
            ]
        ]]);
        return;
    }

    /**
     * 判断参数是否存在不存在返回相关提示
     * @param $param 参数
     * @return string
     */
    private function slt($param) {
        if(isset($param))
            return $param;
        return '报表数据延时';
    }

}