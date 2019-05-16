<?php
/**
 * User.php.
 *
 * @description   用户日报插入数据任务
 * @Author  Luis
 * @date  2019-04-07
 * @links  Report/User
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-15
 */

namespace Site\Task\Report;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class User implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['time' => $time] = $context->getData();
        $adapter = $context->getAdapter();
        try {
            $daily = intval(date('Ymd', $time));
            $start_time = strtotime('today', $time);
            $end_time = $start_time + 86400 - 1;
            $mysqlReport = $config->data_report;
            $mysqlUser = $config->data_user;

            //检测数据是否锁定
            $dailyInfo = [];
            $sql = 'select daily from daily_status where daily=:daily and frozen=1';
            foreach ($mysqlReport->query($sql, [':daily' => $daily]) as $row) {
                $dailyInfo = $row;
            }
            if (!empty($dailyInfo)) {
                return;
            }

            $allrow = [];
            foreach ($config->deal_list as $deal) {
                $mysqlDeal = $config->__get('data_'.$deal);
                $sql = 'SELECT distinct user_id,user_key,account_name,layer_id '.
                    'FROM deal WHERE deal_time BETWEEN :start_time AND :end_time ';
                $param = [':start_time' => $start_time, ':end_time' => $end_time];
                $user_list = iterator_to_array($mysqlDeal->query($sql, $param));
                $user_data = [];
                $user_sql = 'select user_id,account_name as user_name,layer_id,layer_name from user_info_intact ';
                foreach ($mysqlUser->query($user_sql) as $user_detail) {
                    $user_data += [$user_detail['user_id'] => [
                        'user_name' => $user_detail['user_name'],
                        'layer_id' => $user_detail['layer_id'],
                        'layer_name' => $user_detail['layer_name'],
                    ]];
                }
                if (!empty($user_list)) {
                    foreach ($user_list as $key => $val) {
                        $userSql = 'SELECT major_id,major_name,minor_id,minor_name,agent_id,'.
                            'agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,'.
                            'broker_3_id,broker_3_key,broker_3_name,register_time'.
                            ' FROM user_cumulate WHERE user_id=:user_id';
                        $userParam = [':user_id' => $val['user_id']];
                        $userInfo = [];
                        foreach ($mysqlReport->query($userSql, $userParam)  as $rows) {
                            $userInfo = [
                                'layer_name' => $user_data[$val['user_id']]['layer_name'],
                                'major_id' => $rows['major_id'],
                                'major_name' => $rows['major_name'],
                                'minor_id' => $rows['minor_id'],
                                'minor_name' => $rows['minor_name'],
                                'agent_id' => $rows['agent_id'],
                                'agent_name' => $rows['agent_name'],
                                'broker_1_id' => $rows['broker_1_id'],
                                'broker_1_key' => $rows['broker_1_key'],
                                'broker_1_name' => $rows['broker_1_name'],
                                'broker_2_id' => $rows['broker_2_id'],
                                'broker_2_key' => $rows['broker_2_key'],
                                'broker_2_name' => $rows['broker_2_name'],
                                'broker_3_id' => $rows['broker_3_id'],
                                'broker_3_key' => $rows['broker_3_key'],
                                'broker_3_name' => $rows['broker_3_name'],
                                'register_time' => $rows['register_time'],
                            ];
                        }

                        if (!empty($userInfo)) {
                            $row = [
//                            'daily' => $daily,
                                'user_id' => $val['user_id'],
                                'user_key' => $val['user_key'],
                                'user_name' => $val['account_name'],
                                'layer_id' => $user_data[$val['user_id']]['layer_id'],
                                'layer_name' => $user_data[$val['user_id']]['layer_name'],
                                'major_id' => $userInfo['major_id'],
                                'major_name' => $userInfo['major_name'],
                                'minor_id' => $userInfo['minor_id'],
                                'minor_name' => $userInfo['minor_name'],
                                'agent_id' => $userInfo['agent_id'],
                                'agent_name' => $userInfo['agent_name'],
                                'broker_1_id' => $userInfo['broker_1_id'],
                                'broker_1_key' => $userInfo['broker_1_key'],
                                'broker_1_name' => $userInfo['broker_1_name'],
                                'broker_2_id' => $userInfo['broker_2_id'],
                                'broker_2_key' => $userInfo['broker_2_key'],
                                'broker_2_name' => $userInfo['broker_2_name'],
                                'broker_3_id' => $userInfo['broker_3_id'],
                                'broker_3_key' => $userInfo['broker_3_key'],
                                'broker_3_name' => $userInfo['broker_3_name'],
                            ];

                            //注单数  投注金额  派奖单数  派奖金额 返点单数  返点金额 *有效投注单数 有效投注金额 返水金额 损益金额
                            //彩票的
                            $sql = 'select sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,'.
                                'sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,'.
                                'sum(rebate_count) as rebate_count,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,'.
                                'sum(profit_amount) as profit_amount from daily_user_lottery where user_id=:user_id and daily=:daily';
                            $bet = [];
                            foreach ($mysqlReport->query($sql, [':user_id' => $val['user_id'], ':daily' => $daily]) as $betRow) {
                                $bet = $betRow;
                            }
                            //三方的
                            $sql = 'select sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,'.
                                'sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,'.
                                'sum(subsidy_amount) as subsidy_amount,sum(profit_amount) as profit_amount from daily_user_external where user_id=:user_id and daily=:daily';
                            foreach ($mysqlReport->query($sql, [':user_id' => $val['user_id'], ':daily' => $daily]) as $betRowEx) {
                                $betEx = $betRowEx;
                            }
                            //返佣
                            $user_id = $val['user_id'];
                            $sql = 'select brokerage from brokerage_deliver where deliver_time between :start_time and :end_time  and user_id =:user_id';
                            $userbrokerage = iterator_to_array($mysqlDeal->query($sql, [':start_time' => $start_time, ':end_time' => $end_time, ':user_id' => $user_id]));
                            $brokerage = 0;
                            if (!empty($userbrokerage[0])) {
                                $brokerage += $userbrokerage[0]['brokerage'];
                            }
                            $user_id = $val['user_id'];
                            $sql = 'select subsidy from subsidy_deliver where deliver_time between :start_time and :end_time  and user_id = :user_id';
                            $usersubsidy = iterator_to_array($mysqlDeal->query($sql, [':start_time' => $start_time, ':end_time' => $end_time, ':user_id' => $user_id]));
                            $subsidy = 0;
                            if (!empty($usersubsidy[0])) {
                                $subsidy += $usersubsidy[0]['subsidy'];
                            }
                            $row += [
                                'bet_count' => $bet['bet_count'] + $betEx['bet_count'],
                                'bet_amount' => $bet['bet_amount'] + $betEx['bet_amount'],
                                'wager_amount' => $bet['wager_amount'] + $betEx['wager_amount'],
                                'wager_count' => $bet['wager_count'] + $betEx['wager_count'],
                                'bonus_count' => $bet['bonus_count'] + $betEx['bonus_count'],
                                'bonus_amount' => $bet['bonus_amount'] + $betEx['bonus_amount'],
                                'subsidy_amount' => empty($subsidy) ? 0 : $subsidy,
                                'profit_amount' => $bet['profit_amount'] + $betEx['profit_amount'] + $brokerage,
                                'brokerage_amount' => empty($brokerage) ? 0 : $brokerage,
                                'rebate_count' => empty($bet['rebate_count']) ? 0 : $bet['rebate_count'],
                                'rebate_amount' => empty($bet['rebate_amount']) ? 0 : $bet['rebate_amount'],
                            ];

                            $num = 0;
                            $money = 0;
                            $max_deposit = [];
                            //计算三方入款的
                            $sql = 'SELECT count(deposit_serial) as num, sum(finish_money) as money,way_key,max(finish_money) AS gate_way_money '.
                                'FROM deposit_gateway_intact WHERE user_id=:user_id AND finish_time BETWEEN :start_time AND :end_time'.
                                ' group by way_key';
                            $gateList = iterator_to_array($mysqlDeal->query($sql, [':user_id' => $val['user_id'], ':start_time' => $start_time, ':end_time' => $end_time]));
                            if (!empty($gateList)) {
                                foreach ($gateList as $k => $v) {
                                    $max_deposit[] = $v['gate_way_money'];
                                    $num += $v['num'];
                                    $money += $v['money'];
                                    if ($v['way_key'] == 'bank') {
                                        $row += ['deposit_bank_count' => 0 + $v['num'], 'deposit_bank_amount' => !empty($v['money']) ? $v['money'] : 0];
                                    } elseif ($v['way_key'] == 'weixin') {
                                        $row += ['deposit_weixin_count' => 0 + $v['num'], 'deposit_weixin_amount' => !empty($v['money']) ? $v['money'] : 0];
                                    } elseif ($v['way_key'] == 'alipay') {
                                        $row += ['deposit_alipay_count' => 0 + $v['num'], 'deposit_alipay_amount' => !empty($v['money']) ? $v['money'] : 0];
                                    }
                                }
                            }
                            $row += [
                                'deposit_bank_count' => empty($row['deposit_bank_count'])?0:$row['deposit_bank_count'],
                                'deposit_bank_amount' => empty($row['deposit_bank_amount']) ? 0 : $row['deposit_bank_amount'],
                                'deposit_alipay_count' => empty($row['deposit_alipay_count']) ? 0 : $row['deposit_alipay_count'],
                                'deposit_alipay_amount' => empty($row['deposit_alipay_amount']) ? 0 : $row['deposit_alipay_amount'],
                                'deposit_weixin_count' => empty($row['deposit_weixin_count']) ? 0 : $row['deposit_weixin_count'],
                                'deposit_weixin_amount' => empty($row['deposit_weixin_amount']) ? 0 : $row['deposit_weixin_amount'],
                            ];

                            //计算银行转账
                            $banks_sql = 'SELECT count(deposit_serial) as num, sum(finish_money) as money,max(finish_money) AS bank_money,sum(finish_money-launch_money) as bank_deposit_coupon '.
                                ' FROM deposit_bank_intact WHERE user_id=:user_id AND finish_time  BETWEEN :start_time AND :end_time';
                            $Bank = [];
                            foreach ($mysqlDeal->query($banks_sql, [':user_id' => $val['user_id'], ':start_time' => $start_time, ':end_time' => $end_time]) as $banks) {
                                $Bank = $banks;
                            }
                            if (!empty($Bank)) {
                                $max_deposit[] = $Bank['bank_money'];
                                $num += $Bank['num'];
                                $money += $Bank['money'];
                                $row += ['bank_deposit_count' => 0 + $Bank['num'], 'bank_deposit_amount' => 0 + $Bank['money'], 'bank_deposit_coupon' => 0 + $Bank['bank_deposit_coupon']];
                            } else {
                                $row += ['bank_deposit_count' => 0, 'bank_deposit_amount' => 0, 'bank_deposit_coupon' => 0];
                            }

                            //快捷入款
                            $simple_sql = 'SELECT count(deposit_serial) as simple_num,sum(finish_money) as finish_money,max(finish_money) as simple_money'.
                                ' FROM deposit_simple_intact WHERE user_id=:user_id AND finish_time BETWEEN :start_time AND :end_time';
                            $tmp = [];
                            foreach ($mysqlDeal->query($simple_sql, [':user_id' => $val['user_id'], ':start_time' => $start_time, ':end_time' => $end_time]) as $item) {
                                $tmp = $item;
                            }
                            if (!empty($tmp)) {
                                $num += $tmp['simple_num'];
                                $money += $tmp['finish_money'];
                                $max_deposit[] = $tmp['simple_money'];
                                $row += ['simple_deposit_count' => !empty($tmp['simple_num']) ? $tmp['simple_num'] : 0, 'simple_deposit_amount' => !empty($tmp['finish_money']) ? $tmp['finish_money'] : 0];
                            } else {
                                $row += ['simple_deposit_count' => 0, 'simple_deposit_amount' => 0];
                            }

                            //计算人工入款的次数和金额
                            $staff_sql = 'SELECT count(deal_serial) as num, sum(money) as money,max(money) as staff_max,sum(coupon_audit) as coupon'.
                                ' FROM staff_deposit_intact WHERE user_id=:user_id AND deposit_time  BETWEEN :start_time AND :end_time';
                            $staffDeposit = [];
                            foreach ($mysqlDeal->query($staff_sql, [':user_id' => $val['user_id'], ':start_time' => $start_time, ':end_time' => $end_time]) as $staff_deposit) {
                                $staffDeposit += $staff_deposit;
                            }
                            if (!empty($staffDeposit)) {
                                $max_deposit[] = $staffDeposit['staff_max'];
                                $num += $staffDeposit['num'];
                                $money += $staffDeposit['money'];
                                $row += ['deposit_max' => !empty(max($max_deposit)) ? max($max_deposit) : 0, 'staff_deposit_count' => 0 + $staffDeposit['num'], 'staff_deposit_amount' => !empty($staffDeposit['money']) ? $staffDeposit['money'] : 0, 'coupon_amount' => 0];
                            //活动礼金金额
                            } else {
                                $row += ['deposit_max' => 0, 'staff_deposit_count' => 0, 'staff_deposit_amount' => 0, 'coupon_amount' => 0];
                            }
                            //计算便捷入款的次数和金额
                            //计算成功入款次数和金额
                            $deposit_count = $num;
                            $deposit_amount = $money;
                            $row += ['deposit_count' => $deposit_count, 'deposit_amount' => $deposit_amount];

                            //计算人工出款的次数及金额
                            $sql = 'SELECT count(deal_serial) as num, sum(money) as money,max(money) as max_staff_withdraw_money'.
                                ' FROM staff_withdraw_intact WHERE user_id=:user_id AND withdraw_time  BETWEEN :start_time AND :end_time';
                            $staffWithdraw = [];
                            $max_withdraw = [];
                            foreach ($mysqlDeal->query($sql, [':user_id' => $val['user_id'], ':start_time' => $start_time, ':end_time' => $end_time]) as $staff_withdraw) {
                                $staffWithdraw += $staff_withdraw;
                            }
                            if (!empty($staffWithdraw)) {
                                $max_withdraw[] = $staffWithdraw['max_staff_withdraw_money'];
                                $row += ['staff_withdraw_count' => 0 + $staffWithdraw['num'], 'staff_withdraw_amount' => !empty($staffWithdraw['money']) ? $staffWithdraw['money'] : 0];
                            } else {
                                $row += ['staff_withdraw_count' => 0, 'staff_withdraw_amount' => 0];
                            }

                            //计算出款的次数和金额
                            $sql = 'SELECT count(distinct(if(finish_time> :start_time AND finish_time <:end_time,withdraw_serial,null))) as num, sum(launch_money) as money,max(launch_money) as max_withdraw_money'.
                                ' FROM withdraw_intact WHERE user_id=:user_id AND finish_time  BETWEEN :start_time AND :end_time';
                            foreach ($mysqlDeal->query($sql, [':user_id' => $val['user_id'], ':start_time' => $start_time, ':end_time' => $end_time]) as $withdraws) {
                                $withdraw = $withdraws;
                                $max_withdraw[] = $withdraws['max_withdraw_money'];
                            }

                            //计算成功出款次数和金额
                            $withdraw_count = $staffWithdraw['num'] + $withdraw['num'];
                            $withdraw_amount = $staffWithdraw['money'] + $withdraw['money'];
                            $row += ['withdraw_max' => !empty(max($max_withdraw)) ? max($max_withdraw) : 0, 'withdraw_count' => $withdraw_count, 'withdraw_amount' => $withdraw_amount];

                            //判断是否是活跃用户
                            $agentSql = 'SELECT layer_id FROM user_cumulate WHERE user_id=:user_id';
                            $agentParam = [':user_id' => $userInfo['broker_1_id']];
                            foreach ($mysqlReport->query($agentSql, $agentParam) as $agentrow) {
                                $broker_agent = $agentrow['layer_id'];
                            }
                            if (!empty($broker_agent)) {
                                $brokerSql = 'SELECT * FROM brokerage_setting WHERE layer_id=:layer_id';
                                $brokerParam = [':layer_id' => $broker_agent];
                                foreach ($mysqlUser->query($brokerSql, $brokerParam) as $brokerrow) {
                                    $brokerRow = $brokerrow;
                                }
                                $betAmount = $bet['wager_amount'] + $betEx['wager_amount'];
                                if ($betAmount >= $brokerRow['min_bet_amount']) {
                                    $row += ['is_active' => 1]; //活跃用户
                                } else {
                                    $row += ['is_active' => 0];
                                }
                            } else {
                                $row += ['is_active' => 1];
                            }

                            //判断是否是首充用户
                            $sql = 'SELECT first_deposit_time FROM user_event WHERE user_id=:user_id AND first_deposit_time BETWEEN :start_time AND :end_time';
                            $first = iterator_to_array($mysqlReport->query($sql, [':user_id' => $val['user_id'], ':start_time' => strtotime(date('Ymd', $time)), ':end_time' => strtotime(date('Ymd', $time)) + 86400]));
                            if (!empty($first)) {
                                $row += ['is_first_deposit' => 1];
                            } else {
                                $row += ['is_first_deposit' => 0];
                            }
                            //判断是否是新注册用户
                            if (($userInfo['register_time'] >= $start_time) && ($userInfo['register_time'] <= $end_time)) {
                                $row += ['is_today_register' => 1];
                            } else {
                                $row += ['is_today_register' => 0];
                            }
                            $allrow[] = $row;
                        }
                    }
                }
            }
            $user_data = [];
            $mysqlReport->daily_user->load($allrow, ['daily' => $daily], 'replace');
            $user_sql = 'select user_id,account_name as user_name,layer_id,layer_name from user_info_intact ';
            foreach ($mysqlUser->query($user_sql) as $user_detail) {
                $user_data += [$user_detail['user_id'] => [
                    'user_name' => $user_detail['user_name'],
                    'layer_id' => $user_detail['layer_id'],
                    'layer_name' => $user_detail['layer_name'],
                ]];
            }
            $weekly = intval(date('oW', $time));
            $first_day = date('Ymd',strtotime('this week',$time));
            $last_day = date('Ymd',strtotime($first_day) + 7 * 86400 -1);
            $sql = 'select user_id,0 as user_name,user_key, 0 as layer_id,0 as layer_name ,major_id,major_name,minor_id,minor_name,agent_id,agent_name,'.
                'broker_1_id,broker_1_key, broker_2_id ,broker_2_key ,broker_3_id,broker_3_key,sum(deposit_count) as deposit_count,'.
                'broker_1_name,broker_2_name,broker_3_name,'.
                'sum(deposit_amount)  as deposit_amount,sum(deposit_bank_count) as deposit_bank_count,'.
                'sum(deposit_bank_amount) as deposit_bank_amount,sum(deposit_weixin_count) as deposit_weixin_count,'.
                'sum(deposit_weixin_amount)as deposit_weixin_amount,sum(deposit_alipay_count) as deposit_alipay_count,'.
                'sum(deposit_alipay_amount) as deposit_alipay_amount,sum(bank_deposit_count) as bank_deposit_count,'.
                'sum(bank_deposit_amount) as bank_deposit_amount,sum(staff_deposit_count) as staff_deposit_count,'.
                'sum(staff_deposit_amount) as staff_deposit_amount,sum(withdraw_count) as withdraw_count,'.
                'sum(withdraw_amount) as withdraw_amount,sum(staff_withdraw_count) as staff_withdraw_count,'.
                'sum(staff_withdraw_amount) as staff_withdraw_amount,sum(coupon_amount) as coupon_amount,sum(bet_count) as bet_count,'.
                'sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,'.
                'sum(rebate_count) as rebate_count ,sum(brokerage_amount) as brokerage_amount,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount ,'.
                'sum(profit_amount) as profit_amount from daily_user where  daily between :first_time and :end_day group by '.
                'user_id,user_key,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,'.
                'broker_1_key, broker_2_id ,broker_2_key ,broker_3_id,broker_3_key,broker_1_name,broker_2_name,broker_3_name ';

            $user_list = iterator_to_array($mysqlReport->query($sql, [':first_time' => $first_day, ':end_day' => $last_day]));
            $week_data = [];
            if (!empty($user_list[0]['user_id'])) {
                foreach ($user_list as $val) {
                    $week_data[] = [
                        'user_id' => $val['user_id'],
                        'user_key' => $val['user_key'],
                        'user_name' => !empty($user_data[$val['user_id']]['user_name']) ? $user_data[$val['user_id']]['user_name'] : 0,
                        'layer_id' => $user_data[$val['user_id']]['layer_id'],
                        'layer_name' => $user_data[$val['user_id']]['layer_name'],
                        'major_id' => $val['major_id'],
                        'major_name' => $val['major_name'],
                        'minor_id' => $val['minor_id'],
                        'minor_name' => $val['minor_name'],
                        'agent_id' => $val['agent_id'],
                        'agent_name' => $val['agent_name'],
                        'broker_1_id' => $val['broker_1_id'],
                        'broker_1_key' => $val['broker_1_key'],
                        'broker_1_name' => $val['broker_1_name'],
                        'broker_2_name' => $val['broker_2_name'],
                        'broker_3_name' => $val['broker_3_name'],
                        'broker_2_id' => $val['broker_2_id'],
                        'broker_2_key' => $val['broker_2_key'],
                        'broker_3_id' => $val['broker_3_id'],
                        'broker_3_key' => $val['broker_3_key'],
                        'deposit_count' => empty($val['deposit_count']) ? 0 : $val['deposit_count'],
                        'deposit_amount' => empty($val['deposit_amount']) ? 0 : $val['deposit_amount'],
                        'deposit_bank_count' => empty($val['deposit_bank_count']) ? 0 : $val['deposit_bank_count'],
                        'deposit_bank_amount' => empty($val['deposit_bank_amount']) ? 0 : $val['deposit_bank_amount'],
                        'deposit_weixin_count' => empty($val['deposit_weixin_count']) ? 0 : $val['deposit_weixin_count'],
                        'deposit_weixin_amount' => empty($val['deposit_weixin_amount']) ? 0 : $val['deposit_weixin_amount'],
                        'deposit_alipay_count' => empty($val['deposit_alipay_count']) ? 0 : $val['deposit_alipay_count'],
                        'deposit_alipay_amount' => empty($val['deposit_alipay_amount']) ? 0 : $val['deposit_alipay_amount'],
                        'bank_deposit_count' => empty($val['bank_deposit_count']) ? 0 : $val['bank_deposit_count'],
                        'bank_deposit_amount' => empty($val['bank_deposit_amount']) ? 0 : $val['bank_deposit_amount'],
                        'staff_deposit_count' => empty($val['staff_deposit_count']) ? 0 : $val['staff_deposit_count'],
                        'staff_deposit_amount' => empty($val['staff_deposit_amount']) ? 0 : $val['staff_deposit_amount'],
                        'withdraw_count' => empty($val['withdraw_count']) ? 0 : $val['withdraw_count'],
                        'withdraw_amount' => empty($val['withdraw_amount']) ? 0 : $val['withdraw_amount'],
                        'staff_withdraw_count' => empty($val['staff_withdraw_count']) ? 0 : $val['staff_withdraw_count'],
                        'staff_withdraw_amount' => empty($val['staff_withdraw_amount']) ? 0 : $val['staff_withdraw_amount'],
                        'coupon_amount' => empty($val['coupon_amount']) ? 0 : $val['coupon_amount'],
                        'bet_count' => empty($val['bet_count']) ? 0 : $val['bet_count'],
                        'bet_amount' => empty($val['bet_amount']) ? 0 : $val['bet_amount'],
                        'wager_count' => empty($val['wager_count']) ? 0 : $val['wager_count'],
                        'wager_amount' => empty($val['wager_amount']) ? 0 : $val['wager_amount'],
                        'bonus_count' => empty($val['bonus_count']) ? 0 : $val['bonus_count'],
                        'bonus_amount' => empty($val['bonus_amount']) ? 0 : $val['bonus_amount'],
                        'rebate_count' => empty($val['rebate_count']) ? 0 : $val['rebate_count'],
                        'rebate_amount' => empty($val['rebate_amount']) ? 0 : $val['rebate_amount'],
                        'subsidy_amount' => empty($val['subsidy_amount']) ? 0 : $val['subsidy_amount'],
                        'brokerage_amount' => empty($val['brokerage_amount']) ? 0 : $val['brokerage_amount'],
                        'profit_amount' => empty($val['profit_amount']) ? 0 : $val['profit_amount'],
                    ];
                }
            }

            if (!empty($week_data)) {
                $mysqlReport->weekly_user->load($week_data, [
                    'weekly' => $weekly,
                ], 'replace');
            }

            $monthly = intval(date('Ym', $time));
            $first_day = date('Ym01', $time);
            $last_day = date('Ymd', strtotime(date('Y-m-01', $time).' +1 month -1 day'));
            $sql = 'select user_id,0 as user_name, user_key, 0 as layer_id,0 as layer_name ,major_id,major_name,minor_id,minor_name,agent_id,agent_name,'.
                'broker_1_id,broker_1_key, broker_2_id ,broker_2_key ,broker_3_id,broker_3_key,sum(deposit_count) as deposit_count,'.
                'broker_1_name,broker_2_name,broker_3_name,'.
                'sum(deposit_amount)  as deposit_amount,sum(deposit_bank_count) as deposit_bank_count,'.
                'sum(deposit_bank_amount) as deposit_bank_amount,sum(deposit_weixin_count) as deposit_weixin_count,'.
                'sum(deposit_weixin_amount)as deposit_weixin_amount,sum(deposit_alipay_count) as deposit_alipay_count,'.
                'sum(deposit_alipay_amount) as deposit_alipay_amount,sum(bank_deposit_count) as bank_deposit_count,'.
                'sum(bank_deposit_amount) as bank_deposit_amount,sum(staff_deposit_count) as staff_deposit_count,'.
                'sum(staff_deposit_amount) as staff_deposit_amount,sum(withdraw_count) as withdraw_count,'.
                'sum(withdraw_amount) as withdraw_amount,sum(staff_withdraw_count) as staff_withdraw_count,'.
                'sum(staff_withdraw_amount) as staff_withdraw_amount,sum(coupon_amount) as coupon_amount,sum(bet_count) as bet_count,'.
                'sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,'.
                'sum(rebate_count) as rebate_count ,sum(brokerage_amount) as brokerage_amount,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount ,'.
                'sum(profit_amount) as profit_amount from daily_user where  daily between :first_time and :end_day group by '.
                'user_id,user_key,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,'.
                'broker_1_key, broker_2_id ,broker_2_key ,broker_3_id,broker_3_key,broker_1_name,broker_2_name,broker_3_name ';

            $user_list_monthly = iterator_to_array($mysqlReport->query($sql, [':first_time' => $first_day, ':end_day' => $last_day]));
            $month_data = [];
            if (!empty($user_list_monthly[0]['user_id'])) {
                foreach ($user_list_monthly as $key => $val) {
                    $month_data[] = [
                        'user_id' => $val['user_id'],
                        'user_key' => $val['user_key'],
                        'user_name' => !empty($user_data[$val['user_id']]['user_name']) ? $user_data[$val['user_id']]['user_name'] : 0,
                        'layer_id' => $user_data[$val['user_id']]['layer_id'],
                        'layer_name' => $user_data[$val['user_id']]['layer_name'],
                        'major_id' => $val['major_id'],
                        'major_name' => $val['major_name'],
                        'minor_id' => $val['minor_id'],
                        'minor_name' => $val['minor_name'],
                        'agent_id' => $val['agent_id'],
                        'agent_name' => $val['agent_name'],
                        'broker_1_id' => $val['broker_1_id'],
                        'broker_1_key' => $val['broker_1_key'],
                        'broker_1_name' => $val['broker_1_name'],
                        'broker_2_name' => $val['broker_2_name'],
                        'broker_3_name' => $val['broker_3_name'],
                        'broker_2_id' => $val['broker_2_id'],
                        'broker_2_key' => $val['broker_2_key'],
                        'broker_3_id' => $val['broker_3_id'],
                        'broker_3_key' => $val['broker_3_key'],
                        'deposit_count' => empty($val['deposit_count']) ? 0 : $val['deposit_count'],
                        'deposit_amount' => empty($val['deposit_amount']) ? 0 : $val['deposit_amount'],
                        'deposit_bank_count' => empty($val['deposit_bank_count']) ? 0 : $val['deposit_bank_count'],
                        'deposit_bank_amount' => empty($val['deposit_bank_amount']) ? 0 : $val['deposit_bank_amount'],
                        'deposit_weixin_count' => empty($val['deposit_weixin_count']) ? 0 : $val['deposit_weixin_count'],
                        'deposit_weixin_amount' => empty($val['deposit_weixin_amount']) ? 0 : $val['deposit_weixin_amount'],
                        'deposit_alipay_count' => empty($val['deposit_alipay_count']) ? 0 : $val['deposit_alipay_count'],
                        'deposit_alipay_amount' => empty($val['deposit_alipay_amount']) ? 0 : $val['deposit_alipay_amount'],
                        'bank_deposit_count' => empty($val['bank_deposit_count']) ? 0 : $val['bank_deposit_count'],
                        'bank_deposit_amount' => empty($val['bank_deposit_amount']) ? 0 : $val['bank_deposit_amount'],
                        'staff_deposit_count' => empty($val['staff_deposit_count']) ? 0 : $val['staff_deposit_count'],
                        'staff_deposit_amount' => empty($val['staff_deposit_amount']) ? 0 : $val['staff_deposit_amount'],
                        'withdraw_count' => empty($val['withdraw_count']) ? 0 : $val['withdraw_count'],
                        'withdraw_amount' => empty($val['withdraw_amount']) ? 0 : $val['withdraw_amount'],
                        'staff_withdraw_count' => empty($val['staff_withdraw_count']) ? 0 : $val['staff_withdraw_count'],
                        'staff_withdraw_amount' => empty($val['staff_withdraw_amount']) ? 0 : $val['staff_withdraw_amount'],
                        'coupon_amount' => empty($val['coupon_amount']) ? 0 : $val['coupon_amount'],
                        'bet_count' => empty($val['bet_count']) ? 0 : $val['bet_count'],
                        'bet_amount' => empty($val['bet_amount']) ? 0 : $val['bet_amount'],
                        'wager_count' => empty($val['wager_count']) ? 0 : $val['wager_count'],
                        'wager_amount' => empty($val['wager_amount']) ? 0 : $val['wager_amount'],
                        'bonus_count' => empty($val['bonus_count']) ? 0 : $val['bonus_count'],
                        'bonus_amount' => empty($val['bonus_amount']) ? 0 : $val['bonus_amount'],
                        'rebate_count' => empty($val['rebate_count']) ? 0 : $val['rebate_count'],
                        'rebate_amount' => empty($val['rebate_amount']) ? 0 : $val['rebate_amount'],
                        'subsidy_amount' => empty($val['subsidy_amount']) ? 0 : $val['subsidy_amount'],
                        'brokerage_amount' => empty($val['brokerage_amount']) ? 0 : $val['brokerage_amount'],
                        'profit_amount' => empty($val['profit_amount']) ? 0 : $val['profit_amount'],
                    ];
                }
            }

            if (!empty($month_data)) {
                $mysqlReport->monthly_user->load($month_data, [
                    'monthly' => $monthly,
                ], 'replace');
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/Staff', ['time' => $time], time(), 9);
        }
    }
}
