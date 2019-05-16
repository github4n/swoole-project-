<?php

namespace Site\Task\Lottery;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * Step
 * @description   取赔率的步长存入redis彩票结算使用
 * @Author  nathan
 * @date  2019-05-08
 * @links  Lottery/Step
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-08
 */
class Step implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        //存Step
        $cache = $config->cache_site;
        $mysqlStaff = $config->data_staff;
        $mysqlPublic = $config->data_public;
        $sql = 'SELECT play_key,game_key FROM lottery_game_play WHERE acceptable=1';
        foreach ($mysqlStaff->query($sql) as $row) {
            $sqls = 'SELECT win_rate,decimal_place,win_key FROM lottery_win WHERE play_key=:play_key';
            foreach ($mysqlPublic->query($sqls, [':play_key' => $row['play_key']]) as $item) {
                $decimal = $item['decimal_place'];
                $len = intval(str_pad(1, $decimal + 2, 0));
                $tmp = ceil(($item['win_rate'] / 1000) * $len);
                $pushData['step'] = $tmp / $len;
                $cache->hset('Step', $row['game_key'].'-'.$item['win_key'], $pushData['step']);
            }
        }
    }
}
