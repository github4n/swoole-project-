<?php

/**
 * Class PrivateDetail
 * @description 消息管理会员私信详情类
 * @author Rose
 * @date 2018-12-03
 * @link Websocket: Website/Message/PrivateDetail {"user_message_id":1}
 * @param int $user_message_id 私信Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PrivateDetail extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if($StaffGrade != 0){
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
        $user_message_id = $data['user_message_id'];
        if(!is_numeric($user_message_id)){
            $context->reply(['status' => 205, 'msg' => '参数类型错误']);
            return;
        }
        $sql = "SELECT title,content FROM user_message WHERE user_message_id = :user_message_id";
        $param = [":user_message_id" => $user_message_id];
        $info = array();
        try{
            foreach ($mysql->query($sql,$param) as $row){
                $info = $row ;
            }
        }catch (\PDOException $e){
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        if(empty($info)){
            $context->reply(['status' => 206, 'msg' => '检查参数是否正确']);
            return;
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'info' => $info]);
    }
}