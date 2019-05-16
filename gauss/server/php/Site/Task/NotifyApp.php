<?php
namespace Site\Task;

use Lib\Config;
use Lib\Task\Adapter;
use Lib\Task\Context;
use Lib\Task\IHandler;

class NotifyApp implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['path' => $path, 'data' => $data] = $context->getData();
        foreach ($config->app_list as $app) {
            if (isset($data['app']) && in_array($data['app'],$config->app_list)) {
                $cacheKey = 'cache_'.$data['app'];
                $adapter = new Adapter($config->$cacheKey);
                $adapter->plan($path, $data);
                break;
            } else {
                $cacheKey = 'cache_' . $app;
                $adapter = new Adapter($config->$cacheKey);
                $adapter->plan($path, $data);
            }
        }
    }
}
