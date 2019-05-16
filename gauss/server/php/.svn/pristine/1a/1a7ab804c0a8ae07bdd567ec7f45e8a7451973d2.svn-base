<?php
namespace App\Task\User;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class UserInfo implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['user_id' => $userId,'id'=>$id] = $context->getData();
        $mysql = $config->data_user;
        $sql = "SELECT user_key,account_name,layer_name,invite_code FROM user_info_intact WHERE user_id = :user_id";
        $param = [":user_id"=>$userId];
        $info = [];
        $user_ifo = [];
        foreach ($mysql->query($sql,$param) as $row){
            $info = $row;
        }
        if(!empty($info)){
            $user_ifo["user_key"] = $info["user_key"];
            $user_ifo["account_name"] = empty($info["account_name"]) ? "" : $info["account_name"];
            $user_ifo["layer_name"] = $info["layer_name"];
            $user_ifo["invite_code"] = empty($info["invite_code"]) ? "" : $info["invite_code"];
        }
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
        $websocketAdapter->send($id,'User/UserInfo', $user_ifo);
    }
}
