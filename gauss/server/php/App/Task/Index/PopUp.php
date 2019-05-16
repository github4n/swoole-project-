<?php
namespace App\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class PopUp implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['popup'=>$popup] = $context->getData();
        $adapter = $context->getAdapter();

        $cache = $config->cache_app;
        $json = json_encode($popup, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->hset('Index', "PopUp", $json);

        $adapter->plan('NotifyClient', ['path' => 'Index/PopUp' , 'data' => $popup]);
    }
}
