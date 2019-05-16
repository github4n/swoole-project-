<?php

/**
 * Class PromotionList
 * @description 优惠活动列表类
 * @author Rose
 * @date 2018-12-07
 * @link Websocket: Promotion/Manage/PromotionList
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Promotion\Manage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PromotionList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);
            return;
        }

        // 操作权限检测
        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_promotion', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_staff;
        $start = !empty($data['start_time']) ? $data['start_time'] : '';
        $end = !empty($data['end_time']) ? $data['end_time'] : '';
        $time = '';
        //开始时间的起止时间
        if (!empty($data['start_time'])) {
            $start = strtotime($data['start_time']);
        }
        if (!empty($data['end_time'])) {
            $end = strtotime($data['end_time']);
        }
        $paramSearch = [];
        if (!empty($start) && !empty($end)) {
            $paramSearch = [
                ':start' => $start,
                ':end' => $end,
            ];
            $time = ' AND start_time >= :start AND stop_time <=:end';
        }
        if (!empty($start) && empty($end)) {
            $paramSearch = [':start' => $start];
            $time = ' AND start_time >= :start';
        }
        if (empty($start) && !empty($end)) {
            $paramSearch = [':end' => $end];
            $time = ' AND stop_time <= :end';
        }
        $sql = 'SELECT * FROM promotion WHERE 1=1'.$time.' order by add_time desc';
        $total_sql = 'SELECT promotion_id FROM promotion WHERE 1=1'.$time;
        $list = [];
        $promotion_list = [];
        try {
            foreach ($mysql->query($sql, $paramSearch) as $rows) {
                $list[] = $rows;
            }
            $total = $mysql->execute($total_sql, $paramSearch);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取列表失败']);
            throw new \PDOException($e);
        }
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $promotion_list[$key]['promotion_id'] = $val['promotion_id'];
                $promotion_list[$key]['title'] = $val['title'];
                $promotion_list[$key]['publish'] = $val['publish'];
                $promotion_list[$key]['start_time'] = date('Y-m-d H:i:s', $val['start_time']);
                $promotion_list[$key]['stop_time'] = date('Y-m-d H:i:s', $val['stop_time']);
                $promotion_list[$key]['cover'] = $val['cover'];
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取列表成功', 'total' => $total, 'list' => $promotion_list]);
    }
}
