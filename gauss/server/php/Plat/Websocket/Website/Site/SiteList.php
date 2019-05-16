<?php

namespace Plat\Websocket\Website\Site;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * SiteList class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/Site/SiteList {"site_key":"site1"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SiteList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_status_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $mysqlAdmin = $config->data_admin;
        $site_key = isset($data['site_key']) ? $data['site_key'] : '';
        if (empty($site_key)) {
            $context->reply(['status' => '205', 'msg' => '站点关键字不能为空']);

            return;
        }

        $sql = 'select status from site where site_key=:site_key';
        $info = [];
        foreach ($mysqlAdmin->query($sql, [':site_key' => $site_key]) as $row) {
            $info = $row;
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'data' => $info,
        ]);
    }
}
