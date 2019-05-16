<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-17
 * Time: 上午9:37
 */

namespace App\Task\Lottery;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;


class Record implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['game_list' => $game_list,"id"=>$id] = $context->getData();
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_app);
        $mysql = $config->data_public;
        $data = Array();
        foreach ($game_list as $value) {
            $param = ['game_key'=>$value['game_key']];
            $sql = 'select * from lottery_number_intact where game_key = :game_key order by period desc limit 1';
            $pushData = [];
            foreach ($mysql->query($sql,$param) as $row ) {
                $model_key = explode("_",$value['game_key']);
                $model_key = $model_key[0];
                $pushData = [
                    'model_key' => $model_key,
                    'game_key' => $value['game_key'],
                    'game_name' => $row['game_name'],
                    'period' => $row['period'],
                    'open_time' => $row['open_time'],
                ];

                for ($i = 1; $i <= 20; $i++) {
                    $key = 'normal' . $i;
                    if (-1 != $row[$key]) {
                        $pushData[$key] = $row[$key];
                    }
                }
                for ($i = 1; $i <= 2; $i++) {
                    $key = 'special' . $i;
                    if (-1 != $row[$key]) {
                        $pushData[$key] = $row[$key];
                    }
                }
            }

            $data[] = $pushData;

        }
       // $websocketAdapter->send($id,'Lottery/Record', $data);
    }
}