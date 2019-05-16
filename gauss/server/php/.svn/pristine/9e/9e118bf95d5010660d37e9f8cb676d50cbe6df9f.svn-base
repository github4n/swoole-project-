<?php

/**
 * Class MessageEditUpdate
 * @description 提交修改消息类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/MessageEditUpdate {"announcement_id":1,"start_time":"2018-11-30","stop_time":"2018-12-25","content":"","publish":1}
 * @param int $announcement_id 首页消息Id
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @param string $content 内容
 * @param int $publish 状态
 * @modifyAuthor Kayden
 * $modifyDate 2019-04-15
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class MessageEditUpdate extends CheckLogin
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
        $start_time = $data['start_time'];
        $stop_time = $data['stop_time'];
        $content = $data['content'];
        $publish = $data['publish'];
        if(!is_numeric($announcement_id)){
            $context->reply(['status' => 203, 'msg' => '参数错误']);
            return;
        }
        if(empty($publish)) {
            $context->reply(['status' => 500, 'msg' => '请选择状态']);
            return;
        }
        if(empty($start_time)){
            $context->reply(['status' => 204, 'msg' => '开始时间不能为空']);
            return;
        }else{
            $start_time = strtotime($start_time . ' 00:00:00');
        }
        if(empty($stop_time)){
            $context->reply(['status' => 205, 'msg' => '结束时间不能为空']);
            return;
        }else{
            $stop_time =  strtotime($stop_time . ' 23:59:59');
        }
        if(empty($content) || mb_strlen($content) > 80){
            $context->reply(['status' => 206, 'msg' => '内容不能为空，且内容长度能超过80个字符']);
            return;
        }
        if($publish == 1){
            $publish = 1;
        }elseif ($publish == 2){
            $publish = 0;
        }else{
            $publish = 1;
        }
        if($start_time>$stop_time){
            $context->reply(['status' => 207, 'msg' => '开始时间不能大于结束时间']);
            return;
        }
        $sql = "UPDATE announcement SET start_time=:start_time, stop_time=:stop_time, content=:content, publish=:publish WHERE announcement_id=:announcement_id";
        $param = [
            ":announcement_id"=>$announcement_id,
            ":start_time"=>$start_time,
            ":stop_time"=>$stop_time,
            ":content"=>$content,
            ":publish"=>$publish,
        ];
        try{
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'web_homepage',
            ':detail' =>'修改id为' . $announcement_id . '的消息通知',
        ];
        $mysql->execute($sql, $params);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Index/AppAnnouncement', [], time());
    }
}