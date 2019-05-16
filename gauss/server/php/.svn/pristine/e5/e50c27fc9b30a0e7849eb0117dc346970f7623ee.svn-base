<?php
namespace App\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Banner implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['banner'=>$banner] = $context->getData();
        $adapter = $context->getAdapter();

        $cache = $config->cache_app;
        $json = json_encode($banner, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->hset('Index', "Banner", $json);

        $adapter->plan('NotifyClient', ['path' => 'Index/Banner' , 'data' => $banner]);
    }
}
