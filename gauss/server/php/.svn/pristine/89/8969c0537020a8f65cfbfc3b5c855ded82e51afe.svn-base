<?php
namespace Site\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class AppLoginNotify implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $data] = $context->getData();
        foreach ($config->app_list as $app) {
            $cache = $config->__get('cache_' . $app);
            $clientKey = 'websocket:client:' . $data;
            $cache->hdel($clientKey,"UserId");
            $cache->hdel($clientKey,"UserKey");
            $cache->hdel($clientKey,"LoginDevice");
            $cache->hdel($clientKey,"LayerId");
            $cache->hdel($clientKey,"DealKey");
            $cache->hdel($clientKey,"InviteCode");
            $cache->hdel($clientKey,"AccountName");
        }
        $adapter = $context->getAdapter();
        $adapter->plan('NotifyApp', ['path' => 'AppLogin/Notify', 'data' => ["client_id"=>$data,"data"=>["status" => 800,"msg"=>"该账号已在其他地方登录"]]],time(),1);

    }
}
