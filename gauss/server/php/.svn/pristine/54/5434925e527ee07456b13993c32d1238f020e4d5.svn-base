<?php
namespace App\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Popular implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['popular'=>$popular] = $context->getData();
        $adapter = $context->getAdapter();

        $cache = $config->cache_app;
        $json = json_encode($popular, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->hset('Index', "Popular", $json);

        $adapter->plan('NotifyClient', ['path' => 'Index/Popular' , 'data' => $popular]);
    }
}
