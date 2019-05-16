<?php

/**
 * Class BannerEdit
 * @description 网站管理编辑轮播图类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/BannerEdit {"carousel_id":1}
 * @param int $carousel_id 轮播图Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BannerEdit extends CheckLogin
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
        $carousel_id = $data['carousel_id'];
        if (!is_numeric($carousel_id)) {
            $context->reply(['status' => 205, 'msg' => '参数错误']);
            return;
        }
        $sql = 'SELECT * FROM carousel WHERE carousel_id=:carousel_id';
        $param = [':carousel_id' => $carousel_id];
        $list = array();
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $list = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        if (empty($list)) {
            $context->reply(['status' => 206, 'msg' => '获取信息有误，检查参数']);
            return;
        }
        $list['add_time'] = date('Y-m-d H:i:s', $list['add_time']);
        $list['start_time'] = date('Y-m-d H:i:s', $list['start_time']);
        $list['stop_time'] = date('Y-m-d H:i:s', $list['stop_time']);
        $context->reply(['status' => 200, 'msg' => '获取成功', 'info' => $list]);
    }
}
