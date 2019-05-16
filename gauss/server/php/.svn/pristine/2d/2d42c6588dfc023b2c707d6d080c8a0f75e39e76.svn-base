<?php

/**
 * Class PopDelete
 * @description 删除弹窗消息类
 * @author Rose
 * @date 2019-02-15
 * @link Websocket: Website/Index/App/PopDelete {"popup_id":2}
 * @param int $popup_id 消息Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index\App;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PopDelete extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        //接收参数
        $data = $context->getData();
        $popup_id = $data['popup_id'];

        if (empty($popup_id)) {
            $context->reply(['status' => 201, 'msg' => '内容序号为空']);
            return;
        } elseif (!is_numeric($popup_id)) {
            $context->reply(['status' => 201, 'msg' => '参数类型错误']);
            return;
        }

        //连接数据库
        $staff_mysql = $config->data_staff;

        //删除数据
        $sql = 'DELETE FROM popup WHERE popup_id=:popup_id';
        $param = [':popup_id' => $popup_id];
        $flag = $staff_mysql->execute($sql, $param);
        if ($flag) {
            $context->reply(['status' => 200, 'msg' => '删除成功']);
        } else {
            $context->reply(['status' => 200, 'msg' => '删除失败']);
        }

        //记录日志
        $operate_sql = 'INSERT INTO operate_log (staff_id,operate_key,detail,client_ip) VALUES (:staff_id,:operate_key,:detail,:client_ip)';
        $operate_param = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':operate_key' => 'web_homepage',
            ':detail' => '删除app弹窗消息',
            ':client_ip' => ip2long($context->getClientAddr()),
        ];
        $staff_mysql->execute($operate_sql, $operate_param);
    }
}
