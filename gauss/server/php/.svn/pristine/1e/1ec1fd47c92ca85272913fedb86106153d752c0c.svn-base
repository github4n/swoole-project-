<?php

namespace Plat\Websocket\LotteryTicket\Play;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * TicketPlay class.
 *
 * @description   彩票列表
 * @Author  avery
 * @date  2019-04-18
 * @links  LotteryTicket/Play/TicketPlay
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class TicketPlay extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('ticket_play_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        //彩票类型
        $model_list = json_decode($context->getInfo('ModelList'));
        $data = $context->getData();
        $model_key = $data['model_key'] ?: 'dice';
        //彩票类型
        $mysql = $config->data_public;
        //彩种及开关
        $gamelist = array();

        $gamesql = 'SELECT game_key,game_name,acceptable FROM lottery_game WHERE model_key=:model_key';
        $param = [':model_key' => $model_key];
        foreach ($mysql->query($gamesql, $param) as $rows) {
            $gamelist[] = $rows;
        }
        //玩法和开关
        $switchsql = 'SELECT game_key,play_key,play_name,acceptable FROM lottery_game_play_intact WHERE model_key =:model_key ';
        $params = [':model_key' => $model_key];
        $play_list = iterator_to_array($mysql->query($switchsql, $params));

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'model_list' => $model_list,
            'game_list' => $gamelist,
            'play_list' => $play_list,
        ]);
    }
}
