<?php
namespace App\Task\User;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Notice implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $data] = $context->getData();
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('NotifySite', ['path' => 'User/Notice', 'data' => ["data" => $data]]);
    }

}
