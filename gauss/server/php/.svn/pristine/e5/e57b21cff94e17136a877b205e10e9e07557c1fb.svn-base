<?php

/**
 * Class PromotionUpdate
 * @description 优惠活动编辑保存类
 * @author Rose
 * @date 2018-12-07
 * @link Websocket: Promotion/Manage/PromotionUpdate {"promotion_id":1,"title":"","publish":1,"start_time":"","stop_time":"","content":"","cover":""}
 * @param int $promotion_id 活动Id
 * @param string $title 标题
 * @param int $publish 状态
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @param string $content 内容
 * @param string $cover 封面
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Promotion\Manage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;
use Site\Websocket\Website\Message\BulletinAdd;

class PromotionUpdate extends CheckLogin
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
        if (!in_array('web_promotion', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_staff;
        $promotion_id = $data['promotion_id'];
        $title = $data['title'];
        $publish = $data['publish'];
        $start_time = $data['start_time'];
        $stop_time = $data['stop_time'];
        $content = $data['content'];
        $cover = $data['cover'];
        if (empty($publish)) {
            $context->reply(['status' => 500, 'msg' => '请选择状态']);
            return;
        }
        if (!is_numeric($promotion_id)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);
            return;
        }
        if (empty($title)) {
            $context->reply(['status' => 205, 'msg' => '活动标题不能为空']);
            return;
        } else {
            $kTrim = new BulletinAdd;
            $title = $kTrim->trimEmpty($title);
        }
        if (mb_strlen($title) < 4 || mb_strlen($title) > 20) {
            $context->reply(['status' => 206, 'msg' => '标题长度在4-20个字符之间']);
            return;
        }
        if (empty($content)) {
            $context->reply(['status' => 207, 'msg' => '内容描述不能为空']);
            return;
        }
        if (!empty($start_time)) {
            $start_time = strtotime($start_time);
        } else {
            $start_time = 0;
        }
        if (!empty($stop_time)) {
            $stop_time = strtotime($stop_time);
        } else {
            $stop_time = 0;
        }
        $sql = 'UPDATE promotion SET title=:title, publish=:publish, start_time=:start_time,stop_time=:stop_time, cover=:cover, content=:content WHERE promotion_id=:promotion_id';
        $param = [
            ':promotion_id' => $promotion_id,
            ':title' => $title,
            ':publish' => $publish == 1 ? 1 : 0,
            ':start_time' => $start_time,
            ':stop_time' => $stop_time,
            ':cover' => $cover ?: 0,
            ':content' => $content,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改活动失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改活动成功']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('NotifyApp', ['path' => 'Message/Activity', 'data' => []]);
    }
}
