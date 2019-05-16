<?php

namespace Site\Websocket\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * DepositsPool class.
 *
 * @description   站点首页
 * @Author  blake
 * @date  2019-05-08
 * @links  Index/DepositsPool  {"time":"yesterday","setup_name":"","start_time":"2019-03-03 07:53:47","end_time":"2019-03-03 07:53:47"}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class DepositsPool extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');

        if ($MasterId != 0) {
            $staff_mysql = $config->data_staff;
            $sql = 'select master_id from staff_info where staff_id= :staffId  ';
            $betTranslation = iterator_to_array($staff_mysql->query($sql, [':staffId' => $staffId]));
            $staffId = $betTranslation[0]['master_id'];
        }

        $mysqlReport = $config->data_report;
        $mysqlUser = $config->data_user;
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('home_report', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();

        $time = !empty($data['time']) ? $data['time'] : '';
        $time_start = !empty($data['start_time']) ? $data['start_time'] : '';
        $time_end = !empty($data['end_time']) ? $data['end_time'] : '';

        $setup_name = !empty($data['setup_name']) ? $data['setup_name'] : '';
        if (empty($time_start) && empty($time_end) && empty($time) && empty($setup_name)) {
            $context->reply(['status' => 205, 'msg' => '参数不可为空']);

            return;
        }
        if (!in_array($time, ['today', 'yesterday', 'week', 'month', 'lastWeek', 'lastMonth', ''])) {
            $context->reply(['status' => 203, 'msg' => '时间筛选参数错误']);

            return;
        }

        if ((!empty($time_start) && !empty($time_end)) && $time_start > $time_end) {
            $context->reply(['status' => 204, 'msg' => '结束时间不可小于开始时间']);

            return;
        }
        if (!empty($time_start) || !empty($time_end)) {
            $time = ''; //筛选为优先级
            if ((!empty($time_start) && empty($time_end))) {
                $time_end = $time_start;
            } elseif (empty($time_start) && !empty($time_end)) {
                $time_start = $time_end;
            }//支持单选，即为单日搜索

            $choise_day_start = date('Ymd', strtotime($time_start));
            $choise_day_end = date('Ymd', strtotime($time_end));
            $choise_time_start = strtotime(date('Y-m-d', strtotime($time_start)).' 00:00:00'); //选择的时间起始时间
            $choise_time_end = strtotime(date('Y-m-d', strtotime($time_end)).' 23:59:59'); //选择的时间结束时间
        }
        if (empty($time_start) && empty($time_end) && empty($time) && !empty($setup_name)) {
            $time = 'today';
        }
        $rechargeCount = 0;
        $allmoney = 0;
        $alipay = 0;
        $firstNum = 0;
        $weixin = 0;
        $onlineBank = 0;
        $bank = 0;
        $manual = 0;
        $rechargeUserNum = 0;
        $rechargeNum = 0;
        $manualRechargeUserNum = 0;
        $manualRechargeNum = 0;
        $alipayUser = 0;
        $weixinUser = 0;
        $onlineBankUser = 0;
        $bankUser = 0;
        $manualUser = 0;
        $allWithdraw = 0;
        $manualWithdraw = 0;
        $activityWithdraw = 0;
        $activityWithdrawNum = 0;
        $withdrawUserNumAll = 0;
        $rebate = 0;
        $rebateNum = 0;
        $rebatesub = 0;
        $register = 0;
        $rebatesubNum = 0;
        $broker = 0;
        $brokerNum = 0;
        $withdrawNum = 0;
        $withdrawUserNum = 0;
        $manualWithdrawUserNum = 0;
        $manualWithdrawNum = 0;
        $betAmount = 0;
        $betNum = 0;
        $firstMoney = 0;
        $betCount = 0;
        $depositLineBank = [];
        $depositWeixin = [];
        $depositAlipay = [];
        $depositBank = [];
        $convenientRecharge = [];
        $convenientPaymentCount = 0; //便捷支付金额
        $convenientPaymentNum = 0; //便捷支付人数
        $bonusNum = 0;
        $bonusCount = 0;
        $bonusFrequency = 0;
        $bank_deposit_count = 0;
        $bank_deposit_amount = 0;

        //外接口投注及派奖数据
        $fg_betcount = 0;
        $fg_betamount = 0;
        $fg_betnum = 0;
        $fg_bonuscount = 0;
        $fg_bonusamount = 0;
        $fg_bonusnum = 0; //fg-FunGaming

        $ky_betcount = 0;
        $ky_betamount = 0;
        $ky_betnum = 0;
        $ky_bonuscount = 0;
        $ky_bonusamount = 0;
        $ky_bonusnum = 0; //ky-开元棋牌

        $lb_betcount = 0;
        $lb_betamount = 0;
        $lb_betnum = 0;
        $lb_bonuscount = 0;
        $lb_bonusamount = 0;
        $lb_bonusnum = 0; //lb-Lebo体育

        $ag_betcount = 0;
        $ag_betamount = 0;
        $ag_betnum = 0;
        $ag_bonuscount = 0;
        $ag_bonusamount = 0;
        $ag_bonusnum = 0; //ag-AsiaGaming

        $lottery_game_betcount = 0;
        $lottery_game_betamount = 0;
        $lottery_game_betnum = 0;
        $lottery_game_bonuscount = 0;
        $lottery_game_bonusamount = 0;
        $lottery_game_bonusnum = 0; //彩票游戏（本地非外接口）

        $staffWithdrawalAmountAll = 0; //人工出款总额
        $staffWithdrawalCountAll = 0; //人工出款总人数

        $staffWithdrawalAmount = 0; //手工提出
        $staffWithdrawalCount = 0;
        $cancelDepositAmount = 0; //取消存款
        $cancelDepositCount = 0;
        $illegalBetAmount = 0; //非法投注
        $illegalBetCount = 0;
        $forgoOfferAmount = 0;
        $forgoOfferCount = 0; //放弃存款优惠
        $otherWithdrawalAmount = 0; //其他出款
        $otherWithdrawalCount = 0;

        $refuseWithdrawalAmount = 0; //拒绝出款金额
        $refuseWithdrawalCount = 0; //拒绝出款人数

        $distributedBrokerage = 0;
        $distributedBrokerageNum = 0;
        $waitBrokerageNum = 0;
        $waitBrokerage = 0;

        $distributedSubsidy = 0;
        $distributedSubsidyNum = 0;
        $waitSubsidyNum = 0;
        $waitSubsidy = 0;

        $staffDepositAmount = 0;
        $staffDepositNum = 0;
        $staffDepositType1Amount = 0;
        $staffDepositType1Num = 0;
        $staffDepositType2Amount = 0;
        $staffDepositType2Num = 0;
        $staffDepositType3Amount = 0;
        $staffDepositType3Num = 0;

        $betcountTranslation = 0;
        $betamountTranslation = 0;
        $betnumTranslation = 0;
        $bonuscountTranslation = 0;
        $bonusnumTranslation = 0;
        $bonusamountTranslation = 0;
        $success_withdraw_count = 0;

        $linebankTranslation = [];
        $weixinTranslation = [];
        $alipayTranslation = [];
        $bankTranslation = [];
        $simpleTranslation = [];

        $now_time = time();
        $day_today = date('Ymd', time()); //今日日期
        $today_day_start = strtotime(date('Y-m-d', time()).' 00:00:00'); //日起始时间
        $today_day_end = strtotime(date('Y-m-d', time()).' 23:59:59'); //日结束时间
        $yesterday_time = intval(date('Ymd', strtotime('-1 day'))); //昨日日期
        $yesterday_start = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨日起始时间
        $yesterday_end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1; //昨日结束时间
        $week_start = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('y')); //周起始时间
        $week_start_day = date('Ymd', time() - 86400 * date('w')) + 1; //本周开始第一天
        $month_start = mktime(0, 0, 0, date('m'), 1, date('Y')); //月起始时间
        $month_start_day = date('Ymd', strtotime(date('Y-m', time()).'-01 00:00:00')); //本月开始第一天

        $lastMonthFirstday = date('Ym01', strtotime('last month')); //上月第一天
        $lastMonthLastday = date('Ymd', strtotime(date('Y-m-1').'-1 day')); //上月最后一天
        $lastMonthBegin = mktime(0, 0, 0, date('m') - 1, 1, date('Y')); //上月开始时间戳
        $lastMonthEnd = mktime(23, 59, 59, date('m'), 0, date('Y')); //上月结束时间戳

        $beginLastweek = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y')); //上周开始时间戳
        $endLastweek = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y')); //上周结束时间戳
        $lastweek_start = date('Ymd ', mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'))); //上周第一天
        $lastweek_end = date('Ymd ', mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'))); //上周最后一天
        $day_time_start = '';
        $day_time_end = '';
        $time_point_start = '';
        $time_point_end = '';

        $agentMysql = $config->data_staff;
        $user_id = '';
        $userList_sql = '';
        $user_list = [];
        $param = [];
        if (!empty($data['setup_name'])) { //体系线下的账号进行搜索
            $setupName = $data['setup_name'];
            $sql = 'select staff_id,staff_name,staff_grade,leader_id from staff_info_intact where staff_key= :setupName ';
            $betTranslation = iterator_to_array($agentMysql->query($sql, [':setupName' => $setupName]));
            if (empty($betTranslation)) {
                $context->reply(['status' => 203, 'msg' => '未有该体系线，请检查输入是否正确']);

                return;
            }

            if ($StaffGrade == 1) {//大股东
                if ($betTranslation[0]['staff_grade'] == 2 && $betTranslation[0]['leader_id'] != $staffId) {
                    $context->reply(['status' => 204, 'msg' => '查询有误，不属于当前体系线']);

                    return;
                }
                if ($betTranslation[0]['staff_grade'] == 3) {
                    $leaderId = $betTranslation[0]['leader_id'];
                    $sql = 'select leader_id from staff_info where staff_id= :leaderId  ';
                    foreach ($staff_mysql->query($sql, [':leaderId' => $leaderId]) as $value) {
                        if ($value['leader_id'] != $staffId) {
                            $context->reply(['status' => 204, 'msg' => '查询有误，不属于当前体系线']);

                            return;
                        }
                    }
                }
            }

            if ($StaffGrade == 2) {//股东
                if ($betTranslation[0]['staff_grade'] == 3 && $betTranslation[0]['leader_id'] != $staffId) {
                    $context->reply(['status' => 204, 'msg' => '查询有误，不属于当前体系线']);

                    return;
                }
            }

            if ($StaffGrade == 3) {//总代理
                $context->reply(['status' => 204, 'msg' => '查询有误，仅能查看当前体系线']);

                return;
            }

            $staffId = $betTranslation[0]['staff_id'];
            switch ($betTranslation[0]['staff_grade']) {
                case 0:
                    $userList_sql = 'select  user_id from user_info_intact ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where 1=1 ';
                    break;
                case 1:
                    $userList_sql = 'select user_id from user_event where major_id= :staffId ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where major_id= :staffId ';
                    $param[':staffId'] = $staffId;
                    break;
                case 2:
                    $userList_sql = 'select user_id from user_event where minor_id= :staffId ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where minor_id= :staffId  ';
                    $param[':staffId'] = $staffId;
                    break;
                case 3:
                    $userList_sql = 'select  user_id from user_event where agent_id= :staffId ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where agent_id= :staffId ';
                    $param[':staffId'] = $staffId;
                    break;
            }
        } else {
            switch ($StaffGrade) {
                case 0:
                    $userList_sql = 'select  user_id from user_info_intact ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where 1=1 ';
                    break;
                case 1:
                    $userList_sql = 'select  user_id from user_event where major_id= :staffId ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where  major_id= :staffId ';
                    $param[':staffId'] = $staffId;
                    break;
                case 2:
                    $userList_sql = 'select  user_id from user_event where  minor_id= :staffId ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where  minor_id= :staffId';
                    $param[':staffId'] = $staffId;
                    break;
                case 3:
                    $userList_sql = 'select  user_id from user_event where  agent_id= :staffId ';
                    $bet_user_sql = 'select sum(wager_user) as bet_user from daily_staff where  agent_id= :staffId ';
                    $param[':staffId'] = $staffId;
                    break;
            }
        }

        if ($StaffGrade > 0 || !empty($betTranslation[0]['staff_grade'])) {
            foreach ($mysqlReport->query($userList_sql, [':staffId' => $staffId]) as $item) {
                $user_list[] = $item['user_id'];
            }
        } else {
            if (!empty($userList_sql)) {
                foreach ($mysqlUser->query($userList_sql, [':staffId' => $staffId]) as $item) {
                    $user_list[] = $item['user_id'];
                }
            }
        }
        if (empty($user_list)) {
            $user_list = [0];
        }
        $recharge_user_sql = 'select count(distinct user_id) as allUsers ,sum(if(deposit_bank_count>0,deposit_bank_count,0))as'
                .' rechargebankUserNo,sum(if(deposit_weixin_count>0,deposit_weixin_count,0))as'
                .' rechargeweixinUserNo,sum(if(deposit_alipay_count>0,deposit_alipay_count,0))as'
                .' rechargealipayUserNo,sum(if(bank_deposit_count>0,bank_deposit_count,0))as'
                .' rechargelinebankUserNo,sum(if(simple_deposit_count>0,simple_deposit_count,0))as'
                .' rechargesimpleUserNo from daily_user WHERE 1=1  and user_id in :user_list  AND (deposit_bank_count>0 or deposit_weixin_count>0'
                .' or deposit_alipay_count>0 or simple_deposit_count>0 or bank_deposit_count>0) and  ';
        $all_data_sql = 'select sum(deposit_count) as  deposit_count_number, sum(withdraw_count) as  withdraw_count_number,'.
                'sum(bank_deposit_coupon)as bank_deposit_coupon_amount,'.
                'count(is_first_deposit=1 or null ) as firstRechargeNum,sum(if(is_first_deposit>0,deposit_amount,0)) as firstRechargeAmount,'.
                'sum(simple_deposit_amount) as simpleDepositAmount ,count(DISTINCT (if(simple_deposit_amount>0,user_id,null))) as simpleDepositNum,'.
                'sum(bonus_count) as bonusFrequency ,count(bonus_amount>0 or null) as bonusNum,sum(bonus_amount) as bonusCount,'
                .'count(DISTINCT (if(staff_withdraw_count>0,user_id,null))) as staff_withdraw_userNO,count(DISTINCT (if(staff_deposit_count>0,user_id,null))) as staff_deposit_userNO ,'.
                'count(DISTINCT (if(bank_deposit_count>0,user_id,null)) ) as bank_deposit_count, sum(bank_deposit_amount-bank_deposit_coupon)as bank_deposit_amount,'.
                'count(DISTINCT (if(deposit_bank_count>0,user_id,null))) as deposit_bank_count,sum(deposit_bank_amount) as deposit_bank_amount,'.
                'count(DISTINCT (if(deposit_weixin_count>0,user_id,null)) ) as deposit_weixin_count ,sum(deposit_weixin_amount)as deposit_weixin_amount,'.
                'count(DISTINCT (if(deposit_alipay_count>0,user_id,null)) ) as deposit_alipay_count,sum(deposit_alipay_amount) as deposit_alipay_amount,'.
                'count(staff_deposit_count>0 or null )  as staff_deposit_count,sum(staff_deposit_amount)  as staff_deposit_amount,'.
                'count(DISTINCT (if( withdraw_count-staff_withdraw_count>0,user_id,null))) as  withdraw_count, sum(withdraw_count-staff_withdraw_count)as success_withdraw_count, sum(withdraw_amount-staff_withdraw_amount)as withdraw_amount,count(bank_deposit_coupon>0 or null)as coupon_count,'.
                'sum(coupon_amount)as coupon_amount,count(staff_withdraw_count) as staff_withdraw_count,sum(staff_withdraw_amount) as staff_withdraw_amount,'.
                'count(rebate_amount>0 or null) as rebate_amountTime ,sum(rebate_amount)as rebate_amount, count(subsidy_amount>0 or null) as subsidy_count ,'.
                'sum(subsidy_amount)  as subsidy_amount,count(staff_deposit_count>0 or null)as staff_deposit_countTime,'.
                'sum(staff_deposit_count)  as staff_deposit_countSum, count(staff_withdraw_count>0 or null)as staff_withdraw_countTime,'.
                'sum(staff_withdraw_count) as  staff_withdraw_countSum,  count(wager_count>0 or null) as wager_countTime,sum(wager_count)as wager_count,'.
                'sum(wager_amount)as wager_amount, count(is_first_deposit>0 or null) as is_first_deposit from daily_user where user_id in :user_list AND ';

        $register_data_sql = 'SELECT count(distinct user_id) as user_id FROM user_info_intact WHERE user_id in :user_list  AND ';
        $deposit_linebank_detail = 'SELECT way_name,gate_name, sum(launch_money) as money '.
                'FROM deposit_gateway_intact WHERE user_id in :user_list  AND ';
        $deposit_weixin_detail = 'SELECT way_name,gate_name, sum(launch_money) as money '.
                'FROM deposit_gateway_intact WHERE user_id in :user_list  AND ';
        $deposit_alipay_detail = 'SELECT way_name,gate_name, sum(launch_money) as money '.
                'FROM deposit_gateway_intact WHERE user_id in :user_list  AND ';
        $deposit_bank_detail = 'SELECT passage_name,to_bank_name, to_account_number, sum(launch_money) as money'.
                ' FROM deposit_bank_intact WHERE user_id in :user_list  AND ';
        $deposit_simple_detail = 'SELECT passage_name,pay_url, sum(launch_money) as money '.
                'FROM deposit_simple_intact WHERE user_id in :user_list  AND ';
        $lottery_bet_sql = 'select  count(distinct user_id ) as total ,sum(wager_count)as gameBetCount,'.
                'sum(wager_amount)as game_BetAmount from daily_user_lottery where user_id in :user_list  AND '; //TODO 未想到合并这两个sql更好的方法，待优化
        $lottery_bonus_sql = 'select  count(distinct user_id ) as total ,sum(bonus_amount)as gameBonusAmount,'.
                'sum(bonus_count) as gameBonusCount from daily_user_lottery where  user_id in :user_list  AND ';
        $external_bet_sql = 'select interface_key,sum(wager_count)as gameBetCount,count(distinct user_id) as gameBetNum,'.
                'sum(wager_amount)as game_BetAmount  from daily_user_external where  user_id in :user_list  AND wager_amount>0 and ';
        $external_bonus_sql = 'select interface_key,sum(bonus_amount)as gameBonusAmount,sum(bonus_count) as gameBonusCount,'
                .'count(distinct user_id)as gameBonusNum from daily_user_external where user_id in :user_list  AND bonus_amount>0 and ';
        $brokerage_sql = 'select count(distinct(if(deliver_time=0  and brokerage>0,user_id,null)) ) as waitBrokerageNum,count(distinct(if(deliver_time>0 and brokerage>0,user_id,null))) as distributedBrokerageNum,'.
                'sum(if(deliver_time>0,brokerage,0)) as distributedBrokerage,sum(if(deliver_time=0,brokerage,0)) as waitBrokerage from daily_user_brokerage WHERE user_id in :user_list  AND brokerage>0 and ';
        $staff_withdraw_intact_sql = 'select withdraw_type,sum(money) as  withdraw_money,count(distinct user_id) as user_ids  from  staff_withdraw_intact  where  user_id in :user_list  AND ';
        $withdraw_intact_sql = 'select sum(launch_money) as withdraw_money,count(distinct user_id) as user_ids from withdraw_intact where user_id in :user_list  AND ';
        $staff_deposit_sql = ' select deposit_type,count(distinct user_id) as users ,sum(money) as staff_deposit_money from staff_deposit_intact where user_id in :user_list  and  ';
        $Distributeed_brokerage_sql = ' select count(distinct (if(brokerage>0,user_id,null))) as user_id,sum(brokerage) as brokerage  from daily_user_brokerage where user_id in :user_list  and  ';
        $Distributeed_subsidy_sql = ' select count(distinct (if(subsidy>0,user_id,null))) as user_id,sum(subsidy) as subsidy  from daily_user_subsidy where user_id in :user_list  and  ';
        $subsidy_sql = 'select count(DISTINCT(if(deliver_time=0 and subsidy>0 ,user_id,null))) as waitSubsidyNum,count(distinct(if(deliver_time>0  and subsidy>0,user_id,null))) as distributedSubsidyNum,'.
                'sum(if(deliver_time>0,subsidy,0)) as distributedSubsidy,sum(if(deliver_time=0,subsidy,0)) as waitSubsidy from daily_user_subsidy WHERE user_id in :user_list  AND subsidy>0 and ';
        if ($time == 'lastWeek') {
            $day_time_start = $lastweek_start;
            $day_time_end = $lastweek_end;
            $time_point_start = $beginLastweek;
            $time_point_end = $endLastweek;
        }

        if ($time == 'lastMonth') {
            $day_time_start = $lastMonthFirstday;
            $day_time_end = $lastMonthLastday;
            $time_point_start = $lastMonthBegin;
            $time_point_end = $lastMonthEnd;
        }

        if (!empty($time_start)) {
            $day_time_start = $choise_day_start;
            $day_time_end = $choise_day_end;
            $time_point_start = $choise_time_start;
            $time_point_end = $choise_time_end;
        }

        if ($time == 'today') {
            $day_time_start = $day_today;
            $day_time_end = $day_today;
            $time_point_start = $today_day_start;
            $time_point_end = $today_day_end;
        }

        if ($time == 'yesterday') {
            $day_time_start = $yesterday_time;
            $day_time_end = $yesterday_time;
            $time_point_start = $yesterday_start;
            $time_point_end = $yesterday_end;
        }

        if ($time == 'week') {
            $day_time_start = $week_start_day;
            $day_time_end = $day_today;
            $time_point_start = $week_start;
            $time_point_end = $now_time;
        }

        if ($time == 'month') {
            $day_time_start = $month_start_day;
            $day_time_end = $day_today;
            $time_point_start = $month_start;
            $time_point_end = $now_time;
        }
        $day_start_translation=$day_time_start ;
        $day_end_translation=$day_time_end ;
        if($time == 'today'||$time == 'yesterday'||!empty($time_start)){        
            $day_start_translation= date("Ymd",(strtotime($day_start_translation) - 3600*24));
            $day_end_translation= date("Ymd",(strtotime($day_end_translation) - 3600*24));
        }
        $recharge_user_sql .= ' daily  between :day_time_start and :day_time_end ';
        $all_data_sql .= ' daily  between :day_time_start and :day_time_end ';
        $register_data_sql .= ' register_time between :time_point_start and :time_point_end ';
        $deposit_linebank_detail .= '  finish_time BETWEEN  :time_point_start and :time_point_end '.
                " AND way_key='bank' group by gate_name,way_name ";
        $deposit_weixin_detail .= '  finish_time  BETWEEN  :time_point_start and :time_point_end '.
                " AND way_key='weixin' group by gate_name,way_name ";
        $deposit_alipay_detail .= '  finish_time  BETWEEN  :time_point_start and :time_point_end '.
                " AND way_key='alipay' group by gate_name,way_name ";
        $deposit_bank_detail .= '  finish_time   BETWEEN   :time_point_start and :time_point_end  group by passage_name,to_bank_name,to_account_number';
        $deposit_simple_detail .= '  finish_time BETWEEN   :time_point_start and :time_point_end  group by passage_name,pay_url ';
        $lottery_bet_sql .= '  daily  between  :day_time_start and :day_time_end  and wager_amount>0  ';
        $lottery_bonus_sql .= '  daily  between  :day_time_start and :day_time_end  and  bonus_amount>0   ';
        $external_bet_sql .= ' daily  between  :day_time_start and :day_time_end  group by interface_key ';
        $external_bonus_sql .= ' daily  between  :day_time_start and :day_time_end  group by interface_key ';
        $brokerage_sql .= ' daily  between  :day_time_start and :day_time_end  and daily < :day_today ';
        $staff_withdraw_intact_sql .= ' withdraw_time  BETWEEN :time_point_start and :time_point_end  group by withdraw_type ';
        $withdraw_intact_sql .= ' reject_time  BETWEEN :time_point_start and :time_point_end ';
        $staff_deposit_sql .= ' deposit_time BETWEEN  :time_point_start and :time_point_end  group by deposit_type';
        $Distributeed_brokerage_sql .= ' deliver_time  BETWEEN  :time_point_start and :time_point_end ';
        $Distributeed_subsidy_sql .= ' deliver_time  BETWEEN  :time_point_start and :time_point_end  ';
        $subsidy_sql .= ' daily  between :day_time_start and :day_time_end  and daily < :day_today ';
        $bet_user_sql .= ' and daily  between :day_time_start and :day_time_end ';
        $parame_point = [
            ':user_list' => $user_list,
            ':time_point_start' => $time_point_start,
            ':time_point_end' => $time_point_end,
        ];
        $parame_day = [
            ':user_list' => $user_list,
            ':day_time_start' => $day_time_start,
            ':day_time_end' => $day_time_end,
        ];
        $param[':day_time_start'] = $day_time_start;
        $param[':day_time_end'] = $day_time_end;
        //$context->reply(['status' => 204, 'msg' => $day_time_start.'/'.$day_time_end]);

        //return;
        $bet_user = iterator_to_array($mysqlReport->query($bet_user_sql, $param));
        $distributeed_brokerage_data = iterator_to_array($mysqlReport->query($Distributeed_brokerage_sql, $parame_point));
        $recharge_user = iterator_to_array($mysqlReport->query($recharge_user_sql, $parame_day));
        $rechargeUserNum += !empty($recharge_user[0]['allUsers']) ? $recharge_user[0]['allUsers'] : 0;
        $rechargeUserNumCount = 0 + $recharge_user[0]['rechargebankUserNo'] + $recharge_user[0]['rechargeweixinUserNo'] + $recharge_user[0]['rechargealipayUserNo'] + $recharge_user[0]['rechargelinebankUserNo'] + $recharge_user[0]['rechargesimpleUserNo'];
        $brokerage_data_list = iterator_to_array($mysqlReport->query($brokerage_sql, [
            ':user_list' => $user_list,
            ':day_time_start' =>  $day_start_translation,
            ':day_time_end' => $day_end_translation,
            ':day_today' => $day_today,
        ]));
        $distributedBrokerage += $distributeed_brokerage_data[0]['brokerage'];
        $distributedBrokerageNum += $distributeed_brokerage_data[0]['user_id'];
        $waitBrokerageNum += $brokerage_data_list[0]['waitBrokerageNum'];
        $waitBrokerage += $brokerage_data_list[0]['waitBrokerage'];

        $broker += $distributedBrokerage + $waitBrokerage;
        $brokerNum += $waitBrokerageNum + $distributedBrokerageNum; //返佣人数

        $distributeed_subsidy_data = iterator_to_array($mysqlReport->query($Distributeed_subsidy_sql, $parame_point));
        $subsidy_data_list = iterator_to_array($mysqlReport->query($subsidy_sql, [
            ':user_list' => $user_list,
            ':day_time_start' =>  $day_start_translation,
            ':day_time_end' => $day_end_translation,
            ':day_today' => $day_today,
        ]));
        $distributedSubsidy += $distributeed_subsidy_data[0]['subsidy'];
        $distributedSubsidyNum += $distributeed_subsidy_data[0]['user_id'];
        $waitSubsidyNum += $subsidy_data_list[0]['waitSubsidyNum'];
        $waitSubsidy += $subsidy_data_list[0]['waitSubsidy'];
        $rebatesub += $distributedSubsidy + $waitSubsidy;
        $rebatesubNum += $distributedSubsidyNum + $waitSubsidyNum;
        $data_list = iterator_to_array($mysqlReport->query($all_data_sql, $parame_day));

        $staffDepositNum += $data_list[0]['staff_deposit_userNO'];
        $staffWithdrawalCountAll += $data_list[0]['staff_withdraw_userNO']; //人工出款总人数
        $rechargeCount += $data_list[0]['bank_deposit_count'] + $data_list[0]['deposit_bank_count'] + $data_list[0]['deposit_weixin_count'] + $data_list[0]['deposit_alipay_count'] + $data_list[0]['simpleDepositNum']; //计算待思考
        $allmoney += $data_list[0]['bank_deposit_amount'] + $data_list[0]['deposit_bank_amount'] + $data_list[0]['deposit_weixin_amount'] + $data_list[0]['deposit_alipay_amount'] + $data_list[0]['simpleDepositAmount'];
        //内外接口的投注及派奖数据

        $data_bet_lottery = iterator_to_array($mysqlReport->query($lottery_bet_sql, $parame_day));
        $data_bonus_lottery = iterator_to_array($mysqlReport->query($lottery_bonus_sql, $parame_day));

        $lottery_game_betcount += $data_bet_lottery[0]['gameBetCount'];
        $lottery_game_betamount += $data_bet_lottery[0]['game_BetAmount'];
        $lottery_game_betnum += $data_bet_lottery[0]['total'];

        $lottery_game_bonuscount += $data_bonus_lottery[0]['gameBonusCount'];
        $lottery_game_bonusamount += $data_bonus_lottery[0]['gameBonusAmount'];
        $lottery_game_bonusnum += $data_bonus_lottery[0]['total'];

        $data_bet_external = iterator_to_array($mysqlReport->query($external_bet_sql, $parame_day));
        $data_bonus_external = iterator_to_array($mysqlReport->query($external_bonus_sql, $parame_day));
        if (!empty($data_bet_external)) {
            foreach ($data_bet_external as $value) {
                $betcountTranslation += $value['gameBetCount'];
                $betamountTranslation += $value['game_BetAmount'];
                $betnumTranslation += $value['gameBetNum'];
                switch ($value['interface_key']) {
                    case 'fg':
                        $fg_betcount += $value['gameBetCount'];
                        $fg_betamount += $value['game_BetAmount'];
                        $fg_betnum += $value['gameBetNum'];
                        break;
                    case 'ky':
                        $ky_betcount += $value['gameBetCount'];
                        $ky_betamount += $value['game_BetAmount'];
                        $ky_betnum += $value['gameBetNum'];
                        break;
                    case 'lb':
                        $lb_betcount += $value['gameBetCount'];
                        $lb_betamount += $value['game_BetAmount'];
                        $lb_betnum += $value['gameBetNum'];
                        break;
                    case 'ag':
                        $ag_betcount += $value['gameBetCount'];
                        $ag_betamount += $value['game_BetAmount'];
                        $ag_betnum += $value['gameBetNum'];
                        break;
                }
            }
        }
        if (!empty($data_bonus_external)) {
            foreach ($data_bonus_external as $value) {
                $bonuscountTranslation += $value['gameBonusCount'];
                $bonusnumTranslation += $value['gameBonusNum'];
                $bonusamountTranslation += $value['gameBonusAmount'];
                switch ($value['interface_key']) {
                    case 'fg':
                        $fg_bonuscount += $value['gameBonusCount'];
                        $fg_bonusnum += $value['gameBonusNum'];
                        $fg_bonusamount += $value['gameBonusAmount'];
                        break;
                    case 'ky':
                        $ky_bonuscount += $value['gameBonusCount'];
                        $ky_bonusnum += $value['gameBonusNum'];
                        $ky_bonusamount += $value['gameBonusAmount'];
                        break;
                    case 'lb':
                        $lb_bonuscount += $value['gameBonusCount'];
                        $lb_bonusnum += $value['gameBonusNum'];
                        $lb_bonusamount += $value['gameBonusAmount'];
                        break;
                    case 'ag':
                        $ag_bonuscount += $value['gameBonusCount'];
                        $ag_bonusnum += $value['gameBonusNum'];
                        $ag_bonusamount += $value['gameBonusAmount'];
                        break;
                }
            }
        }

        //右侧收益数据固定栏
        $month_income_sql = 'select sum(wager_amount) as monthBetAmount,sum(bonus_amount) as monthBonusAmount,sum(rebate_amount)as monthRebateAmount,sum(subsidy_amount) as monthSubsidyAmount,sum(coupon_amount) as monthCouponAmount from daily_user where  daily >=:month_start_day and user_id in :user_list ';
        $month_brokerage_sql = 'select sum(brokerage) as brokerage  FROM daily_user_brokerage where  deliver_time  BETWEEN :month_start and :nowtime and  daily >=:month_start_day and  user_id in :user_list ';
        $lastMonth_income_sql = 'select sum(wager_amount) as monthBetAmount,sum(bonus_amount) as monthBonusAmount,sum(rebate_amount)as monthRebateAmount,sum(subsidy_amount) as monthSubsidyAmount  , sum(coupon_amount) as monthCouponAmount from daily_user   where  daily BETWEEN :lastMonthFirstday and :lastMonthLastday and  user_id in :user_list ';
        $lastMonth_brokerage_sql = 'select sum(brokerage) as brokerage FROM daily_user_brokerage where    deliver_time  BETWEEN :lastMonthBegin and :lastMonthEnd and daily   BETWEEN :lastMonthFirstday and :lastMonthLastday and  user_id in :user_list ';
        $onlineUsers_sql = 'select count(lose_time=0 or null ) as usersNum from  user_session where 1=1 and  user_id in :user_list ';
        $month_subsidy_sql = 'select sum(subsidy) as subsidy  FROM daily_user_subsidy where  deliver_time  BETWEEN  :month_start and :nowtime and  daily >=:month_start_day and  user_id in :user_list ';
        $lastMonth_subsidy_sql = 'select sum(subsidy) as subsidy  FROM daily_user_subsidy where  deliver_time  BETWEEN :lastMonthBegin and :lastMonthEnd  and daily   BETWEEN  :lastMonthFirstday and :lastMonthLastday  and  user_id in :user_list ';

        $lineUser = iterator_to_array($mysqlUser->query($onlineUsers_sql, [':user_list' => $user_list]));
        $month_income = iterator_to_array($mysqlReport->query($month_income_sql, [':month_start_day' => $month_start_day, ':user_list' => $user_list]));
        $month_brokerage = iterator_to_array($mysqlReport->query($month_brokerage_sql, [':month_start' => $month_start_day, ':nowtime' => $now_time, ':month_start_day' => $month_start_day, ':user_list' => $user_list]));
        $lastMonth_income = iterator_to_array($mysqlReport->query($lastMonth_income_sql, [':lastMonthFirstday' => $lastMonthFirstday, ':lastMonthLastday' => $lastMonthLastday, ':user_list' => $user_list]));
        $lastMonth_brokerage = iterator_to_array($mysqlReport->query($lastMonth_brokerage_sql, [':lastMonthBegin' => $lastMonthBegin, ':lastMonthEnd' => $lastMonthEnd, ':lastMonthFirstday' => $lastMonthFirstday, ':lastMonthLastday' => $lastMonthLastday, ':user_list' => $user_list]));
        $month_subsidy = iterator_to_array($mysqlReport->query($month_subsidy_sql, [':month_start' => $month_start, ':nowtime' => $now_time, ':month_start_day' => $month_start_day, ':user_list' => $user_list]));
        $lastMonth_subsidy = iterator_to_array($mysqlReport->query($lastMonth_subsidy_sql, [':lastMonthBegin' => $lastMonthBegin, ':lastMonthEnd' => $lastMonthEnd, ':lastMonthFirstday' => $lastMonthFirstday, ':lastMonthLastday' => $lastMonthLastday, ':user_list' => $user_list]));

        $monthBetAmount = !empty($month_income[0]['monthBetAmount']) ? $month_income[0]['monthBetAmount'] : 0;
        $monthBonusAmount = !empty($month_income[0]['monthBonusAmount']) ? $month_income[0]['monthBonusAmount'] : 0;
        $monthRebateAmount = !empty($month_income[0]['monthRebateAmount']) ? $month_income[0]['monthRebateAmount'] : 0;
        $monthCouponAmount = !empty($month_income[0]['monthCouponAmount']) ? $month_income[0]['monthCouponAmount'] : 0;
        $monthBrokerage = !empty($month_brokerage[0]['brokerage']) ? $month_brokerage[0]['brokerage'] : 0;
        $monthSubsidyAmount = !empty($month_subsidy[0]['subsidy']) ? $month_subsidy[0]['subsidy'] : 0;

        $lastMonthBetAmount = !empty($lastMonth_income[0]['monthBetAmount']) ? $lastMonth_income[0]['monthBetAmount'] : 0;
        $lastMonthBonusAmount = !empty($lastMonth_income[0]['monthBonusAmount']) ? $lastMonth_income[0]['monthBonusAmount'] : 0;
        $lastMonthRebateAmount = !empty($lastMonth_income[0]['monthRebateAmount']) ? $lastMonth_income[0]['monthRebateAmount'] : 0;
        $lastMonthCouponAmount = !empty($lastMonth_income[0]['monthCouponAmount']) ? $lastMonth_income[0]['monthCouponAmount'] : 0;
        $lastMonthBrokerage = !empty($lastMonth_brokerage[0]['brokerage']) ? $lastMonth_brokerage[0]['brokerage'] : 0;
        $lastMonthSubsidyAmount = !empty($lastMonth_subsidy[0]['subsidy']) ? $lastMonth_subsidy[0]['subsidy'] : 0;

        $monthIncome = $monthBetAmount - $monthBonusAmount - $monthRebateAmount;
        $monthProfit = $monthBetAmount - $monthBonusAmount - $monthRebateAmount - $monthSubsidyAmount - $monthBrokerage;
        $lastMonthIncome = $lastMonthBetAmount - $lastMonthBonusAmount - $lastMonthRebateAmount;
        $lastMonthProfit = $lastMonthBetAmount - $lastMonthBonusAmount - $lastMonthRebateAmount - $lastMonthSubsidyAmount - $lastMonthBrokerage;

        if ($monthBetAmount == 0) {
            $monthRate = 0;
            $monthProfitRate = 0;
        } else {
            $monthRate = round($monthIncome / $monthBetAmount, 4) * 100;
            $monthProfitRate = round($monthProfit / $monthBetAmount, 4) * 100; //前端需百分比显示
        }
        if ($lastMonthBetAmount == 0) {
            $lastMonthRate = 0;
            $lastMonthProfitRate = 0;
        } else {
            $lastMonthRate = round($lastMonthIncome / $lastMonthBetAmount, 4) * 100;
            $lastMonthProfitRate = round($lastMonthProfit / $lastMonthBetAmount, 4) * 100;
        }

        $success_withdraw_count += $data_list[0]['success_withdraw_count'];
        $bank += $data_list[0]['bank_deposit_amount'];
        $bankUser += $data_list[0]['bank_deposit_count']; //银行
        $onlineBank += $data_list[0]['deposit_bank_amount']; //网银
        $onlineBankUser += $data_list[0]['deposit_bank_count'];
        $weixin += $data_list[0]['deposit_weixin_amount']; //微信
        $weixinUser += $data_list[0]['deposit_weixin_count'];
        $alipay += $data_list[0]['deposit_alipay_amount']; //支付宝
        $alipayUser += $data_list[0]['deposit_alipay_count'];
        $manual += $data_list[0]['staff_deposit_amount']; //人工存入
        $manualUser += $data_list[0]['staff_deposit_count'];
        $bank_deposit_count += $data_list[0]['coupon_count']; //入款优惠笔数
        $bank_deposit_amount += $data_list[0]['bank_deposit_coupon_amount'];   //入款优惠金额
        $convenientPaymentCount += $data_list[0]['simpleDepositAmount']; //便捷入款金额
        $convenientPaymentNum += $data_list[0]['simpleDepositNum']; //便捷入款人数
        $withdrawUserNumAll += $data_list[0]['withdraw_count']; //总出款人数　
        $allWithdraw += $data_list[0]['withdraw_amount']; //总出款
        $activityWithdraw += $data_list[0]['coupon_amount']; //活动礼金金额
        $activityWithdrawNum += $data_list[0]['coupon_count']; //活动礼金的人数
        $manualWithdraw += $data_list[0]['staff_withdraw_amount']; //人工提款金额
        $withdrawUserNum += $data_list[0]['staff_withdraw_count']; //人工提款人数
        $rebate += $data_list[0]['rebate_amount'];
        $rebateNum += $data_list[0]['rebate_amountTime']; //返点

        $bonusNum += $bonusnumTranslation + $lottery_game_bonusnum; //派奖人数
        $bonusCount += $bonusamountTranslation + $lottery_game_bonusamount; //派奖金额
        $bonusFrequency += $lottery_game_bonuscount + $bonuscountTranslation; //派奖注数

        $betCount += $betcountTranslation + $lottery_game_betcount; //单数
        $betAmount += $this->intercept_num($lottery_game_betamount + $betamountTranslation); //金额
        $betNum += $bet_user[0]['bet_user']; //投注人数

        $register += iterator_to_array($mysqlUser->query($register_data_sql, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]))[0]['user_id']; //注册人数
        $withdrawNum += $data_list[0]['withdraw_count_number']; //出款单数
        $manualRechargeNum += $data_list[0]['staff_deposit_countSum']; //笔数
        $manualRechargeUserNum += $data_list[0]['staff_deposit_countTime']; //人数
        $rechargeNum += $data_list[0]['deposit_count_number']; //充值总笔数
        $manualWithdrawNum += $data_list[0]['staff_withdraw_count'];
        $manualWithdrawUserNum += $data_list[0]['staff_withdraw_countTime'];

        $firstNum += $data_list[0]['firstRechargeNum'];   //首充人数
        $firstMoney += $data_list[0]['firstRechargeAmount']; //首充金额
        foreach ($config->deal_list as $deal) {
            $mysqlDeal = $config->__get('data_'.$deal);
            $staff_deposit_data = iterator_to_array($mysqlDeal->query($staff_deposit_sql, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]));
            if (!empty($staff_deposit_data)) {
                foreach ($staff_deposit_data as $depositData) {
                    $staffDepositAmount += $depositData['staff_deposit_money'];
                    switch ($depositData['deposit_type']) {
                        case 0:
                            $staffDepositType1Amount += $depositData['staff_deposit_money'];
                            $staffDepositType1Num += $depositData['users'];
                            break;
                        case 1:
                            $staffDepositType2Amount += $depositData['staff_deposit_money'];
                            $staffDepositType2Num += $depositData['users'];
                            break;
                        case 2:
                            $staffDepositType3Amount += $depositData['staff_deposit_money'];
                            $staffDepositType3Num += $depositData['users'];
                            break;
                    }
                }
            }

            $staff_withdraw_data = iterator_to_array($mysqlDeal->query($staff_withdraw_intact_sql, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]));
            $withdraw_intact_data = iterator_to_array($mysqlDeal->query($withdraw_intact_sql, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]));
            if (!empty($staff_withdraw_data)) {
                foreach ($staff_withdraw_data as $staff_withdraw) {
                    $staffWithdrawalAmountAll += $staff_withdraw['withdraw_money']; //人工出款总额
                    switch ($staff_withdraw['withdraw_type']) {
                        case 0:
                            $staffWithdrawalAmount += $staff_withdraw['withdraw_money'];
                            $staffWithdrawalCount = $staff_withdraw['user_ids'];
                            break;
                        case 1:
                            $cancelDepositAmount += $staff_withdraw['withdraw_money'];
                            $cancelDepositCount = $staff_withdraw['user_ids'];
                            break;
                        case 2:
                            $illegalBetAmount += $staff_withdraw['withdraw_money'];
                            $illegalBetCount = $staff_withdraw['user_ids'];
                            break;
                        case 3:
                            $forgoOfferAmount += $staff_withdraw['withdraw_money'];
                            $forgoOfferCount = $staff_withdraw['user_ids'];
                            break;
                        case 4:
                            $otherWithdrawalAmount += $staff_withdraw['withdraw_money'];
                            $otherWithdrawalCount = $staff_withdraw['user_ids'];
                            break;
                    }
                }
            }
            if (!empty($withdraw_intact_data)) {
                foreach ($withdraw_intact_data as $withdraw_intact) {
                    $refuseWithdrawalAmount += $withdraw_intact['withdraw_money'];
                    $refuseWithdrawalCount += $withdraw_intact['user_ids'];
                }
            }

            foreach ($mysqlDeal->query($deposit_linebank_detail, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]) as $deposit_linebank) {
                if (!empty($deposit_linebank['money'])) {
                    $mark = $deposit_linebank['way_name'].'-'.$deposit_linebank['gate_name'];
                    if (!empty($linebankTranslation[$mark])) {
                        $linebankTranslation[$mark]['money'] += $deposit_linebank['money'];
                    } else {
                        $linebankTranslation += [
                            $mark => [
                                'way_name' => $deposit_linebank['way_name'],
                                'gate_name' => $deposit_linebank['gate_name'],
                                'money' => $this->intercept_num($deposit_linebank['money']),
                            ],
                        ]; //网银入款详情
                    }
                }
            }
            foreach ($mysqlDeal->query($deposit_weixin_detail, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]) as $deposit_weixin) {
                if (!empty($deposit_weixin['money'])) {
                    $mark = $deposit_weixin['way_name'].'-'.$deposit_weixin['gate_name'];
                    if (!empty($weixinTranslation[$mark])) {
                        $weixinTranslation[$mark]['money'] += $deposit_weixin['money'];
                    } else {
                        $weixinTranslation += [
                            $mark => [
                                'way_name' => $deposit_weixin['way_name'],
                                'gate_name' => $deposit_weixin['gate_name'],
                                'money' => $this->intercept_num($deposit_weixin['money']),
                            ],
                        ];
                    }
                }//微信入款详情
            }
            foreach ($mysqlDeal->query($deposit_alipay_detail, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]) as $deposit_alipay) {
                if (!empty($deposit_alipay['money'])) {
                    $mark = $deposit_alipay['way_name'].'-'.$deposit_alipay['gate_name'];
                    if (!empty($alipayTranslation[$mark])) {
                        $alipayTranslation[$mark]['money'] += $deposit_alipay['money'];
                    } else {
                        $alipayTranslation += [
                            $mark => [
                                'way_name' => $deposit_alipay['way_name'],
                                'gate_name' => $deposit_alipay['gate_name'],
                                'money' => $this->intercept_num($deposit_alipay['money']),
                            ],
                        ];
                    }
                }//支付宝入款详情
            }
            foreach ($mysqlDeal->query($deposit_bank_detail, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]) as $deposit_bank) {
                if (!empty($deposit_bank['money'])) {
                    $mark = $deposit_bank['passage_name'].'-'.$deposit_bank['to_bank_name'].'-'.$deposit_bank['to_account_number'];
                    if (!empty($bankTranslation[$mark])) {
                        $bankTranslation[$mark]['money'] += $deposit_bank['money'];
                    } else {
                        $bankTranslation += [
                            $mark => [
                                'passage_name' => $deposit_bank['passage_name'],
                                'to_bank_name' => $deposit_bank['to_bank_name'],
                                'to_account_number' => $deposit_bank['to_account_number'],
                                'money' => $this->intercept_num($deposit_bank['money']),
                            ],
                        ];
                    }
                }  //银行入款详情
            }
            foreach ($mysqlDeal->query($deposit_simple_detail, [':user_list' => $user_list, ':time_point_start' => $time_point_start, ':time_point_end' => $time_point_end]) as $deposit_simple) {
                if (!empty($deposit_simple['money'])) {
                    $mark = $deposit_simple['passage_name'].'-'.$deposit_simple['pay_url'];
                    if (!empty($simpleTranslation[$mark])) {
                        $simpleTranslation[$mark]['money'] += $deposit_simple['money'];
                    } else {
                        $simpleTranslation += [
                            $mark => [
                                'passage_name' => $deposit_simple['passage_name'],
                                'pay_url' => $deposit_simple['pay_url'],
                                'money' => $deposit_simple['money'],
                            ],
                        ];
                    }
                } //便捷入款详情
            }
        }

        foreach ($linebankTranslation as $value) {
            $depositLineBank[] = $value;
        }

        foreach ($weixinTranslation as $value) {
            $depositWeixin[] = $value;
        }

        foreach ($alipayTranslation as $value) {
            $depositAlipay[] = $value;
        }

        foreach ($bankTranslation as $value) {
            $depositBank[] = $value;
        }

        foreach ($simpleTranslation as $value) {
            $convenientRecharge[] = $value;
        }

        $rechargeDetail = [
            [
                'methodName' => '银行转账', 'methodKey' => 'bank', 'userNum' => $bankUser, 'rechargeAmount' => $this->intercept_num($bank), 'detail' => $depositBank, ],
            [
                'methodName' => '网银支付', 'methodKey' => 'onlineBank', 'userNum' => $onlineBankUser, 'rechargeAmount' => $this->intercept_num($onlineBank), 'detail' => $depositLineBank, ],
            [
                'methodName' => '微信支付', 'methodKey' => 'weixin', 'userNum' => $weixinUser, 'rechargeAmount' => $this->intercept_num($weixin), 'detail' => $depositWeixin, ],
            [
                'methodName' => '支付宝充值', 'methodKey' => 'alipay', 'userNum' => $alipayUser, 'rechargeAmount' => $this->intercept_num($alipay), 'detail' => $depositAlipay, ],
            [
                'methodName' => '便捷支付', 'methodKey' => 'convenient', 'userNum' => $convenientPaymentNum, 'rechargeAmount' => $this->intercept_num($convenientPaymentCount), 'detail' => $convenientRecharge, ],
        ];

        $betDetail = [
            [
                'betName' => '彩票游戏', 'interface_key' => 'lottery', 'betAmount' => $this->intercept_num($lottery_game_betamount), 'betQuantity' => $lottery_game_betcount, 'betNum' => $lottery_game_betnum, ],
            [
                'betName' => 'FunGaming', 'interface_key' => 'fg', 'betAmount' => $this->intercept_num($fg_betamount), 'betQuantity' => $fg_betcount, 'betNum' => $fg_betnum, ],
            [
                'betName' => '开元棋牌', 'interface_key' => 'ky', 'betAmount' => $this->intercept_num($ky_betamount), 'betQuantity' => $ky_betcount, 'betNum' => $ky_betnum, ],
            [
                'betName' => 'Lebo体育', 'interface_key' => 'lb', 'betAmount' => $this->intercept_num($lb_betamount), 'betQuantity' => $lb_betcount, 'betNum' => $lb_betnum, ],
            [
                'betName' => 'AsiaGaming', 'interface_key' => 'ag', 'betAmount' => $this->intercept_num($ag_betamount), 'betQuantity' => $ag_betcount, 'betNum' => $ag_betnum, ],
        ];

        $bonusDetail = [
            [
                'bonusName' => '彩票游戏', 'interface_key' => 'lottery', 'bonusAmount' => $this->intercept_num($lottery_game_bonusamount), 'bonusQuantity' => $lottery_game_bonuscount, 'bonusNum' => $lottery_game_bonusnum, ],
            [
                'bonusName' => 'FunGaming', 'interface_key' => 'fg', 'bonusAmount' => $this->intercept_num($fg_bonusamount), 'bonusQuantity' => $fg_bonuscount, 'bonusNum' => $fg_bonusnum, ],
            [
                'bonusName' => '开元棋牌', 'interface_key' => 'ky', 'bonusAmount' => $this->intercept_num($ky_bonusamount), 'bonusQuantity' => $ky_bonuscount, 'bonusNum' => $ky_bonusnum, ],
            [
                'bonusName' => 'Lebo体育', 'interface_key' => 'lb', 'bonusAmount' => $this->intercept_num($lb_bonusamount), 'bonusQuantity' => $lb_bonuscount, 'bonusNum' => $lb_bonusnum, ],
            [
                'bonusName' => 'AsiaGaming', 'interface_key' => 'ag', 'bonusAmount' => $this->intercept_num($ag_bonusamount), 'bonusQuantity' => $ag_bonuscount, 'bonusNum' => $ag_bonusnum, ],
        ];

        $staff_deposit_data = [
            'staffDepositAmount' => $this->intercept_num($staffDepositAmount),
            'staffDepositNum' => $staffDepositNum,
            'staffDepositdetail' => [
                'staffManualDepositAmount' => $this->intercept_num($staffDepositType1Amount),
                'staffManualDepositNum' => $staffDepositType1Num,
                'staffCancelPaymentAmount' => $this->intercept_num($staffDepositType2Amount),
                'staffCancelPaymentNum' => $staffDepositType2Num,
                'staffEventOfferAmount' => $this->intercept_num($staffDepositType3Amount),
                'staffEventOfferNum' => $staffDepositType3Num,
            ],
        ];

        $depositCount = [
            'staffWithdrawalAmount' => $this->intercept_num($staffWithdrawalAmount),
            'staffWithdrawalCount' => $staffWithdrawalCount,
            'cancelDepositAmount' => $this->intercept_num($cancelDepositAmount),
            'cancelDepositCount' => $cancelDepositCount,
            'illegalBetAmount' => $this->intercept_num($illegalBetAmount),
            'illegalBetCount' => $illegalBetCount,
            'forgoOfferAmount' => $this->intercept_num($forgoOfferAmount),
            'forgoOfferCount' => $forgoOfferCount,
            'otherWithdrawalAmount' => $this->intercept_num($otherWithdrawalAmount),
            'otherWithdrawalCount' => $otherWithdrawalCount,
        ];
        $staffWithdrawalDetail = [
            'staffWithdrawalAmountAll' => $this->intercept_num($staffWithdrawalAmountAll),
            'staffWithdrawalCountAll' => $staffWithdrawalCountAll,
            'staffWithdrawalDetail' => $depositCount,
            'refuseWithdrawalAmount' => $this->intercept_num($refuseWithdrawalAmount),
            'refuseWithdrawalCount' => $refuseWithdrawalCount,
        ];

        $deposit = [
            'recharge_amount' => $this->intercept_num($allmoney), //总充值金额
            'rechargeUserNum' => $rechargeUserNum, //充值人数
            'rechargeDetail' => $rechargeDetail,
        ];

        $activeDetail = [
            'rechargeGiftCount' => '0.00', //充值送的彩金金额　
            'rechargeGiftNum' => 0, //充值送的彩金人数
            'rechargeOfferCount' => $this->intercept_num($bank_deposit_amount), //入款优惠金额
            'rechargeOfferNum' => $bank_deposit_count, //入款优惠笔数
        ];
        $brokerageDetail = [
            'distributedBrokerage' => $this->intercept_num($distributedBrokerage), //已派发佣金
            'distributedBrokerageNum' => $distributedBrokerageNum, //已派发佣金人数
            'waitBrokerageNum' => $waitBrokerageNum, //未派发佣金人数
            'waitBrokerage' => $this->intercept_num($waitBrokerage), //未派发佣金
        ];
        $subsidyDetail = [
            'distributedSubsidy' => $this->intercept_num($distributedSubsidy),
            'distributedSubsidyNum' => $distributedSubsidyNum,
            'waitSubsidyNum' => $waitSubsidyNum,
            'waitSubsidy' => $this->intercept_num($waitSubsidy),
        ];

        $withdraw = [
            'withdraw_amount' => $this->intercept_num($allWithdraw), //总支出
            'withdrawUserNumAll' => $withdrawUserNumAll,
            'active' => $this->intercept_num($bank_deposit_amount), //活动礼金
            'activityWithdrawNum' => $activityWithdrawNum, //活动礼金人数
            'activeDetail' => $activeDetail, //活动礼金详情
            'brokerageDetail' => $brokerageDetail, //返佣详情
            'subsidyDetail' => $subsidyDetail, //反水详情
            'rebatesub' => $this->intercept_num($rebatesub), //反水金额
            'rebatesubNum' => $rebatesubNum, //反水人数
            'broker' => $this->intercept_num($broker), //返佣金额
            'brokerNum' => $brokerNum, //返佣人数
        ];
        $count = [
            'recharge_count' => $rechargeUserNumCount, //充值笔数
            'rechargeUserAll' => $rechargeUserNum,
            'withdraw_count' => $success_withdraw_count, //提现笔数
            'first_recharge_count' => $firstNum, //首充人数
            'firstMoney' => !empty($this->intercept_num($firstMoney)) ? $this->intercept_num($firstMoney) : '0.00', //首充金额
        ];
        //中上
        $bet = [
            'betCount' => $betCount, //投注总单数
            'betAmount' => $this->intercept_num($betAmount), //投注总金额
            'betNum' => $betNum, //　投注总人数
            'betDetail' => $betDetail,
        ];
        //中下
        $bonus = [
            'bonusNum' => $bonusNum, //派奖人数
            'bonusCount' => $this->intercept_num($bonusCount), //派奖金额
            'bonusFrequency' => $bonusFrequency, //派奖注数
            'bonusDetail' => $bonusDetail,
        ];
        //顶部
        $list = [
            'new_register' => $register, //今日注册人数
            'bonus_num' => $bonusNum, //派奖人数
            'bonus_count' => $this->intercept_num($bonusCount + $rebate), //派奖金额
            'first_recharge_num' => $firstNum, //首充人数
            'first_money' => !empty($this->intercept_num($firstMoney)) ? $this->intercept_num($firstMoney) : '0.00', //首充金额
            'bet_money' => $this->intercept_num($betAmount), //投注金额
            'bet_user_number' => $betNum, //$rebate投注人数
            'bonus' => $this->intercept_num($betAmount - $bonusCount - $distributedBrokerage - $distributedSubsidy - $rebate), //盈利
        ];
        $right = [
            'monthIncome' => $this->intercept_num($monthIncome), //本月损益
            'monthRate' => $this->intercept_num($monthRate), //本月毛率
            'lastMonthIncome' => $this->intercept_num($lastMonthIncome), //上月损益
            'lastMonthrRate' => $this->intercept_num($lastMonthRate), //上月毛率
            'monthRevenue' => $this->intercept_num($monthProfit), //本月盈利
            'monthRevenueRate' => $this->intercept_num($monthProfitRate), //本月盈率
            'lastMonthRevenue' => $this->intercept_num($lastMonthProfit), //上月盈利
            'lastMonthRevenueRate' => $this->intercept_num($lastMonthProfitRate), //上月盈率
            'onlineUsers' => !empty($lineUser[0]['usersNum']) ? $lineUser[0]['usersNum'] : 0, //在线用户
        ];
        $finaleResult = array_merge($list, $deposit, $withdraw, $bet, $bonus, $count, $right, $staffWithdrawalDetail, $staff_deposit_data);
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'data' => $finaleResult,
        ]);
    }
}
