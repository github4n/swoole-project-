<?php

namespace Plat\Task\Ip;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
class Gather implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $ipdata] = $context->getData();
        $publicMysql = $config->data_public;
        $publicMysql->ip_address->load([$ipdata],[],'ignore');

    }
}