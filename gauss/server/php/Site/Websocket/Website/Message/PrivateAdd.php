<?php

/**
 * Class PrivateAdd
 * @description 会员私信新增类
 * @author Rose
 * @date 2018-12-03
 * @link Websocket: Website/Message/PrivateAdd {"user_id":3,"user_key":"user002","title":"","start_time":"2018-12-12","stop_time":"2018-12-30","content":""}
 * @param int $user_id 会员Id
 * @param string $user_key 帐号
 * @param string $title 标题
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @param string $content 内容
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-09
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PrivateAdd extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);
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
        $user_id = !empty($data["user_id"]) ? $data["user_id"] : '';
        $user_key = !empty($data["user_key"]) ? $data["user_key"] : '';
        $title = !empty($data["title"]) ? $data["title"] : '';
        $content = $data["content"];
        if (!is_numeric($user_id)) {
            $context->reply(['status' => 204, 'msg' => '会员参数类型错误']);
            return;
        }
        if (empty($user_key)) {
            $context->reply(['status' => 205, 'msg' => '会员参数不能为']);
            return;
        }
        if (empty($content)) {
            $context->reply(['status' => 206, 'msg' => '私信内容不能为空']);
            return;
        }
        // 内容长度判断
        $length = mb_strlen($content);
        if($length > 80) {
            $context->reply(['status' => 500, 'msg' => '内容长度不能超过80个字符']);
            return;
        }
        if (empty($title)) {
            $context->reply(["status" => 207, "msg" => "私信标题不能为空"]);
            return;
        } else {
            $kTrim = new BulletinAdd;
            $title = $kTrim->trimEmpty($title);
        }
        // 标题长度判断
        $length = mb_strlen($title);
        if($length < 4 || $length > 40) {
            $context->reply(['status' => 500, 'msg' => '标题长度为4-40个字符之间']);
            return;
        }
        $sql = "INSERT INTO user_message SET user_id=:user_id,user_key=:user_key,title=:title,insert_time=:insert_time,content=:content";
        $param = [
            ":user_id" => $user_id,
            ":user_key" => $user_key,
            ":title" => $title,
            ":insert_time" => 0,
            ":content" => $content,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '新增成功']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '新增成功']);
        //添加日志信息
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo("StaffId"),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_message',
            ':detail' => '新增会员id为' . $user_id . '的私信',
        ];
        $mysql_staff->execute($sql, $params);
        $user_mysql = $config->data_user;
        $sql = "SELECT client_id FROM user_session WHERE user_id=:user_id";
        $param = ['user_id' => $user_id];
        foreach ($user_mysql->query($sql, $param) as $row) {
            $id = $row['client_id'];
            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $taskAdapter->plan('NotifyApp', ['path' => 'Message/UserMessage', 'data' => ['user_id' => $user_id, "id" => $id]]);
        }
    }

}
