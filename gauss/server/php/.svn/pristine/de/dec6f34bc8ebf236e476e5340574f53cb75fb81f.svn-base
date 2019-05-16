<?php

/**
 * Class MessageEdit
 * @description 编辑首页消息类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/MessageEdit {"announcement_id":1}
 * @param int $announcement_id 首页消息Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class MessageEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        // 操作权限检测
        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $data = $context->getData();
        $mysql = $config->data_staff;
        $announcement_id = $data['announcement_id'];
        if(!is_numeric($announcement_id)){
            $context->reply(['status'=>205, 'msg' => '参数错误']);
            return;
        }
        $sql = "SELECT * FROM announcement WHERE announcement_id=:announcement_id";
        $param = [':announcement_id' => $announcement_id];
        $list = array();
        try{
            foreach ($mysql->query($sql,$param) as $row){
                $list = $row;
            }
        }catch (\PDOException $e){
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        if(empty($list)){
            $context->reply(['status' => 206, 'msg' => '获取信息有误，检查参数']);
            return;
        }
        $lists = [
            'announcement_id' => $list['announcement_id'] ,
            'content' => $list['content'] ,
            'publish' => $list['publish'] ,
            'start_time' => date('Y-m-d', $list['start_time']) ,
            'stop_time' =>date('Y-m-d', $list['stop_time']) ,
        ] ;
        $context->reply(['status' => 200, 'msg' => '获取成功', 'info' => $lists]);
    }
}