<?php

/**
 * Class PopEdit
 * @description App弹窗消息修改弹窗消息类
 * @author Rose
 * @date 2019-02-15
 * @link Websocket: Website/Index/App/PopEdit {"popup_id":1}
 * @param int $popup_id 消息Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index\App;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PopEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);
            return;
        }

        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);
            return;
        }

        //接收参数
        $data = $context->getData();
        $popup_id = $data['popup_id'];

        if (!is_numeric($popup_id)) {
            $context->reply(['status' => 201, 'msg' => '参数类型错误']);
            return;
        }

        //连接数据库
        $staff_mysql = $config->data_staff;

        //查询数据
        $tmp_list = [];
        $list = [];
        $sql = 'SELECT * FROM popup WHERE popup_id=:popup_id';
        $param = [
            ':popup_id' => $popup_id,
        ];
        foreach ($staff_mysql->query($sql, $param) as $row) {
            $tmp_list[] = $row;
        }

        foreach ($tmp_list as $k => $v) {
            $list[$k]['popup_id'] = $v['popup_id'];
            $list[$k]['publish'] = $v['publish'];
            $list[$k]['start_time'] = date('Y-m-d H:i:s', $v['start_time']);
            $list[$k]['stop_time'] = date('Y-m-d H:i:s', $v['stop_time']);
            $list[$k]['content'] = $v['content'];
        }

        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $list]);

        //记录日志
        $operate_sql = 'INSERT INTO operate_log (staff_id,operate_key,detail,client_ip) VALUES (:staff_id,:operate_key,:detail,:client_ip)';
        $operate_param = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':operate_key' => 'web_homepage',
            ':detail' => '编辑app弹窗消息',
            ':client_ip' => ip2long($context->getClientAddr()),
        ];
        $staff_mysql->execute($operate_sql, $operate_param);
    }
}
