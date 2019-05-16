<?php

namespace Plat\Websocket\Website\SiteLottery;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * SiteLotteryList class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/SiteLottery/SiteLotteryList   {"site_name":""}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SiteLotteryList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_lottery_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $admin_mysql = $config->data_admin;
        $site = !empty($data['site_name']) ? $data['site_name'] : '';
        $site_find_sql = 'select * from site  where 1=1 ';
        $param = [];
        if (!empty($site)) {
            $site_find_sql = 'select * from site  where 1=1 and site_name=:site_name';
            $param = [':site_name' => $site];
        }

        $lastResult = [];
        $site_data = iterator_to_array($admin_mysql->query($site_find_sql, $param));
        if (!empty($site_data)) {
            foreach ($site_data as $siteDetail) {
                $site_key = $siteDetail['site_key'];
                $closeLottery_sql = 'select count(acceptable!=1 or null ) as number  from site_game  where site_key=:site_key  ';
                $game_number = iterator_to_array($admin_mysql->query($closeLottery_sql, [':site_key' => $site_key]));
                $gameNumber = $game_number[0]['number'];
                $siteUser = $config->__get('data_'.$site_key.'_user');
                $onLineUser_sql = 'select count(lose_time=0 or null ) as userNumber from user_session ';
                $user_number = iterator_to_array($siteUser->query($onLineUser_sql));
                $userNumber = $user_number[0]['userNumber'];
                $lastResult[] = ['site_key' => $site_key, 'site_name' => $siteDetail['site_name'], 'onlineUserNumber' => $userNumber, 'closeGameNumber' => $gameNumber];
            }
        }
        $context->reply([
            'status' => 200,
            'list' => $lastResult,
            'msg' => '获取成功',
        ]);
    }
}
