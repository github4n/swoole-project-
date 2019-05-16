<?php
namespace App\Task\Guest;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Delete implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['user_id' => $userId] = $context->getData();
        $cache = $config->cache_app;

        //删除用户缓存信息
        $sql = "SELECT client_id FROM user_session WHERE user_id=:user_id";
        $mysql = $config->data_user;
        foreach ($mysql->query($sql,[":user_id"=>$userId]) as $row){
            $clientKey = 'websocket:client:' . $row['client_id'];
            $cache->hdel($clientKey,"GuestId");
            $cache->hdel($clientKey,"UserId");
            $cache->hdel($clientKey,"DealKey");
        }
        $mysql = $config->data_guest;
        $sql = "DELETE FROM guest_user WHERE user_id=:user_id";
        $mysql->execute($sql,[":user_id"=>$userId]);
        $sql = "DELETE FROM guest_session WHERE user_id=:user_id";
        $mysql->execute($sql,[":user_id"=>$userId]);
        $sql = "DELETE FROM account WHERE user_id=:user_id";
        $mysql->execute($sql,[":user_id"=>$userId]);
        $sql = "DELETE FROM bet_normal WHERE user_id=:user_id";
        $mysql->execute($sql,[":user_id"=>$userId]);
        $sql = "DELETE FROM bet_chase WHERE user_id=:user_id";
        $mysql->execute($sql,[":user_id"=>$userId]);

    }
}
