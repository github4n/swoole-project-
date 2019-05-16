<?php

namespace App\Websocket\BetRecord;

use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

// BetRecord/GetBetRate  {"bet_serial":"190329124658000004"}  投注单号
class GetBetRate extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $data = $context->getData();
        $cache = $config->cache_app;
        $public_mysql = $config->data_public;
        $bet_serial = !empty($data["bet_serial"]) ? $data["bet_serial"] : '';
        if (empty($bet_serial)) {
            $context->reply(['status' => 202, 'msg' => '请传入单号']);
            return;
        }
        $play_key = '';
        $play_keyList=[];
        $rateData = [];
        $rebate_rate = 0;
        $bet_serial_sql = "select * from bet_rule where bet_serial='$bet_serial' ";

        $deal_key = $context->getInfo('DealKey');
        $mysqlDeal = $config->__get("data_" . $deal_key);
        $bet_serial_data = iterator_to_array($mysqlDeal->query($bet_serial_sql));
        if (!empty($bet_serial_data)) {
            foreach ($bet_serial_data as $bet) {
                if(!in_array($bet['play_key'], $play_keyList)){
                    $play_keyList[]=$bet['play_key'];
                    $play_key = $bet['play_key'];
                    $rebate_rate = $bet['rebate_rate'];
                    $game_key_sql = "select game_key,play_key from lottery_game_play_intact where play_key='$play_key' limit 1 ";
                    $game_key_datas = iterator_to_array($public_mysql->query($game_key_sql));
                    if (empty($game_key_datas)) {
                        $context->reply(['status' => 203, 'msg' => '未找到对应彩种']);
                        return;
                    }
                    foreach ($game_key_datas as $game_key_data) {
                        $game_list = json_decode($cache->hget("GameWin", $game_key_data['game_key']));
                        if (empty($game_list)) {
                            $context->reply(['status' => 204, 'msg' => '赔率异常']);
                            return;
                        }
                        foreach ($game_list as $game) {
                            if ($game->play_key == $game_key_data['play_key']) {
                                $rateData[] = $game;
                            }
                        }
                    }
                }
                
            }
        } else {
            $context->reply(['status' => 205, 'msg' => '未找到该投注记录']);
            return;
        }

        $context->reply(['status' => 200, 'rateDetail' => $rateData, 'rebate_rate' => $rebate_rate]);
        return;
    }

}
