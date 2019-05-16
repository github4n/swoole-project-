<?php
namespace Site\Task\Lottery;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * Settle.php
 * @description   结算设置任务
 * @Author  rose 
 * @date  2019-05-09
 * @links  Lottery/Settle 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class Settle implements IHandler
{
    private const BATCH_SIZE = 100;
    public function onTask(Context $context, Config $config)
    {
        ['deal_key' => $deal_key, 'game_key' => $game_key, 'period' => $period] = $context->getData();
        // 取 model_key
        $mysqlPublic = $config->data_public;
        $sql = 'select model_key from lottery_game where game_key=:game_key';
        foreach ($mysqlPublic->query($sql, ['game_key' => $game_key]) as $row) {
            $model_key = $row['model_key'];
        }
        if (empty($model_key)) {
            return;
        }

        $mysqlDeal = $config->__get('data_' . $deal_key);
        $taskAdapter = $context->getAdapter();

        // 插入分批结算任务
        $sql = 'select * from bet_no_settle where game_key=:game_key and period=:period';
        $params = ['game_key' => $game_key, 'period' => $period];
        $buffer = [];
        foreach ($mysqlDeal->query($sql, $params) as $row) {
            $buffer[] = $row;
            if (100 <= count($buffer)) {
                $taskAdapter->plan('Lottery/Settle/' . $model_key, [
                    'deal_key' => $deal_key, 'game_key' => $game_key, 'period' => $period, 'betList' => $buffer,
                ]);
                $buffer = [];
            }
        }
        if (0 < count($buffer)) {
            $taskAdapter->plan('Lottery/Settle/' . $model_key, [
                'deal_key' => $deal_key, 'game_key' => $game_key, 'period' => $period, 'betList' => $buffer,
            ]);
        }

    }
}
