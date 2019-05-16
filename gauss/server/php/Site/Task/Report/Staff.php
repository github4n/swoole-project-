<?php
/**
 * Staff.php.
 *
 * @description   体系线报表插入数据任务
 * @Author  nathan
 * @date  2019-04-07
 * @links  Initialize.php
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-09
 */

namespace Site\Task\Report;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Staff implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['time' => $time] = $context->getData();
        $adapter = $context->getAdapter();
        try {
            $daily = intval(date('Ymd', $time));
            $mysqlReport = $config->data_report;

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

            $sql = 'SELECT major_id,major_name,minor_id,minor_name,agent_id,agent_name,'.
                'sum(is_first_deposit) as user_first_deposit,sum(is_first_deposit*deposit_amount) as amount_first_deposit,'.
                'sum(is_active) as user_active,sum(deposit_count) as deposit_count,sum(deposit_amount) as deposit_amount,'.
                'count(deposit_amount>0 or null) as deposit_user,sum(deposit_bank_count) as deposit_bank_count,'.
                'sum(deposit_bank_amount) as deposit_bank_amount,count(deposit_weixin_count>0 or null) as deposit_weixin_user,'.
                'sum(deposit_weixin_count) as deposit_weixin_count,sum(deposit_weixin_amount) as deposit_weixin_amount,'.
                'sum(deposit_alipay_count) as deposit_alipay_count,sum(deposit_alipay_amount) as deposit_alipay_amount,'.
                'count(deposit_alipay_count>0 or null) as deposit_alipay_user,count(bank_deposit_count>0 or null) as bank_deposit_user,'.
                'sum(bank_deposit_count) as bank_deposit_count,sum(bank_deposit_amount) as bank_deposit_amount,'.
                'sum(staff_deposit_count) as staff_deposit_count,sum(staff_deposit_amount) as staff_deposit_amount,'.
                'count(staff_deposit_count>0 or null) as staff_deposit_user,sum(withdraw_count) as withdraw_count,'.
                'sum(withdraw_amount) as withdraw_amount,count(withdraw_count>0 or null) as withdraw_user,'.
                'count(DISTINCT (IF (bet_amount>0,user_id,null))) as bet_user,count(rebate_amount>0 or null) as rebate_user,count(bonus_amount>0 or null) as bonus_user,'.
                'count(staff_withdraw_count>0 or null) as staff_withdraw_user,count(coupon_amount>0 or null) as coupon_user,'.
                'sum(staff_withdraw_count) as staff_withdraw_count,sum(staff_withdraw_amount) as staff_withdraw_amount,'.
                'sum(coupon_amount) as coupon_amount,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,'.
                'sum(wager_amount) as wager_amount,count(DISTINCT (IF (wager_amount>0,user_id,null))) as wager_user,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,sum(rebate_count) as rebate_count,'.
                'sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,'.
                'count(deposit_bank_amount>0 or null) as deposit_bank_user,'.
                'count(subsidy_amount>0 or null) as subsidy_user,sum(brokerage_amount) as brokerage_amount,count(brokerage_amount>0 or null) as brokerage_user '.
                'FROM daily_user WHERE daily=:daily group by major_id,major_name,minor_id,minor_name,agent_id,agent_name';
            //返佣总人数返佣金额
            $all_list = iterator_to_array($mysqlReport->query($sql, [':daily' => $daily]));
            if (!empty($all_list)) {
                foreach ($all_list as $key => $val) {
                    $user_sql = 'select user_id from user_cumulate where agent_id=:agent_id';
                    $user_all = $mysqlReport->execute($user_sql, [':agent_id' => $val['agent_id']]);
                    $start_time = strtotime($daily);
                    $end_time = $start_time + 86399;
                    //今日注册人数
                    $user_register_sql = 'select user_id from user_cumulate where agent_id=:agent_id and register_time between :start_time and :end_time';
                    $user_register = $mysqlReport->execute($user_register_sql, [':agent_id' => $val['agent_id'], ':start_time' => $start_time, ':end_time' => $end_time]);
                    $row = ['daily' => $daily, 'major_id' => $val['major_id'], 'major_name' => $val['major_name'],
                        'minor_id' => $val['minor_id'], 'minor_name' => $val['minor_name'], 'agent_id' => $val['agent_id'],
                        'agent_name' => $val['agent_name'], 'user_all' => $user_all, 'user_active' => $val['user_active'],
                        'user_first_deposit' => $val['user_first_deposit'], 'amount_first_deposit' => $val['amount_first_deposit'],
                        'deposit_count' => $val['deposit_count'], 'deposit_user' => $val['deposit_user'],
                        'deposit_amount' => $val['deposit_amount'], 'deposit_bank_count' => $val['deposit_bank_count'],
                        'deposit_bank_user' => $val['deposit_bank_user'], 'deposit_bank_amount' => $val['deposit_bank_amount'],
                        'deposit_weixin_count' => $val['deposit_weixin_count'], 'deposit_weixin_user' => $val['deposit_weixin_user'],
                        'deposit_weixin_amount' => $val['deposit_weixin_amount'], 'withdraw_user' => $val['withdraw_user'],
                        'deposit_alipay_count' => $val['deposit_alipay_count'], 'deposit_alipay_user' => $val['deposit_alipay_user'],
                        'deposit_alipay_amount' => $val['deposit_alipay_amount'], 'bank_deposit_count' => $val['bank_deposit_count'],
                        'bank_deposit_user' => $val['bank_deposit_user'], 'bank_deposit_amount' => $val['bank_deposit_amount'],
                        'staff_deposit_count' => $val['staff_deposit_count'], 'staff_deposit_user' => $val['staff_deposit_user'],
                        'staff_deposit_amount' => $val['staff_deposit_amount'], 'withdraw_count' => $val['withdraw_count'],
                        'staff_withdraw_count' => $val['staff_withdraw_count'], 'staff_withdraw_user' => $val['staff_withdraw_user'],
                        'staff_withdraw_amount' => $val['staff_withdraw_amount'], 'coupon_user' => $val['coupon_user'],
                        'coupon_amount' => $val['coupon_amount'], 'bet_count' => $val['bet_count'], 'bet_user' => $val['bet_user'],
                        'bet_amount' => $val['bet_amount'], 'wager_count' => $val['wager_count'], 'wager_user' => $val['wager_user'], 'wager_amount' => $val['wager_amount'], 'bonus_count' => $val['bonus_count'], 'bonus_user' => $val['bonus_user'],
                        'bonus_amount' => $val['bonus_amount'], 'rebate_count' => $val['rebate_count'],
                        'rebate_user' => $val['rebate_user'], 'rebate_amount' => $val['rebate_amount'],
                        'subsidy_user' => $val['subsidy_user'], 'subsidy_amount' => $val['subsidy_amount'],
                        'profit_amount' => $val['wager_amount'] - $val['rebate_amount'] - $val['bonus_amount'],
                        'user_register' => $user_register,
                        'withdraw_amount' => $val['withdraw_amount'],
                        'brokerage_user' => $val['brokerage_user'], 'brokerage_amount' => !empty($val['brokerage_amount']) ? $val['brokerage_amount'] : 0,
                    ];

                    $allrow[] = $row;
                }
            }
            $mysqlReport->daily_staff->load($allrow, [], 'replace');
            $weekly = intval(date('oW', $time));
            $first_day = date('Ymd',strtotime('this week',$time));
            $last_day = date('Ymd',strtotime($first_day) + 7 * 86400 -1);
            $week_start_time = strtotime($first_day);
            $week_end_time = strtotime($last_day) + 86399;
            $sql = 'SELECT major_id,major_name,minor_id,minor_name,agent_id,agent_name,'.
                'sum(is_first_deposit) as user_first_deposit,sum(is_first_deposit*deposit_amount) as amount_first_deposit,'.
                'sum(is_active) as user_active,sum(deposit_count) as deposit_count,sum(deposit_amount) as deposit_amount,'.
                'count(deposit_amount>0 or null) as deposit_user,sum(deposit_bank_count) as deposit_bank_count,'.
                'sum(deposit_bank_amount) as deposit_bank_amount,count(deposit_weixin_count>0 or null) as deposit_weixin_user,'.
                'sum(deposit_weixin_count) as deposit_weixin_count,sum(deposit_weixin_amount) as deposit_weixin_amount,'.
                'sum(deposit_alipay_count) as deposit_alipay_count,sum(deposit_alipay_amount) as deposit_alipay_amount,'.
                'count(deposit_alipay_count>0 or null) as deposit_alipay_user,count(bank_deposit_count>0 or null) as bank_deposit_user,'.
                'sum(bank_deposit_count) as bank_deposit_count,sum(bank_deposit_amount) as bank_deposit_amount,'.
                'sum(staff_deposit_count) as staff_deposit_count,sum(staff_deposit_amount) as staff_deposit_amount,'.
                'count(staff_deposit_count>0 or null) as staff_deposit_user,sum(withdraw_count) as withdraw_count,'.
                'sum(withdraw_amount) as withdraw_amount,count(withdraw_count>0 or null) as withdraw_user,'.
                'count(DISTINCT (IF (bet_amount>0,user_id,null))) as bet_user,count(rebate_amount>0 or null) as rebate_user,count(bonus_amount>0 or null) as bonus_user,'.
                'count(staff_withdraw_count>0 or null) as staff_withdraw_user,count(coupon_amount>0 or null) as coupon_user,'.
                'sum(staff_withdraw_count) as staff_withdraw_count,sum(staff_withdraw_amount) as staff_withdraw_amount,'.
                'sum(coupon_amount) as coupon_amount,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,'.
                'sum(wager_amount) as wager_amount,count(DISTINCT (IF (wager_amount>0,user_id,null))) as wager_user,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,sum(rebate_count) as rebate_count,'.
                'sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,'.
                'count(deposit_bank_amount>0 or null) as deposit_bank_user,'.
                'count(subsidy_amount>0 or null) as subsidy_user,sum(brokerage_amount) as brokerage_amount,count(brokerage_amount>0 or null) as brokerage_user '.
                'FROM daily_user WHERE daily BETWEEN :start_day AND :end_day group by major_id,major_name,minor_id,minor_name,agent_id,agent_name';

            $allrows = [];
            $generator = iterator_to_array($mysqlReport->query($sql, [':start_day' => $first_day, ':end_day' => $last_day]));
            if (!empty($generator)) {
                foreach ($generator as $key => $val) {
                    $user_sql = 'select user_id from user_cumulate where agent_id=:agent_id';
                    $user_all = $mysqlReport->execute($user_sql, [':agent_id' => $val['agent_id']]);
                    //今日注册人数
                    $user_register_sql = 'select user_id from user_cumulate where agent_id=:agent_id and register_time between :start_time and :end_time';
                    $user_register = $mysqlReport->execute($user_register_sql, [':agent_id' => $val['agent_id'], ':start_time' => $week_start_time, ':end_time' => $week_end_time]);
                    $row = ['daily' => $daily, 'major_id' => $val['major_id'], 'major_name' => $val['major_name'],
                        'minor_id' => $val['minor_id'], 'minor_name' => $val['minor_name'], 'agent_id' => $val['agent_id'],
                        'agent_name' => $val['agent_name'], 'user_all' => $user_all, 'user_active' => $val['user_active'],
                        'user_first_deposit' => $val['user_first_deposit'], 'amount_first_deposit' => $val['amount_first_deposit'],
                        'deposit_count' => $val['deposit_count'], 'deposit_user' => $val['deposit_user'],
                        'deposit_amount' => $val['deposit_amount'], 'deposit_bank_count' => $val['deposit_bank_count'],
                        'deposit_bank_user' => $val['deposit_bank_user'], 'deposit_bank_amount' => $val['deposit_bank_amount'],
                        'deposit_weixin_count' => $val['deposit_weixin_count'], 'deposit_weixin_user' => $val['deposit_weixin_user'],
                        'deposit_weixin_amount' => $val['deposit_weixin_amount'], 'withdraw_user' => $val['withdraw_user'],
                        'deposit_alipay_count' => $val['deposit_alipay_count'], 'deposit_alipay_user' => $val['deposit_alipay_user'],
                        'deposit_alipay_amount' => $val['deposit_alipay_amount'], 'bank_deposit_count' => $val['bank_deposit_count'],
                        'bank_deposit_user' => $val['bank_deposit_user'], 'bank_deposit_amount' => $val['bank_deposit_amount'],
                        'staff_deposit_count' => $val['staff_deposit_count'], 'staff_deposit_user' => $val['staff_deposit_user'],
                        'staff_deposit_amount' => $val['staff_deposit_amount'], 'withdraw_count' => $val['withdraw_count'],
                        'staff_withdraw_count' => $val['staff_withdraw_count'], 'staff_withdraw_user' => $val['staff_withdraw_user'],
                        'staff_withdraw_amount' => $val['staff_withdraw_amount'], 'coupon_user' => $val['coupon_user'],
                        'coupon_amount' => $val['coupon_amount'], 'bet_count' => $val['bet_count'], 'bet_user' => $val['bet_user'],
                        'bet_amount' => $val['bet_amount'], 'wager_count' => $val['wager_count'], 'wager_user' => $val['wager_user'], 'wager_amount' => $val['wager_amount'], 'bonus_count' => $val['bonus_count'], 'bonus_user' => $val['bonus_user'],
                        'bonus_amount' => $val['bonus_amount'], 'rebate_count' => $val['rebate_count'],
                        'rebate_user' => $val['rebate_user'], 'rebate_amount' => $val['rebate_amount'],
                        'subsidy_user' => $val['subsidy_user'], 'subsidy_amount' => $val['subsidy_amount'],
                        'profit_amount' => $val['wager_amount'] - $val['rebate_amount'] - $val['bonus_amount'],
                        'user_register' => $user_register,
                        'withdraw_amount' => $val['withdraw_amount'],
                        'brokerage_user' => $val['brokerage_user'], 'brokerage_amount' => !empty($val['brokerage_amount']) ? $val['brokerage_amount'] : 0,
                    ];

                    $allrows[] = $row;
                }
            }
            if (!empty($generator)) {
                $mysqlReport->weekly_staff->load($allrows, [
                    'weekly' => $weekly,
                ], 'replace');
            }

            $monthly = intval(date('Ym', $time));
            $first_day = date('Ym01', $time);
            $last_day = date('Ymd', strtotime(date('Y-m-01', $time).' +1 month -1 day'));
            $month_start_time = strtotime($first_day);
            $month_end_time = strtotime($last_day) + 86399;
            $sql = 'SELECT major_id,major_name,minor_id,minor_name,agent_id,agent_name,'.
                'sum(is_first_deposit) as user_first_deposit,sum(is_first_deposit*deposit_amount) as amount_first_deposit,'.
                'sum(is_active) as user_active,sum(deposit_count) as deposit_count,sum(deposit_amount) as deposit_amount,'.
                'count(deposit_amount>0 or null) as deposit_user,sum(deposit_bank_count) as deposit_bank_count,'.
                'sum(deposit_bank_amount) as deposit_bank_amount,count(deposit_weixin_count>0 or null) as deposit_weixin_user,'.
                'sum(deposit_weixin_count) as deposit_weixin_count,sum(deposit_weixin_amount) as deposit_weixin_amount,'.
                'sum(deposit_alipay_count) as deposit_alipay_count,sum(deposit_alipay_amount) as deposit_alipay_amount,'.
                'count(deposit_alipay_count>0 or null) as deposit_alipay_user,count(bank_deposit_count>0 or null) as bank_deposit_user,'.
                'sum(bank_deposit_count) as bank_deposit_count,sum(bank_deposit_amount) as bank_deposit_amount,'.
                'sum(staff_deposit_count) as staff_deposit_count,sum(staff_deposit_amount) as staff_deposit_amount,'.
                'count(staff_deposit_count>0 or null) as staff_deposit_user,sum(withdraw_count) as withdraw_count,'.
                'sum(withdraw_amount) as withdraw_amount,count(withdraw_count>0 or null) as withdraw_user,'.
                'count(DISTINCT (IF (bet_amount>0,user_id,null))) as bet_user,count(rebate_amount>0 or null) as rebate_user,count(bonus_amount>0 or null) as bonus_user,'.
                'count(staff_withdraw_count>0 or null) as staff_withdraw_user,count(coupon_amount>0 or null) as coupon_user,'.
                'sum(staff_withdraw_count) as staff_withdraw_count,sum(staff_withdraw_amount) as staff_withdraw_amount,'.
                'sum(coupon_amount) as coupon_amount,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,'.
                'sum(wager_amount) as wager_amount,count(DISTINCT (IF (wager_amount>0,user_id,null))) as wager_user,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,sum(rebate_count) as rebate_count,'.
                'sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,'.
                'count(deposit_bank_amount>0 or null) as deposit_bank_user,'.
                'count(subsidy_amount>0 or null) as subsidy_user,sum(brokerage_amount) as brokerage_amount,count(brokerage_amount>0 or null) as brokerage_user '.
                'FROM daily_user WHERE daily BETWEEN :start_day AND :end_day group by major_id,major_name,minor_id,minor_name,agent_id,agent_name';

            $allrowss = [];
            $generator = iterator_to_array($mysqlReport->query($sql, [':start_day' => $first_day, ':end_day' => $last_day]));
            if (!empty($generator)) {
                foreach ($generator as $key => $val) {
                    $user_sql = 'select user_id from user_cumulate where agent_id=:agent_id';
                    $user_all = $mysqlReport->execute($user_sql, [':agent_id' => $val['agent_id']]);
                    //今日注册人数
                    $user_register_sql = 'select user_id from user_cumulate where agent_id=:agent_id and register_time between :start_time and :end_time';
                    $user_register = $mysqlReport->execute($user_register_sql, [':agent_id' => $val['agent_id'], ':start_time' => $month_start_time, ':end_time' => $month_end_time]);
                    $row = ['daily' => $daily, 'major_id' => $val['major_id'], 'major_name' => $val['major_name'],
                        'minor_id' => $val['minor_id'], 'minor_name' => $val['minor_name'], 'agent_id' => $val['agent_id'],
                        'agent_name' => $val['agent_name'], 'user_all' => $user_all, 'user_active' => $val['user_active'],
                        'user_first_deposit' => $val['user_first_deposit'], 'amount_first_deposit' => $val['amount_first_deposit'],
                        'deposit_count' => $val['deposit_count'], 'deposit_user' => $val['deposit_user'],
                        'deposit_amount' => $val['deposit_amount'], 'deposit_bank_count' => $val['deposit_bank_count'],
                        'deposit_bank_user' => $val['deposit_bank_user'], 'deposit_bank_amount' => $val['deposit_bank_amount'],
                        'deposit_weixin_count' => $val['deposit_weixin_count'], 'deposit_weixin_user' => $val['deposit_weixin_user'],
                        'deposit_weixin_amount' => $val['deposit_weixin_amount'], 'withdraw_user' => $val['withdraw_user'],
                        'deposit_alipay_count' => $val['deposit_alipay_count'], 'deposit_alipay_user' => $val['deposit_alipay_user'],
                        'deposit_alipay_amount' => $val['deposit_alipay_amount'], 'bank_deposit_count' => $val['bank_deposit_count'],
                        'bank_deposit_user' => $val['bank_deposit_user'], 'bank_deposit_amount' => $val['bank_deposit_amount'],
                        'staff_deposit_count' => $val['staff_deposit_count'], 'staff_deposit_user' => $val['staff_deposit_user'],
                        'staff_deposit_amount' => $val['staff_deposit_amount'], 'withdraw_count' => $val['withdraw_count'],
                        'staff_withdraw_count' => $val['staff_withdraw_count'], 'staff_withdraw_user' => $val['staff_withdraw_user'],
                        'staff_withdraw_amount' => $val['staff_withdraw_amount'], 'coupon_user' => $val['coupon_user'],
                        'coupon_amount' => $val['coupon_amount'], 'bet_count' => $val['bet_count'], 'bet_user' => $val['bet_user'],
                        'bet_amount' => $val['bet_amount'], 'wager_count' => $val['wager_count'], 'wager_user' => $val['wager_user'],
                        'wager_amount' => $val['wager_amount'], 'bonus_count' => $val['bonus_count'], 'bonus_user' => $val['bonus_user'],
                        'bonus_amount' => $val['bonus_amount'], 'rebate_count' => $val['rebate_count'],
                        'rebate_user' => $val['rebate_user'], 'rebate_amount' => $val['rebate_amount'],
                        'subsidy_user' => $val['subsidy_user'], 'subsidy_amount' => $val['subsidy_amount'],
                        'profit_amount' => $val['wager_amount'] - $val['rebate_amount'] - $val['bonus_amount'],
                        'user_register' => $user_register,
                        'withdraw_amount' => $val['withdraw_amount'],
                        'brokerage_user' => $val['brokerage_user'], 'brokerage_amount' => !empty($val['brokerage_amount']) ? $val['brokerage_amount'] : 0,
                    ];

                    $allrowss[] = $row;
                }
            }

            if (!empty($generator)) {
                $mysqlReport->monthly_staff->load($allrowss, [
                    'monthly' => $monthly,
                ], 'replace');
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/Status', ['time' => $time], time(), 8);
            $adapter->plan('NotifyPlat', ['path' => 'Analysis/Site', 'data' => ['time' => $time]], time(), 6);
        }
    }
}
