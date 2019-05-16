<?php

namespace Plat\Websocket\Website\ExternalGame;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * Site class.
 *
 * @description   第三方游戏开关列表
 * @Author  avery
 * @date  2019-04-23
 * @links  Website\ExternalGame\Site {"site_name":"测试站点A"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class Site extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_external_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $mysql = $config->data_admin;
        $accmysql = $config->data_admin;

        $data = $context->getData();
        $site_name = isset($data['site_name']) ? $data['site_name'] : '';
        $sql = 'SELECT site_key,site_name from site ';
        $accsql = 'SELECT site_key,count(acceptable) as closegame from site_external_game where  acceptable = 0 group by site_key';
        $accdate = iterator_to_array($accmysql->query($accsql));
        $site = iterator_to_array($mysql->query($sql));
        if (!empty($site_name)) {
            $sql = "SELECT site_key,site_name from site where site_name =:site_name";
            $mysql = $config->data_admin;
            $site = iterator_to_array($mysql->query($sql,[':site_name'=>$site_name]));
            foreach ($site as $row) {
                $site_key = $row['site_key'];
            }
            $accsql = 'SELECT site_key,count(acceptable) as closegame from site_external_game where acceptable = 0 and site_key = :site_key group by site_key';
            $accdate = iterator_to_array($accmysql->query($accsql, [':site_key' => $site_key]));
        }

        if (!empty($site)) {
            foreach ($site as $k => $v) {
                $v['closegame'] = 0;
                foreach ($accdate as $key => $val) {
                    if ($v['site_key'] == $val['site_key']) {
                        $val['site_name'] = $v['site_name'];
                        $v['closegame'] = $val['closegame'];
                    }
                }
                $usermysql = $config->__get('data_'.$v['site_key'].'_user');
                $usersql = 'select count(user_id) as onlineusers from user_session where lose_time = 0';
                $userData = iterator_to_array($usermysql->query($usersql));
                foreach ($userData as $row) {
                    $v['onlineusers'] = $row['onlineusers'];
                }
                $list[] = $v;
            }
        } else {
            $list = [];
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $list]);
    }
}
