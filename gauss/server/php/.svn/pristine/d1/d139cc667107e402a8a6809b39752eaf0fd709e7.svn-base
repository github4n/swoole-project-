<?php

namespace Plat\Websocket\Report;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * SiteList class.
 *
 * @description   站点列表
 * @Author  rose
 * @date  2019-04-23
 * @links  url
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 *
 * @modifyAuthor   blake
 * @modifyTime  2019-05-14
 */
class SiteList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //获取站点信息
        $mysqlAdmin = $config->data_admin;
        $sql = 'select site_key,site_name from site';
        $sitelist = iterator_to_array($mysqlAdmin->query($sql));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'site_list' => $sitelist]);
    }
}
