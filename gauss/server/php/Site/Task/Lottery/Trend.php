<?php

namespace Site\Task\Lottery;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
use Lib\Calender;

/*
 * Trend.php
 * @description   走势图推送任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/Trend 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class Trend implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['game_key' => $game_key] = $context->getData();
        $adapter = $context->getAdapter();
        $mysql = $config->data_public;
        $mysqlStaff = $config->data_staff;
        $game_list = [];
        $sql = 'select game_key from lottery_game where game_key=:game_key and acceptable = 1';
        foreach ($mysqlStaff->query($sql, [':game_key' => $game_key]) as $rows) {
            $game_list = $rows;
        }
        if (!empty($game_list)) {
            $sql = 'select * from lottery_number_intact where game_key=:game_key order by period desc limit 120';
            $list = array();
            foreach ($mysql->query($sql, [':game_key' => $game_key]) as $row) {
                $pushData = [
                    'game_key' => $game_key,
                    'period' => $row['period'],
                    'open_time' => $row['open_time'],
                ];
                $zodiacList = Calender::getZodiacList($row['open_time']);
                $sx = isset($zodiacList[1]) ? $zodiacList[1] : '';
                if (in_array('six', explode('_', $game_key))) {
                    $pushData['sx'] = $sx;
                }
                for ($i = 1; $i <= 20; ++$i) {
                    $key = 'normal'.$i;
                    if (-1 != $row[$key]) {
                        $pushData[$key] = $row[$key];
                    }
                }
                for ($i = 1; $i <= 2; ++$i) {
                    $key = 'special'.$i;
                    if (-1 != $row[$key]) {
                        $pushData[$key] = $row[$key];
                    }
                }
                $list[] = $pushData;
            }

            $adapter->plan('NotifyApp', ['path' => 'Lottery/Trend', 'data' => ['game_key' => $game_key, 'pushData' => $list]]);
        }
    }
}
