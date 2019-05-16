<?php

/**
 * Class MessageList
 * @description 通知消息列表类
 * @author Rose
 * @date 2018-12-01
 * @link Websocket: Website/Index/MessageList {"start_time":"2019-04-27","stop_time":"2019-04-27"}
 * @param string $start_time 开始时间
 * @param string $stop_time 结束时间
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-12
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class MessageList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if($StaffGrade != 0){
            $context->reply(["status"=>203,"msg"=>"当前账号没有操作权限"]);
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
        $start = isset($data["start_time"]) ? $data["start_time"] : '';
        $end = isset($data["end_time"]) ? $data["end_time"] : '';

        $time = '';
        $times = '';
        //开始时间的起止时间
        if(!empty($data["start_time"])){
            $start = strtotime($data["start_time"]);
            // 时间格式检查
            if(!$start) {
                $context->reply(['status' => 500, 'msg' => '开始时间格式不正确！']);
                return;
            }
        }
        if(!empty($data["end_time"])) {
            $end = strtotime($data["end_time"]);
            // 时间格式检查
            if(!$end) {
                $context->reply(['status' => 500, 'msg' => '开始时间格式不正确！']);
                return;
            }
        }

        $paramSearch = [];
        if(!empty($start) && !empty($end)){
            $time = " AND start_time >= :start AND stop_time<= :end";
            $paramSearch = [
                ':start' => $start,
                ':end' => $end
            ];
        }
        if(!empty($start) && empty($end)){
            $time = " AND start_time >= :start";
            $paramSearch = [
                ':start' => $start
            ];
        }
        if(empty($start) && !empty($end)){
            $time = " AND stop_time <= :end";
            $paramSearch = [
                ':end' => $end
            ];
        }
        $sql = "SELECT * FROM announcement WHERE 1=1".$time.$times . ' order by add_time desc';
        $totalsql = "SELECT announcement_id FROM announcement WHERE 1=1".$time.$times;
        $lists = array();
        try{
            foreach ($mysql->query($sql, $paramSearch) as $rows){
                $lists[] = $rows;
            }
            $total = $mysql->execute($totalsql, $paramSearch);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"获取失败"]);
            throw new \PDOException($e);
        }
        
        $list = array();
        if(!empty($lists)){
            foreach ($lists as $key=>$val){
                $list[$key]["announcement_id"] = $val["announcement_id"];
                $list[$key]["start_time"] = date("Y-m-d H:i:s",$val["start_time"]);
                $list[$key]["stop_time"] = date("Y-m-d H:i:s",$val["stop_time"]);
                $list[$key]["add_time"] = date("Y-m-d H:i:s",$val["add_time"]);
                $list[$key]["content"] = $val["content"];
                $list[$key]["publish"] = $val["publish"];
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取数据成功', 'total' => $total, 'list' => $list]);
    }
}