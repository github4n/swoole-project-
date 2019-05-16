<?php
namespace Plat\Task;

use Lib\Task\IHandler;
use Lib\Task\Context;
use Lib\Config;

class Delay implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data = $context->getData();
        $wsAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
        $wsAdapter->send($data['id'], 'Delay', ['time' => time(),'id'=>$data['id']]);
    }
}