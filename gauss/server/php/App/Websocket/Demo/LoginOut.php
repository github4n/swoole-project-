<?php
namespace App\Websocket\Demo;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 退出登录
 * Demo/LoginOut
 * 
 * */

class LoginOut implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $userId = $context->getInfo("UserId");
        $sql = "DELETE FROM guest_session WHERE user_id=:user_id";
        $sql1 = "delete from guest_user where user_id=:user_id";
        $sql2 = "delete from account where user_id=:user_id";
        $sql3 = "delete from bet_normal where user_id=:user_id";
        $sql4 = "delete from bet_chase where user_id=:user_id";
        $param = [":user_id"=>$userId];
        try{
            $mysql = $config->data_guest;
            $mysql->execute($sql,$param);
            $mysql->execute($sql1,$param);
            $mysql->execute($sql2,$param);
            $mysql->execute($sql3,$param);
            $mysql->execute($sql4,$param);

        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '退出失败']) ;
            throw new \PDOException($e);
        }

        //删除相关的redis信息
        $cache = $config->cache_app;
        $clientKey = 'websocket:client:' . $context->clientId();
        $cache->hdel($clientKey,"UserId");
        $cache->hdel($clientKey,"GuestId");
        $cache->hdel($clientKey,"UserKey");
        $cache->hdel($clientKey,"DealKey");
        $cache->hdel($clientKey,"LayerId");
        $cache->hdel($clientKey,"AccountName");
        $context->reply(['status' => 200, 'msg' => '退出成功']);

    }
}
/*
 * $context->setInfo("GuestId",$userId);
            $context->setInfo('UserKey',$user_info['user_key']);
            $context->setInfo('UserId',$userId);
            $context->setInfo('DealKey','guest');
            $context->setInfo('LayerId',100) ;
            $context->setInfo('AccountName','游客') ;
 *
 *
 * */