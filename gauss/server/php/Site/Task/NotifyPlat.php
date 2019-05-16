<?php
namespace Site\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class NotifyPlat implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['path' => $path, 'data' => $data] = $context->getData();

        $cache = $config->cache_site;
        $task = $path . ' ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->lpush('NotifyPlat', $task);
    }
}
