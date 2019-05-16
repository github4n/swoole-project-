<?php

namespace Plat\Websocket\Website\SitePlay;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * Undocumented class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/SitePlay/SitePlayList {"site_key":"site2"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SitePlayList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_play_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $mysqlAdmin = $config->data_admin;
        $site_key = isset($data['site_key']) ? $data['site_key'] : '';
        if (empty($site_key)) {
            $context->reply(['status' => 200, 'msg' => '站点关键字不能为空']);

            return;
        }
        $site_list = [];
        $sql = 'select model_key,game_key,play_key,acceptable from site_play where site_key=:site_key order by model_key desc';
        $list = iterator_to_array($mysqlAdmin->query($sql, [':site_key' => $site_key]));
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $acceptable = empty($val['acceptable']) ? false : true;
                $site_list[$val['model_key']][$val['game_key']]['play_list'][] = ['play_key' => $val['play_key'], 'acceptable' => $acceptable];
                $site_list[$val['model_key']][$val['game_key']]['isOff'] = '';
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'data' => $site_list,
        ]);
    }
}
