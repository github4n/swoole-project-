<?php

namespace Site\Task\Lottery;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * GameWin.php
 * @description   彩票赔率推送任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/GameWin 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 * @param game_key|彩票key
 */
class GameWin implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $cache = $config->cache_site;
        ['game_key' => $game_key] = $context->getData();
        $adapter = $context->getAdapter();
        $mysql = $config->data_staff;
        $mysqlPublic = $config->data_public;
        $pushData = [];
        $rebate = null;

        $sql = 'select play_key from lottery_game_play where game_key=:game_key AND acceptable=1 ';
        $play_list = iterator_to_array($mysql->query($sql, [':game_key' => $game_key]));
        if (!empty($play_list)) {
            $rebate_sql = 'SELECT rebate_max FROM lottery_game WHERE game_key=:game_key AND acceptable=1';
            $rebate_param = [':game_key' => $game_key];
            foreach ($mysql->query($rebate_sql, $rebate_param) as $item) {
                $rebate = $item['rebate_max'];
            }
            foreach ($play_list as $key => $val) {
                $sql = 'SELECT win_key,bonus_rate FROM lottery_game_win WHERE game_key=:game_key AND play_key=:play_key';
                foreach ($mysql->query($sql, [':game_key' => $game_key, ':play_key' => $val['play_key']]) as $row) {
                    $sql1 = 'SELECT win_rate,decimal_place FROM lottery_win WHERE win_key=:win_key';
                    foreach ($mysqlPublic->query($sql1, [':win_key' => $row['win_key']]) as $item) {
                        $decimal = $item['decimal_place'];
                        $len = intval(str_pad(1, $decimal + 2, 0));
                        $tmp = ceil(($item['win_rate'] / 1000) * $len);
                        $list = [
                            'play_key' => $val['play_key'],
                            'win_key' => $row['win_key'],
                            'win_name' => $cache->hget('WinList', $val['play_key'].'-'.$row['win_key']),
                            'bonus_rate' => $row['bonus_rate'],
                            'step' => $tmp / $len,
                            'decimal_place' => $item['decimal_place'],
                            'rebate_max' => $rebate,
                        ];
                        $pushData[] = $list;
                    }
                }
            }
            $adapter->plan('NotifyApp', ['path' => 'Lottery/GameWin', 'data' => ['game_key' => $game_key, 'pushData' => $pushData]]);
        }
    }
}
