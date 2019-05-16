<?php

/**
 * UserLottery.php.
 *
 * @description   用户彩票日报插入数据任务
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

class UserLottery implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        // TODO: Implement onTask() method.

        $adapter = $context->getAdapter();
        ['time' => $time] = $context->getData();
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

            $data = array();
            foreach ($config->deal_list as $deal) {
                $mysql = $config->__get('data_'.$deal);
                $sql = 'select game_key,user_id,count(bet_serial) as bet_count,sum(bet_launch) as bet_amount,sum(bet>0 or null) as wager_count,sum(bet) as wager_amount,count(bonus > 0 or null) as bonus_count,sum(bonus) as bonus_amount,count(rebate > 0 or null) as rebate_count,sum(rebate) as rebate_amount,sum(revert) as revert_amount from bet_unit_intact where settle_time between :start_time and :end_time group by user_id,game_key';
                foreach ($mysql->query($sql, [':start_time' => $start_time, ':end_time' => $end_time]) as $row) {
                    //截取game_key查询彩票相关信息
                    $lotteryMysql = $config->data_public;
                    $lotterySql = 'select model_name,model_key,game_key,game_name from lottery_game_intact where game_key = :game_key';
                    $lotteryInfo = [];
                    foreach ($lotteryMysql->query($lotterySql, [':game_key' => $row['game_key']]) as $v) {
                        $lotteryInfo = $v;
                    }

                    //根据user_id查询体系相关信息
                    $userInfoSql = 'select user_key,user_name,layer_id,layer_name,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name from user_cumulate where user_id = :user_id';
                    $userInfo = [];
                    foreach ($mysqlReport->query($userInfoSql, [':user_id' => $row['user_id']]) as $info) {
                        $userInfo = $info;
                    }
                    $subsidyData = [];
                    $user_subsidy_sql = 'select user_id, deliver_time from daily_user_subsidy where daily=:daily';
                    foreach ($mysqlReport->query($user_subsidy_sql, [':daily' => $daily]) as $val) {
                        $subsidyData += [
                            $val['user_id'] => $val['deliver_time'],
                        ];
                    }

                    $subsidy_sql = 'select sum(subsidy) as subsidy from daily_user_game_subsidy where daily = :daily and game_key=:game_key and user_id = :user_id';
                    $subsidy_list = iterator_to_array($mysqlReport->query($subsidy_sql, [':daily' => $daily, ':game_key' => $row['game_key'], ':user_id' => $row['user_id']]));
                    $subsidy = 0;
                    if (!empty($subsidy_list[0]['subsidy'])) {
                        $subsidy += $subsidy_list[0]['subsidy'];
                    }
                    if (!empty($subsidyData[$row['user_id']]) && $subsidyData['deliver_time'] == 0 || empty($subsidyData[$row['user_id']])) {
                        $subsidy = 0; //派发了才算反水
                    }

                    //计算损益规则,返奖+反水+返点+退款-有效投注
                    $profitLoss = $row['bonus_amount'] + $row['rebate_amount'] + $row['revert_amount'] + $subsidy - $row['bet_amount']; //真实投注额bet_unit_intact存在错误，暂时以　退款－投注额来取实际投注额
                    if (!empty($userInfo)) {
                        $tag = [
                            'daily' => $daily,
                            'user_id' => $row['user_id'],
                            'user_key' => $userInfo['user_key'],
                            'user_name' => $userInfo['user_name'],
                            'layer_id' => $userInfo['layer_id'],
                            'layer_name' => $userInfo['layer_name'],
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
                            'model_key' => $lotteryInfo['model_key'],
                            'model_name' => $lotteryInfo['model_name'],
                            'game_key' => $row['game_key'],
                            'game_name' => $lotteryInfo['game_name'],
                            'bet_count' => $row['bet_count'],
                            'bet_amount' => $row['bet_amount'],
                            'wager_count' => empty($row['wager_count']) ? 0 : $row['wager_count'],
                            'wager_amount' => $row['wager_amount'],
                            'bonus_count' => $row['bonus_count'],
                            'bonus_amount' => $row['bonus_amount'],
                            'rebate_count' => $row['rebate_count'],
                            'rebate_amount' => $row['rebate_amount'],
                            'subsidy_amount' => $subsidy,
                            'profit_amount' => substr(sprintf('%.3f', $profitLoss), 0, -1),
                        ];
                        $data[] = $tag;
                    }
                }
            }

            $mysqlReport->daily_user_lottery->load($data, [], 'replace');

            $user_data = [];
            $user_sql = 'select user_id,account_name as user_name,layer_id,layer_name from user_info_intact';
            foreach ($mysqlUser->query($user_sql) as $user_detail) {
                $user_data += [$user_detail['user_id'] => [
                    'user_name' => $user_detail['user_name'],
                    'layer_id' => $user_detail['layer_id'],
                    'layer_name' => $user_detail['layer_name'],
                ]];
            }
            //周报
            $weekly = intval(date('oW', $time));
            $first_day = date('Ymd',strtotime('this week',$time));
            $last_day = date('Ymd',strtotime($first_day) + 7 * 86400 -1);
            $weekly_sql = 'select user_id,0 as user_name,user_key,0 as layer_id,0 as layer_name,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name,model_key,model_name,game_key,game_name,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,sum(rebate_count) as rebate_count,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,sum(profit_amount) as profit_amount from daily_user_lottery where daily between :first_day and :last_day group by user_id,user_key,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key, broker_2_id ,broker_2_key ,broker_3_id,broker_3_key,broker_1_name,broker_2_name,broker_3_name,model_key,model_name,game_key,game_name';
            $weekly_list = iterator_to_array($mysqlReport->query($weekly_sql, [':first_day' => $first_day, ':last_day' => $last_day]));
            $weekly_data = [];
            if (!empty($weekly_list[0]['user_id'])) {
                foreach ($weekly_list as $v) {
                    $weekly_data[] = [
                        'weekly' => $weekly,
                        'user_id' => $v['user_id'],
                        'user_key' => $v['user_key'],
                        'user_name' => !empty($user_data[$v['user_id']]['user_name']) ? $user_data[$v['user_id']]['user_name'] : 0,
                        'layer_id' => $user_data[$v['user_id']]['layer_id'],
                        'layer_name' => $user_data[$v['user_id']]['layer_name'],
                        'major_id' => $v['major_id'],
                        'major_name' => $v['major_name'],
                        'minor_id' => $v['minor_id'],
                        'minor_name' => $v['minor_name'],
                        'agent_id' => $v['agent_id'],
                        'agent_name' => $v['agent_name'],
                        'broker_1_id' => $v['broker_1_id'],
                        'broker_1_key' => $v['broker_1_key'],
                        'broker_1_name' => $v['broker_1_name'],
                        'broker_2_id' => $v['broker_2_id'],
                        'broker_2_key' => $v['broker_2_key'],
                        'broker_2_name' => $v['broker_2_name'],
                        'broker_3_id' => $v['broker_3_id'],
                        'broker_3_key' => $v['broker_3_key'],
                        'broker_3_name' => $v['broker_3_name'],
                        'model_key' => $v['model_key'],
                        'model_name' => $v['model_name'],
                        'game_key' => $v['game_key'],
                        'game_name' => $v['game_name'],
                        'bet_count' => empty($v['bet_count']) ? 0 : $v['bet_count'],
                        'bet_amount' => empty($v['bet_amount']) ? 0 : $v['bet_amount'],
                        'wager_count' => empty($v['wager_count']) ? 0 : $v['wager_count'],
                        'wager_amount' => empty($v['wager_amount']) ? 0 : $v['wager_amount'],
                        'bonus_count' => empty($v['bonus_count']) ? 0 : $v['bonus_count'],
                        'bonus_amount' => empty($v['bonus_amount']) ? 0 : $v['bonus_amount'],
                        'rebate_count' => empty($v['rebate_count']) ? 0 : $v['rebate_count'],
                        'rebate_amount' => empty($v['rebate_amount']) ? 0 : $v['rebate_amount'],
                        'subsidy_amount' => empty($v['subsidy_amount']) ? 0 : $v['subsidy_amount'],
                        'profit_amount' => empty($v['profit_amount']) ? 0 : $v['profit_amount'],
                    ];
                }
            }
            if (!empty($weekly_data)) {
                $mysqlReport->weekly_user_lottery->load($weekly_data, [], 'replace');
            }

            //月报
            $monthly = intval(date('Ym', $time));
            $first_day = date('Ym01', $time);
            $last_day = date('Ymd', strtotime(date('Y-m-01', $time).' +1 month -1 day'));
            $monthly_sql = 'select user_id,0 as user_name,user_key,0 as layer_id,0 as layer_name,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name,model_key,model_name,game_key,game_name,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,sum(rebate_count) as rebate_count,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,sum(profit_amount) as profit_amount from daily_user_lottery where daily between :first_day and :last_day group by user_id,user_key,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key, broker_2_id ,broker_2_key ,broker_3_id,broker_3_key,broker_1_name,broker_2_name,broker_3_name,model_key,model_name,game_key,game_name';
            $monthly_list = iterator_to_array($mysqlReport->query($monthly_sql, [':first_day' => $first_day, ':last_day' => $last_day]));
            $monthly_data = [];
            if (!empty($monthly_list[0]['user_id'])) {
                foreach ($monthly_list as $val) {
                    $monthly_data[] = [
                        'monthly' => $monthly,
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
                        'broker_2_id' => $val['broker_2_id'],
                        'broker_2_key' => $val['broker_2_key'],
                        'broker_2_name' => $val['broker_2_name'],
                        'broker_3_id' => $val['broker_3_id'],
                        'broker_3_key' => $val['broker_3_key'],
                        'broker_3_name' => $val['broker_3_name'],
                        'model_key' => $val['model_key'],
                        'model_name' => $val['model_name'],
                        'game_key' => $val['game_key'],
                        'game_name' => $val['game_name'],
                        'bet_count' => empty($val['bet_count']) ? 0 : $val['bet_count'],
                        'bet_amount' => empty($val['bet_amount']) ? 0 : $val['bet_amount'],
                        'wager_count' => empty($val['wager_count']) ? 0 : $val['wager_count'],
                        'wager_amount' => empty($val['wager_amount']) ? 0 : $val['wager_amount'],
                        'bonus_count' => empty($val['bonus_count']) ? 0 : $val['bonus_count'],
                        'bonus_amount' => empty($val['bonus_amount']) ? 0 : $val['bonus_amount'],
                        'rebate_count' => empty($val['rebate_count']) ? 0 : $val['rebate_count'],
                        'rebate_amount' => empty($val['rebate_amount']) ? 0 : $val['rebate_amount'],
                        'subsidy_amount' => empty($val['subsidy_amount']) ? 0 : $val['subsidy_amount'],
                        'profit_amount' => empty($val['profit_amount']) ? 0 : $val['profit_amount'],
                    ];
                }
            }
            if (!empty($monthly_data)) {
                $mysqlReport->monthly_user_lottery->load($monthly_data, [], 'replace');
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/UserExternal', ['time' => $time], time(), 9);
        }
    }
}
