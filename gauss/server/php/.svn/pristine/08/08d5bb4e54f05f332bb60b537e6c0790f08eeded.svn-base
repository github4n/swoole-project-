<?php

namespace Plat\Websocket\Website\Site;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * Site class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/Site/Site
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class Site extends CheckLogin
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
        $site_name = isset($data['site_name']) ? $data['site_name'] : '';
        $status = isset($data['status']) ? $data['status'] : '';

        if (is_numeric($status)) {
            if ($status >= 4 || $status < 0) {
                $context->reply(['status' => 300, 'msg' => '站点状态不正确']);

                return;
            }
        }

        if (!empty($site_name) && is_numeric($status)) {
            $sql = 'SELECT site_key,site_name,status,create_time from site where 1=1 and site_name=:site_name and status=:status';
            $param = [':site_name' => $site_name, ':status' => $status];
        } elseif (!empty($site_name)) {
            $sql = 'SELECT site_key,site_name,status,create_time from site where 1=1 and site_name=:site_name';
            $param = [':site_name' => $site_name];
        } elseif (is_numeric($status)) {
            $sql = 'SELECT site_key,site_name,status,create_time from site where 1=1 and status=:status';
            $param = [':status' => $status];
        } else {
            $sql = 'SELECT site_key,site_name,status,create_time from site where 1=1';
            $param = [];
        }

        $site_list = iterator_to_array($mysqlAdmin->query($sql, $param));
        $siteList = [];
        if (!empty($site_list)) {
            foreach ($site_list as $key => $val) {
                $sql = 'SELECT count(user_id) as onlineusers from user_session where lose_time=0';
                $siteMysqlUser = $config->__get('data_'.$val['site_key'].'_user');
                $user_num = iterator_to_array($siteMysqlUser->query($sql));
                $siteList[$key]['site_key'] = $val['site_key'];
                $siteList[$key]['site_name'] = $val['site_name'];
                $siteList[$key]['user_num'] = $user_num[0]['onlineusers'];
                $siteList[$key]['status'] = $val['status'];
                $siteList[$key]['time'] = floor((time() - $val['create_time']) / 86400);
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $siteList,
        ]);
    }
}
