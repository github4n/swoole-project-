<?php
namespace Site\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class NotifyClient implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['path' => $path, 'data' => $data] = $context->getData();
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_site);
        foreach ($websocketAdapter->queryClients() as $clientId) {
            $websocketAdapter->send($clientId, $path, $data);
        }
    }
}
