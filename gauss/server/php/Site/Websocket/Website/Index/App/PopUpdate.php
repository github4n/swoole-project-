<?php

/**
 * Class PopUpdate
 * @description App弹窗保存修改的弹窗消息
 * @author Rose
 * @date 2019-02-15
 * @link Websocket: Website/Index/App/PopUpdate {"popup_id":1,"publish":1,"start_time":"","stop_time":"","content":""}
 * @param int $popup_id 消息Id
 * @param int $publish 状态
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @param string $content 消息内容
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-12
 */

namespace Site\Websocket\Website\Index\App;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PopUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);
            return;
        }

        $auth = json_decode($context->getInfo('StaffAuth'),true);
        if (!in_array('web_homepage',$auth))
        {
            $context->reply(["status"=>203,'msg' => "当前账号没有操作权限权限"]);
            return;
        }

        //接收数据
        $data = $context->getData();
        $popup_id = $data['popup_id'];
        $content = $data['content'];
        $publish = $data['publish'];
        $start_time = strtotime($data['start_time']);
        $stop_time = strtotime($data['stop_time']);

        if (empty($popup_id))
        {
            $context->reply(['status' => 201, 'msg' => '内容序号为空']);
            return;
        }else if (!is_numeric($popup_id))
        {
            $context->reply(['status' => 202, 'msg' => '内容序号数据类型错误']);
            return;
        }

        if (empty($content))
        {
            $context->reply(['status' => 204, 'msg' => '内容不能为空，且内容长度不能超过150个字符']);
            return;
        }

        if (empty($publish))
        {
            $context->reply(['status' => 205, 'msg' => '请选择状态']);
            return;
        }else if (!is_numeric($publish))
        {
            $context->reply(['status' => 206, 'msg' => '状态码类型错误']);
            return;
        }

        if (empty($start_time))
        {
            $context->reply(['status' => 207, 'msg' => '开始时间为空或时间格式不正确']);
            return;
        }

        if (empty($stop_time))
        {
            $context->reply(['status' => 208, 'msg' => '停止时间为空或时间格式不正确']);
            return;
        } else {
            $stop_time = strtotime(date('Y-m-d', $stop_time)) + 86399;
        }

        //连接数据库
        $staff_mysql = $config->data_staff;

        //修改数据
        $sql = 'UPDATE popup SET content=:content,publish=:publish,start_time=:start_time,stop_time=:stop_time WHERE popup_id=:popup_id';
        $param = [
            ':content' =>$content,
            ':publish' =>$publish,
            ':start_time' =>$start_time,
            ':stop_time' =>$stop_time,
            ':popup_id' =>$popup_id,
        ];

        $flag = $staff_mysql->execute($sql,$param);
        if ($flag)
        {
            $context->reply(['status' => 200,'msg' => '修改成功']);
            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            // $taskAdapter->plan('Index/AppPop', [],time());
        }else
        {
            $context->reply(['status' => 200,'msg' => '修改失败']);
        }

        //记录日志
        $operate_sql = 'INSERT INTO operate_log (staff_id,operate_key,detail,client_ip) VALUES (:staff_id,:operate_key,:detail,:client_ip)';
        $operate_param = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':operate_key' => 'web_homepage',
            ':detail' => '更新app弹窗消息',
            ':client_ip' => ip2long($context->getClientAddr())
        ];
        $staff_mysql->execute($operate_sql,$operate_param);
    }
}