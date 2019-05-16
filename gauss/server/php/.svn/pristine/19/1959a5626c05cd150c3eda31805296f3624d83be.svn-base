<?php
/**
 * LotteryPeriod.php
 *
 * @description   彩票期数据插入数据任务
 * @Author  nathan
 * @date  2019-04-07
 * @links  Initialize.php
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */

namespace Site\Task\Report;
use Lib\Task\Context;
use Lib\Config;
use Lib\Task\IHandler;

class LotteryPeriod implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $param = $context->getData();
        $game_key = isset($param['game_key']) ? $param['game_key'] : '';
        $period = isset($param['period']) ? $param['period'] : '';
        $open_time = isset($param['open_time']) ? $param['open_time'] : '';
        $mysqlReport = $config->data_report;

        $sql = "select count(DISTINCT user_id) as user_count,count(bet>0 or null) as bet_launch_count,".
            "sum(bet) as bet_launch_amount,sum(bet_launch) as bet_amount,sum(rebate) as rebate_amount,".
            "sum(bonus) as bonus_amount,count(bet_serial) as bet_count from bet_unit_intact".
            " where game_key = :game_key and period = :period";

        $user_count = 0;
        $bet_launch_count = 0;
        $bet_launch_amount = 0;
        $bet_count = 0;
        $bet_amount = 0;
        $bonus_amount = 0;
        $rebate_amount = 0;

        if ($period && $game_key && $open_time) {
            foreach ($config->deal_list as $deal) {
                $mysql = $config->__get('data_' . $deal);
                foreach ($mysql->query($sql,[':game_key'=>$game_key,':period'=>$period]) as $rows) {
                    $user_count += $rows['user_count'];
                    $bet_launch_count += $rows['bet_launch_count'];
                    $bet_launch_amount += $rows['bet_launch_amount'];
                    $bet_count += $rows['bet_count'];
                    $bet_amount += $rows['bet_amount'];
                    $bonus_amount += $rows['bonus_amount'];
                    $rebate_amount += $rows['rebate_amount'];
                }

            }
            $row = [
                'game_key' => $game_key,
                'period' => $period,
                'open_time' => $open_time,
                'user_count' => $user_count,
                "wager_count" => $bet_launch_count,
                "wager_amount" => $bet_launch_amount,
                "bet_count" => $bet_count,
                "bet_amount" => $bet_amount,
                "rebate_amount" => $rebate_amount,
                "bonus_amount" => $bonus_amount,
                "profit_amount" => $bet_launch_amount - $rebate_amount - $bonus_amount
            ];
            $data[] = $row;
            $mysqlReport->lottery_period->load($data, [], 'replace');
        }

    }
}