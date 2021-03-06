<?php

/**
 * Class Banner
 * @description 轮播图管理类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/BannerList
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BannerList extends CheckLogin
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

        $data = $context->getData();
        $mysql = $config->data_staff;
        $type = $data['type'] ?: 1;
        $list = array();
        if ($type == 1) {
            //启用
            $sql = 'SELECT * FROM carousel WHERE publish =1 order by add_time desc';
            foreach ($mysql->query($sql) as $rows) {
                $list[] = $rows;
            }
        } elseif ($type == 2) {
            //禁用
            $sql = 'SELECT * FROM carousel WHERE publish =0 order by add_time desc';
            foreach ($mysql->query($sql) as $rows) {
                $list[] = $rows;
            }
        } else {
            $context->reply(['status' => 205, 'msg' => '参数错误']);
            return;
        }
        $lists = [];
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $lists[$key]['carousel_id'] = $val['carousel_id'];
                $lists[$key]['img_src'] = $val['img_src'];
                $lists[$key]['start_time'] = date('Y-m-d H:i:s', $val['start_time']);
                $lists[$key]['stop_time'] = date('Y-m-d H:i:s', $val['stop_time']);
                $lists[$key]['link_type'] = $val['link_type'];
                $lists[$key]['link_data'] = $val['link_data'];
                $lists[$key]['publish'] = $val['publish'];
            }
        }
        $context->reply(['status' => 200, 'msg' => '列表获取成功', 'list' => $lists]);
    }
}
