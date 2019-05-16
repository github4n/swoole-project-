<?php
namespace App\Task\Message;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class UserMessage implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
        ['user_id' => $userId,'id'=>$id] = $context->getData();
        $mysql = $config->data_user;
        $sql = "SELECT * FROM user_message WHERE user_id=:user_id";
        $param = [":user_id"=>$userId];
        foreach ($mysql->query($sql,$param) as $rows){
            $list[] = $rows;
        }
        $lists = [];
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $lists[$key]["user_message_id"] = $val["user_message_id"];
                $lists[$key]["title"] = $val["title"];
                $lists[$key]["start_time"] = date("Y-m-d",$val["start_time"]);
                $lists[$key]["stop_time"] = date("Y-m-d",$val["stop_time"]);
                $lists[$key]["content"] = $val["content"];
            }

            $websocketAdapter->send($id,'Message/UserMessage', $lists);
        }
    }
}