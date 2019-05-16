<?php

/**
 * Class PopAdd
 * @description 网站管理App弹窗消息添加消息类
 * @author Rose
 * @date 2019-02-15
 * @link Websocket: Website/Index/App/PopAdd {"content":"测试内容","publish":"1","start_time":"2019-04-27","stop_time":"2019-04-27"}
 * @param string $content 内容
 * @param string $publish 状态
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-12
 */

namespace Site\Websocket\Website\Index\App;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PopAdd extends CheckLogin
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
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限权限']);
            return;
        }

        //接受数据
        $data = $context->getData();
        $content = $data['content'];
        $publish = $data['publish'];
        $start_time = strtotime($data['start_time']);
        $stop_time = strtotime($data['stop_time']);

        if (empty($publish)) {
            $context->reply(['status' => 500, 'msg' => '请选择状态']);
            return;
        }

        if (empty($content) || mb_strlen($content) > 150) {
            $context->reply(['status' => 201, 'msg' => '内容不能为空，且内容长度不能超过150个字符']);
            return;
        }

        if (empty($start_time)) {
            $context->reply(['status' => 203, 'msg' => '开始时间为空或时间格式不正确']);
            return;
        }

        if (empty($stop_time)) {
            $context->reply(['status' => 204, 'msg' => '停止时间为空或时间格式不正确']);
            return;
        } else {
            $stop_time = strtotime(date('Y-m-d', $stop_time)) + 86399;
        }

        if (!is_numeric($publish)) {
            $context->reply(['status' => 205, 'msg' => '启用状态码不是数字']);
            return;
        }
        if ($publish == 1) {
            $publish = 1;
        } else {
            $publish = 0;
        }
        //连接数据库
        $staff_mysql = $config->data_staff;

        //插入数据
        $sql = 'INSERT INTO popup (content,publish,start_time,stop_time,add_time) VALUES (:content,:publish,:start_time,:stop_time,:add_time)';
        $param = [
            ':content' => $content,
            ':publish' => $publish,
            ':start_time' => $start_time,
            ':stop_time' => $stop_time,
            ':add_time' => time(),
        ];
        $flag = $staff_mysql->execute($sql, $param);
        if ($flag) {
            $context->reply(['status' => 200, 'msg' => '新增成功']);
        } else {
            $context->reply(['status' => 206, 'msg' => '新增失败']);
        }

        //记录日志
        $operate_sql = 'INSERT INTO operate_log (staff_id,operate_key,detail,client_ip) VALUES (:staff_id,:operate_key,:detail,:client_ip)';
        $operate_param = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':operate_key' => 'web_homepage',
            ':detail' => '新增app弹窗消息',
            ':client_ip' => ip2long($context->getClientAddr()),
        ];
        $staff_mysql->execute($operate_sql, $operate_param);
    }
}