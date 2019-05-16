<?php

/**
 * Class BulletinDelete
 * @description 会员公告删除类
 * @author Rose
 * @date 2018-12-03
 * @link Websocket: Website/Message/BulletinDelete {"layer_message_id":1}
 * @param int $layer_message_id 公告Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BulletinDelete extends CheckLogin
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
        if (!in_array('web_message', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_user;
        $mysql_staff = $config->data_staff;
        $layer_message_id = $data['layer_message_id'];
        if (!is_numeric($layer_message_id)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);

            return;
        }
        $sql = 'DELETE FROM layer_message WHERE layer_message_id=:layer_message_id';
        $param = [':layer_message_id' => $layer_message_id];
        try {
            $result = $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        if ($result > 0) {
            $context->reply(['status' => 200, 'msg' => '删除成功']);
        } else {
            $context->reply(['status' => 205, 'msg' => '删除失败，请检查参数']);
        }
        //添加日志信息
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_message',
            ':detail' => '删除公告id为'.$layer_message_id,
        ];
        $mysql_staff->execute($sql, $params);
    }
}
