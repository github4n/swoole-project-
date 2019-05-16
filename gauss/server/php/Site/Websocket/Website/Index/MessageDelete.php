<?php

/**
 * Class MessageDelete
 * @description 首页删除类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/MessageDelete {"announcement_id":1}
 * @param $announcement_id 首页消息Id
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-27
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class MessageDelete extends CheckLogin
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
            $context->reply(['status' => 205, 'msg' => '参数错误']);
            return;
        }
        $sql = "DELETE FROM announcement WHERE announcement_id=:announcement_id";
        $param = [":announcement_id"=>$announcement_id];
        try{
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '删除成功']);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_homepage',
            ':detail' =>'删除id为' . $announcement_id . '的消息通知',
        ];
        $mysql->execute($sql, $params);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Index/AppAnnouncement', [], time());
    }
}