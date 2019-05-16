<?php

/**
 * Class BannerType
 * @description 轮播图的链接方式类
 * @author Rose
 * @date 2018-12-07
 * @link Website/Index/BannerType
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BannerType extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        // 操作权限检测
        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $mysql = $config->data_staff;
        $mysql_user = $config->data_user;
        $promotion_sql = 'SELECT promotion_id FROM promotion WHERE publish = 1';
        $list = [];
        foreach ($mysql->query($promotion_sql) as $rows) {
            $list[] = $rows;
        }
        $promotion = [];
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $promotion[] .= $val['promotion_id'];
            }
        }
        $message_sql = 'SELECT layer_message_id FROM layer_message WHERE publish = 1 and layer_id = 0';
        $message_list = [];
        foreach ($mysql_user->query($message_sql) as $rows) {
            $message_list[] = $rows;
        }
        $message = [];
        if (!empty($message_list)) {
            foreach ($message_list as $key => $val) {
                $message[] .= $val['layer_message_id'];
            }
        }
        $link = ['webview', 'brower', 'bet', 'promotion', 'layer_message'];
        $link_data = [];
        $link_data['webview'] = [];
        $link_data['brower'] = [];
        $link_data['bet'] = json_decode($context->getInfo('GameList'));
        $link_data['promotion'] = $promotion;
        $link_data['layer_message'] = $message;
        $context->reply(['status' => 200, 'msg' => '信息获取成功', 'link_type' => $link, 'link_data' => $link_data]);
    }
}
