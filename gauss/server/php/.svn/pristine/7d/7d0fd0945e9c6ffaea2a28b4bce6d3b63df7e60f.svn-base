<?php
namespace App\Websocket\Login;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;

/*
 *  Login/LoginOut
 * */

class LoginOut extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $userId = $context->getInfo("UserId");
        $sql = "DELETE FROM user_session WHERE user_id=:user_id";
        $param = [":user_id"=>$userId];
        try{
            $mysql = $config->data_user;
            $mysql->execute($sql,$param);
            //记录退出日志
            $sql = 'INSERT INTO operate_log SET user_id=:user_id, operate_key=:operate_key, detail=:detail';
            $params = [
                ':user_id' => $userId,
                ':operate_key' => 'self_logout',
                ':detail' =>"退出登录",
            ];
            $mysql->execute($sql, $params);

        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '连接超时,请重新退出']);
            throw new \PDOException($e);
        }
        //删除相关的redis信息
        $cache = $config->cache_app;
        $clientKey = 'websocket:client:' . $context->clientId();
        $cache->hdel($clientKey,"UserId");
        $cache->hdel($clientKey,"UserKey");
        $cache->hdel($clientKey,"LoginDevice");
        $cache->hdel($clientKey,"LayerId");
        $cache->hdel($clientKey,"DealKey");
        $cache->hdel($clientKey,"InviteCode");
        $cache->hdel($clientKey,"AccountName");
        $context->reply(['status' => 200, 'msg' => '退出成功']);
    }
}