<?php
namespace App\Task\User;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class LoginOut implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['user_id' => $userId,'id'=>$id] = $context->getData();
        $mysql = $config->data_user;
        $sql = "DELETE FROM user_session WHERE user_id=:user_id";
        $param = [":user_id"=>$userId];
        try{
            $mysql->execute($sql,$param);
            //记录退出日志
            $sql = 'INSERT INTO operate_log SET user_id=:user_id, operate_key=:operate_key, detail=:detail';
            $params = [
                ':user_id' => $userId,
                ':operate_key' => 'self_logout',
                ':detail' =>"账号冻结或者网站维护被强制退出登录",
            ];
            $mysql->execute($sql, $params);

        } catch (\PDOException $e) {
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
        $cache->hdel($clientKey,"Auth");
    }
}
