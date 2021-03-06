<?php

namespace Site\Task\Lottery;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * Lottery.php
 * @description   站点彩票数据存缓存并发送至app
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/Lottery 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class Lottery implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $mysqlPublic = $config->data_public;
        $mysqlStaff = $config->data_staff;
        $cache = $config->cache_site;
        $adapter = $context->getAdapter();
        //彩票
        $sql = 'select game_key,game_name'.' from lottery_game';
        foreach ($mysqlPublic->query($sql) as $rowss) {
            $cache->hset('LotteryName', $rowss['game_key'], $rowss['game_name']);
        }
        $all_lottery = [];
        $gameList = [];
        //彩种
        $sql = 'select l.game_key as game_key,l.model_key as model_key from lottery_game l left join suggest s on l.game_key = s.game_key where acceptable =1 order by s.display_order';
        foreach ($mysqlStaff->query($sql) as $rows) {
            $game_sql = 'select game_name from lottery_game where game_key=:game_key';
            foreach ($mysqlPublic->query($game_sql, [':game_key' => $rows['game_key']]) as $game) {
                $game_list = [
                    'game_key' => $rows['game_key'], 'game_name' => $game['game_name'], 'category_key' => 'lottery',
                ];
                $all_lottery[] = [
                    'game_key' => $rows['game_key'], 'game_name' => $game['game_name'], 'category_key' => 'lottery',
                ];
                $gameList[] = [
                    'game_key' => $rows['game_key'], 'game_name' => $game['game_name'], 'model_key' => $rows['model_key'],
                ];
                $json = json_encode($game_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $cache->hset('GameName', $rows['game_key'], $json);
            }
        }
        $adapter->plan('NotifyApp', ['path' => 'Lottery/Game', 'data' => ['pushData' => $gameList]], time(), 3);

        //三方彩种
        $sql = 'select e.game_key as game_key from external_game e left join suggest s on e.game_key = s.game_key where acceptable = 1 order by s.display_order';
        foreach ($mysqlStaff->query($sql) as $external) {
            $sql = 'select game_name,category_key,game_key from external_game where game_key=:game_key';
            foreach ($mysqlPublic->query($sql, [':game_key' => $external['game_key']]) as $rows) {
                $game_list = [
                    'game_key' => $rows['game_key'], 'game_name' => $rows['game_name'], 'category_key' => $rows['category_key'],
                ];
                $cache->hset('LotteryName', $rows['game_key'], $rows['game_name']);
                $json = json_encode($game_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $cache->hset('GameName', $rows['game_key'], $json);
                $all_lottery[] = [
                    'game_key' => $rows['game_key'], 'game_name' => $rows['game_name'], 'category_key' => $rows['category_key'],
                ];
            }
        }
        $cache->hset('AllLottery', 'AllGame', json_encode($all_lottery));
        $cache->hset('AllLottery', 'LotteryGame', json_encode($gameList));
    }
}
