<?php
namespace Plat\Websocket;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

class Hello implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $context->setInfo('username','张三');
        $context->setInfo('userid',12);
        $context->reply(['hello time' => time(),'name'=>$context->getInfo('username'),'id'=>$context->getInfo('userid')]);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Delay', ['id' => $context->clientId()], time() + 5);
    }
}