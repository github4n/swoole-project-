<?php
/**
 * UserExterna.php.
 *
 * @description   用户外接口日报插入数据任务
 * @Author  nathan
 * @date  2019-04-07
 * @links  Initialize.php
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */

namespace Site\Task\Report;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class UserExternal implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        try {
            ['time' => $time] = $context->getData();
            $daily = intval(date('Ymd', $time));
            $start_time = strtotime('today', $time);
            $end_time = $start_time + 86400 - 1;

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

            $data = [];
            foreach ($config->deal_list as $deal) {
                $mysql = $config->__get('data_'.$deal);
                $sql = 'select user_id,account_name,user_key,layer_id,game_key,external_type,count(audit_serial) as bet_count,sum(audit_amount) as bet_amount from external_audit where play_time between :start_time and :end_time group by user_id,account_name,user_key,layer_id,game_key,external_type';
                foreach ($mysql->query($sql, [':start_time' => $start_time, ':end_time' => $end_time]) as $value) {
                    $game_key = $value['game_key'];
                    $mysqlPublic = $config->data_public;
                    $game_info_sql = 'select category_key,game_name from external_game where game_key = :game_key';
                    $category_key = '';
                    $interface_key = $value['external_type'];
                    $game_name = '';
                    foreach ($mysqlPublic->query($game_info_sql, [':game_key' => $value['game_key']]) as $val) {
                        $category_key = $val['category_key'];
                        $game_name = $val['game_name'];
                    }
                    $user_id = $value['user_id'];
                    $user_info_sql = 'select layer_name,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,'
                        .'broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,'
                        .'broker_3_name from user_cumulate where user_id = :user_id';
                    $userInfo = [];
                    foreach ($mysqlReport->query($user_info_sql, [':user_id' => $value['user_id']]) as $v) {
                        $userInfo += [
                            'layer_name' => $v['layer_name'],
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
                        ];
                    }
                    //反水
                    $subsidy_sql = 'select sum(subsidy) as subsidy from daily_user_game_subsidy where daily = :daily and game_key=:game_key and user_id = :user_id';
                    $subsidy = 0;
                    foreach ($mysqlReport->query($subsidy_sql, [':user_id' => $value['user_id'], ':daily' => $daily, ':game_key' => $value['game_key']]) as $val) {
                        $subsidy = !empty($val['subsidy']) ? $val['subsidy'] : 0;
                    }
                    $bonus_count = 0;
                    $bonus_amount = 0;
                    $win_sql = 'select external_data from external_audit where user_id = :user_id and game_key = :game_key and play_time between :start_time and :end_time';
                    switch ($interface_key) {
                        case 'fg':
                            foreach ($mysql->query($win_sql, [':user_id' => $value['user_id'], ':start_time' => $start_time, ':game_key' => $value['game_key'], ':end_time' => $end_time]) as $fgData) {
                                $fg_data = json_decode($fgData['external_data'], true);
                                //其它类型
                                if (isset($fg_data['all_wins']) && $fg_data['all_wins'] > 0) {
                                    $bonus_amount += $fg_data['all_wins'];
                                    ++$bonus_count;
                                }
                                //捕鱼
                                if (isset($fg_data['fish_dead_chips']) && $fg_data['fish_dead_chips'] > 0) {
                                    $bonus_amount += $fg_data['fish_dead_chips'];
                                    ++$bonus_count;
                                }
                            }
                            break;
                        case 'ky':
                            foreach ($mysql->query($win_sql, [':user_id' => $value['user_id'], ':start_time' => $start_time, ':game_key' => $value['game_key'], ':end_time' => $end_time]) as $kyData) {
                                $ky_data = json_decode($kyData['external_data'], true);
                                if (isset($ky_data['Profit']) && isset($ky_data['CellScore'])) {
                                    $bonus_amount += $ky_data['Profit'] + $ky_data['CellScore'];
                                    ++$bonus_count;
                                }
                            }
                            break;
                        case 'lb':
                            break;
                        case 'ag':
                            break;
                        default:
                            $bonus_count = 0;
                            $bonus_amount = 0;
                            break;
                    }

                    //三方损益
                    $profit_amount = $bonus_amount - $value['bet_amount'];
                    if (!empty($userInfo)) {
                        $tag = [
                            'daily' => $daily,
                            'user_id' => $user_id,
                            'user_key' => $value['user_key'],
                            'user_name' => $value['account_name'],
                            'layer_id' => $value['layer_id'],
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
                            'category_key' => $category_key,
                            'interface_key' => $interface_key,
                            'game_key' => $game_key,
                            'game_name' => $game_name,
                            'bet_count' => $value['bet_count'],
                            'bet_amount' => $value['bet_amount'],
                            'wager_count' => $value['bet_count'],
                            'wager_amount' => $value['bet_amount'],
                            'bonus_count' => $bonus_count,
                            'bonus_amount' => $bonus_amount,
                            'subsidy_amount' => $subsidy,
                            'profit_amount' => $profit_amount,
                        ];
                        $data[] = $tag;
                    }
                }
            }

            if (!empty($data)) {
                $mysqlReport->daily_user_external->load($data, [], 'replace');
            }
            $user_data = [];
            $mysqlUser = $config->data_user;
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
            $weekly_sql = 'select user_id,user_key,0 as user_name,0 as layer_id,0 as layer_name,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name,category_key,interface_key,game_key,game_name,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,sum(subsidy_amount) as subsidy_amount,sum(profit_amount) as profit_amount from daily_user_external where daily between :first_day and :last_day group by user_id,user_key,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name,category_key,interface_key,game_key,game_name';
            $weekly_list = iterator_to_array($mysqlReport->query($weekly_sql, [':first_day' => $first_day, ':last_day' => $last_day]));
            $weekly_data = [];
            if (!empty($weekly_list[0]['user_id'])) {
                foreach ($weekly_list as $v) {
                    $weekly_data[] = [
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
                        'category_key' => $v['category_key'],
                        'interface_key' => $v['interface_key'],
                        'game_key' => $v['game_key'],
                        'game_name' => $v['game_name'],
                        'bet_count' => empty($v['bet_count']) ? 0 : $v['bet_count'],
                        'bet_amount' => empty($v['bet_amount']) ? 0 : $v['bet_amount'],
                        'wager_count' => empty($v['wager_count']) ? 0 : $v['wager_count'],
                        'wager_amount' => empty($v['wager_amount']) ? 0 : $v['wager_amount'],
                        'bonus_count' => empty($v['bonus_count']) ? 0 : $v['bonus_count'],
                        'bonus_amount' => empty($v['bonus_amount']) ? 0 : $v['bonus_amount'],
                        'subsidy_amount' => empty($v['subsidy_amount']) ? 0 : $v['subsidy_amount'],
                        'profit_amount' => empty($v['profit_amount']) ? 0 : $v['profit_amount'],
                    ];
                }
            }
            if (!empty($weekly_data)) {
                $mysqlReport->weekly_user_external->load($weekly_data, [
                    'weekly' => $weekly,
                ], 'replace');
            }

            //月报
            $monthly = intval(date('Ym', $time));
            $first_day = date('Ym01', $time);
            $last_day = date('Ymd', strtotime(date('Y-m-01', $time).' +1 month -1 day'));
            $monthly_sql = 'select user_id,user_key,0 as user_name,0 as layer_id,0 as layer_name,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name,category_key,interface_key,game_key,game_name,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,sum(subsidy_amount) as subsidy_amount,sum(profit_amount) as profit_amount from daily_user_external where daily between :first_day and :last_day group by user_id,user_key,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name,category_key,interface_key,game_key,game_name';
            $monthly_list = iterator_to_array($mysqlReport->query($monthly_sql, [':first_day' => $first_day, ':last_day' => $last_day]));
            $monthly_data = [];
            if (!empty($monthly_list[0]['user_id'])) {
                foreach ($monthly_list as $val) {
                    $monthly_data[] = [
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
                        'category_key' => $val['category_key'],
                        'interface_key' => $val['interface_key'],
                        'game_key' => $val['game_key'],
                        'game_name' => $val['game_name'],
                        'bet_count' => empty($val['bet_count']) ? 0 : $val['bet_count'],
                        'bet_amount' => empty($val['bet_amount']) ? 0 : $val['bet_amount'],
                        'wager_count' => empty($val['wager_count']) ? 0 : $val['wager_count'],
                        'wager_amount' => empty($val['wager_amount']) ? 0 : $val['wager_amount'],
                        'bonus_count' => empty($val['bonus_count']) ? 0 : $val['bonus_count'],
                        'bonus_amount' => empty($val['bonus_amount']) ? 0 : $val['bonus_amount'],
                        'subsidy_amount' => empty($val['subsidy_amount']) ? 0 : $val['subsidy_amount'],
                        'profit_amount' => empty($val['profit_amount']) ? 0 : $val['profit_amount'],
                    ];
                }
            }
            if (!empty($monthly_data)) {
                $mysqlReport->monthly_user_external->load($monthly_data, [
                    'monthly' => $monthly,
                ], 'replace');
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/StaffExternal', ['time' => $time], time(), 9);
        }
    }
}
