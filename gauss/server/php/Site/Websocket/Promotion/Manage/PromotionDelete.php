<?php

/**
 * Class PromotionDelete
 * @description 优惠活动删除类
 * @author Rose
 * @date 2018-12-07
 * @link Websocket: Promotion/Manage/PromotionDelete {"promotion_id":1}
 * @param int $pomotion_id 活动Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Promotion\Manage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PromotionDelete extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if($StaffGrade != 0) {
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
        if(!is_numeric($promotion_id)) {
            $context->reply(['status' => 205, 'msg' => '参数类型错误']);
            return;
        }
        $sql = 'DELETE FROM promotion WHERE promotion_id=:promotion_id';
        $param = [
            ':promotion_id' => $promotion_id,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除活动失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '删除活动成功']);
        $user_mysql = $config->data_user;
        $sql = 'SELECT client_id FROM user_session WHERE lose_time = 0';
        foreach ($user_mysql->query($sql) as $row) {
            $id = $row['client_id'];
            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $taskAdapter->plan('NotifyApp', ['path' => 'Message/Activity', 'data' => ['id' => $id]]);
        }
    }
}