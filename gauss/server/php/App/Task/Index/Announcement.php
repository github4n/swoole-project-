<?php
namespace App\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Announcement implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['announcement'=>$announcement] = $context->getData();
        $adapter = $context->getAdapter();

        $cache = $config->cache_app;
        $json = json_encode($announcement, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->hset('Index', "Announcement", $json);

        $adapter->plan('NotifyClient', ['path' => 'Index/Announcement' , 'data' => $announcement]);
    }
}
