<?php

/**
 * Class BulletinEdit
 * @description 会员公告修改类
 * @author Rose
 * @date 2018-12-03
 * @link Website/Message/BulletinEdit {"layer_message_id":1}
 * @param int $layer_message_id 公告Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class BulletinEdit extends CheckLogin
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
        $layer_message_id = $data['layer_message_id'];
        if (!is_numeric($layer_message_id)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);
            return;
        }
        $sql = 'SELECT * FROM layer_message WHERE layer_message_id=:layer_message_id';
        $param = [':layer_message_id' => $layer_message_id];
        $list = array();
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $list = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        $lists = array();
        if (!empty($list)) {
            $lists['layer_message_id'] = $list['layer_message_id'];
            $lists['title'] = $list['title'];
            $lists['layer_id'] = $list['layer_id'];
            $lists['start_time'] = date('Y-m-d', $list['start_time']);
            $lists['stop_time'] = date('Y-m-d', $list['stop_time']);
            $lists['create_time'] = date('Y-m-d H:i:s', $list['start_time']);
            $lists['content'] = $list['content'];
            $lists['publish'] = $list['publish'];
            $lists['cover'] = (empty($list['cover']) || $list['cover'] == 'null') ? 0 : $list['cover'];
            $context->reply(['status' => 200, 'msg' => '获取成功', 'info' => $lists]);
        } else {
            $context->reply(['status' => 205, 'msg' => '获取消息失败，请检查参数']);
        }
    }
}
