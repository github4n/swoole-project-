<?php

namespace App\Websocket\ExternalGame;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;

/*
 * 获取三方游戏列表接口
 * @description   app/获取三方游戏列表数据
 * @Author  nathan
 * @date  2019-05-08
 * @links  ExternalGame/GameList
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-08
 * @param interface_key|string 平台Key
 * @return  status
 * 200|成功 data|array
 * 401|无效类型
 */
class GameList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $param = $context->getData();
        $interface_key = isset($param['interface_key']) ? $param['interface_key'] : '';
        if (empty($interface_key)) {
            $context->reply(['status' => 401, 'msg' => '无效类型']);

            return;
        }
        $mysqlStaff = $config->data_staff;
        $sql = 'select game_key,acceptable from external_game where interface_key = :interface_key';
        $game_list = [];
        foreach ($mysqlStaff->query($sql, [':interface_key' => $interface_key]) as $value) {
            $game_list[] = $value;
        }
        $context->reply(['status' => 200, 'msg' => '成功', 'data' => $game_list]);

        return;
    }
}
