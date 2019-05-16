<?php

/**
 * Layer_subsidy.php.
 *
 * @description   代理会员返水派发任务
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

class Layer_subsidy implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        ['time' => $time] = $context->getData();

        try {
            $daily = intval(date('Ymd', $time));
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

            $cache = $config->cache_site;
            $layer_list = json_decode($cache->hget('LayerList', 'allLayer'));
            $layerTranslation = [];
            foreach ($layer_list as $layer) {
                $layerTranslation += [$layer->layer_id => $layer->layer_name];
            }

            $sql = 'select daily,layer_id,sum(bet_amount) as bet_amount,game_key,sum(subsidy) as subsidy from daily_user_game_subsidy where daily = :daily GROUP BY layer_id,game_key';
            $param = [':daily' => $daily];
            $subsidy_info = [];
            $lastResult = [];

            // 如果当前层级已经派发过，则该层级直接跳过
            $reportGame = iterator_to_array($mysqlReport->query($sql, $param));
            $sqlLayer = 'Select `layer_id`,`layer_name`,`auto_deliver`,`deliver_staff_id`,`deliver_staff_name`,`deliver_launch_time`,`deliver_finish_time` From `daily_layer_subsidy` Where `daily` = :daily';
            $param = [
                ':daily' => $daily,
            ];
            $arrayLayer = [];
            foreach ($mysqlReport->query($sqlLayer, $param) as $v) {
                $arrayLayer[$v['layer_id']] = $v['deliver_finish_time'];
                $layerOlder[] = $v;
            }

            foreach ($reportGame as $k => $v) {
                if (isset($arrayLayer[$v['layer_id']]) && $arrayLayer[$v['layer_id']] > 0) {
                    continue;
                }
                $video_subsidy = 0;
                $video_bet_amount = 0;
                $game_subsidy = 0;
                $game_bet_amount = 0;
                $sports_subsidy = 0;
                $sports_bet_amount = 0;
                $cards_subsidy = 0;
                $cards_bet_amount = 0;
                $lottery_subsidy = 0;
                $lottery_bet_amount = 0;

                if (strpos($v['game_key'], 'ag') === 0) {
                    $video_subsidy = $v['subsidy'];
                    $video_bet_amount = $v['bet_amount'];
                } elseif (strpos($v['game_key'], 'fg') === 0) {
                    $game_subsidy = $v['subsidy'];
                    $game_bet_amount = $v['bet_amount'];
                } elseif (strpos($v['game_key'], 'lb') === 0) {
                    $sports_subsidy = $v['subsidy'];
                    $sports_bet_amount = $v['bet_amount'];
                } elseif (strpos($v['game_key'], 'ky') === 0) {
                    $cards_subsidy = $v['subsidy'];
                    $cards_bet_amount = $v['bet_amount'];
                } else {
                    $lottery_subsidy = $v['subsidy'];
                    $lottery_bet_amount = $v['bet_amount'];
                }

                $sum_bet_amount = $video_bet_amount + $lottery_bet_amount + $cards_bet_amount + $sports_bet_amount + $game_bet_amount;
                $sum_subsidy = $video_subsidy + $lottery_subsidy + $cards_subsidy + $sports_subsidy + $game_subsidy;

                if (!empty($subsidy_info[$v['daily'].'-'.$v['layer_id']])) {
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_all'] += $sum_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_all'] += $sum_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_lottery'] += $lottery_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_lottery'] += $lottery_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_video'] += $video_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_video'] += $video_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_game'] += $game_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_game'] += $game_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_sports'] += $sports_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_sports'] += $sports_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_cards'] += $cards_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_cards'] += $cards_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['auto_deliver'] = 1;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_staff_id'] = 0;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_staff_name'] = '';
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_launch_time'] = 0;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_finish_time'] = 0;
                } else {
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['daily'] = $v['daily'];
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['layer_id'] = $v['layer_id'];
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['layer_name'] = $layerTranslation[$v['layer_id']];
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_all'] = $sum_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_all'] = $sum_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_lottery'] = $lottery_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_lottery'] = $lottery_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_video'] = $video_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_video'] = $video_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_game'] = $game_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_game'] = $game_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_sports'] = $sports_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_sports'] = $sports_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['bet_cards'] = $cards_bet_amount;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['subsidy_cards'] = $cards_subsidy;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['auto_deliver'] = 1;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_staff_id'] = 0;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_staff_name'] = '';
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_launch_time'] = 0;
                    $subsidy_info[$v['daily'].'-'.$v['layer_id']]['deliver_finish_time'] = 0;
                }
            }

            if(!empty($subsidy_info)) {
                $lastResult = array_values($subsidy_info);
            }
            // 如果该层级用户变成空则将该层级数据清空
            $nowLayer = array_column($lastResult, 'layer_id');
            if (!empty($layerOlder)) {
                foreach ($layerOlder as $v) {
                    if (!in_array($v['layer_id'], $nowLayer) && $v['deliver_finish_time'] <= 0) {
                        $lastResult[] = [
                            'daily' => $daily,
                            'layer_id' => $v['layer_id'],
                            'layer_name' => $v['layer_name'],
                            'bet_all' => 0,
                            'subsidy_all' => 0,
                            'bet_lottery' => 0,
                            'subsidy_lottery' => 0,
                            'bet_video' => 0,
                            'subsidy_video' => 0,
                            'bet_game' => 0,
                            'subsidy_game' => 0,
                            'bet_sports' => 0,
                            'subsidy_sports' => 0,
                            'bet_cards' => 0,
                            'subsidy_cards' => 0,
                            'auto_deliver' => 1,
                            'deliver_staff_id' => $v['deliver_staff_id'],
                            'deliver_staff_name' => $v['deliver_staff_name'],
                            'deliver_launch_time' => $v['deliver_launch_time'],
                            'deliver_finish_time' => $v['deliver_finish_time'],
                        ];
                    }
                }
            }

            $mysqlReport->daily_layer_subsidy->load($lastResult, [], 'replace');
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/UserBrokerage', ['time' => $time], time(), 9);
        }
    }
}
