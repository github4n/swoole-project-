<?php

namespace Plat\Websocket\Website\ExternalGame;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * SiteLottery class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-23
 * @links  Website\ExternalGame\SiteLottery {"site_key":"site1"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SiteLottery extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_external_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $site_key = $data['site_key'];
        $mysql = $config->data_admin;
        $sitesql = 'select category_key,game_key,acceptable,interface_key from site_external_game where site_key = :site_key';
        $param = [':site_key' => $site_key];
        foreach ($mysql->query($sitesql, $param) as $rows) {
            $list[] = $rows;
        }
        $game_list = [];

        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $game_list['interface_key'] = $v['interface_key'];
                $game_list['game_list'][] = [
                    'game_key' => $v['game_key'],
                    'acceptable' => $v['acceptable'],
                ];
                $game_list['isOff'] = '';
            }
        }

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => [$game_list],
        ]);
    }
}
