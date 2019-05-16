<?php

namespace Plat\Websocket\Website\SitePlay;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * SiteList class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/SitePlay/SiteList
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SiteList extends CheckLogin
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
        $site_name = isset($data['site_name']) ? $data['site_name'] : '';
        if (!empty($site_name)) {
            $sql = 'select site_key,site_name from site where site_name =:site_name';
            $param = [':site_name' => $site_name];
        } else {
            $sql = 'select site_key,site_name from site';
            $param = [];
        }

        $mysql = $config->data_admin;
        $site_list = [];

        $site = iterator_to_array($mysql->query($sql, $param));
        if (!empty($site)) {
            foreach ($site as $key => $val) {
                $siteMysqlUser = $config->__get('data_'.$val['site_key'].'_user');
                $sql = 'select count(user_id) as onlineusers from user_session where lose_time=0';
                $user_num = iterator_to_array($siteMysqlUser->query($sql));
                $playSql = 'select play_key from site_play where acceptable=0 and site_key=:site_key';
                $play_num = $mysql->execute($playSql, [':site_key' => $val['site_key']]);
                $site_list[$key]['site_key'] = $val['site_key'];
                $site_list[$key]['site_name'] = $val['site_name'];
                $site_list[$key]['user_num'] = $user_num[0]['onlineusers'];
                $site_list[$key]['play_num'] = $play_num;
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $site_list,
        ]);
    }
}
