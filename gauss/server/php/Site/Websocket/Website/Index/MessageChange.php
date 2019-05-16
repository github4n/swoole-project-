<?php

/**
 * Class MessageChange
 * @description 网站管理状态修改类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/MessageChange  {"announcement_id":2,"publish":1}
 * @param int $announcement_id 首页消息Id
 * @param int $publish 状态
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class MessageChange extends CheckLogin
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
        if(!is_numeric($announcement_id)) {
            $context->reply(['status' => 205, 'msg' => '参数错误']);
            return;
        }
        $publish = $data['publish'];
        if(!empty($publish)) {
            if($publish == 1){
                $publish = 1;
            }elseif ($publish == 2){
                $publish = 0;
            }else{
                $context->reply(['status' => 204, 'msg' => '应用状态有误']);
                return;
            }
        }else{
            $context->reply(['status' => 206, 'msg' => '提交修改的信息']);
            return;
        }
        $sql = "UPDATE announcement SET publish=:publish  WHERE announcement_id=:announcement_id";
        $param = [':announcement_id' => $announcement_id, ':publish' => $publish];
        try{
            $mysql->execute($sql,$param);
        }catch(\PDOException $e){
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Index/AppAnnouncement', [],time());
    }
}