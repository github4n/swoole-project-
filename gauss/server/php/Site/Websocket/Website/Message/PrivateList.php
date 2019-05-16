<?php

/**
 * Class PrivateList
 * @description 会员私信列表类
 * @author Rose
 * @date 2018-12-03
 * @link Websocket: Website/Message/PrivateList
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Message;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class PrivateList extends CheckLogin
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
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        // 参数
        $paramSearch = [];
        if(!empty($user_key)){
            $paramSearch[':user_key'] = $user_key;
            $user_key = " WHERE user_key = :user_key";
        }
        $sql = "SELECT * FROM user_message " . $user_key . ' order by insert_time desc';
        $total_sql = "SELECT user_message_id FROM user_message " . $user_key;
        $list = array();
        try{
            foreach ($mysql->query($sql, $paramSearch) as $rows){
                $list[] = $rows;
            }
            $total = $mysql->execute($total_sql, $paramSearch);
        }catch (\PDOException $e){
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        $lists = array();
        if(!empty($list)){
            foreach ($list as $key=>$val) {
                $lists[$key]['user_message_id'] = $val['user_message_id'];
                $lists[$key]['title'] = $val['title'];
                $lists[$key]['user_key'] = $val['user_key'];
                $lists[$key]['create_time'] = date('Y-m-d H:i:s', $val['insert_time']);
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total' => $total, 'list' => $lists]);
    }
}