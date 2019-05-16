<?php

namespace Site\Task\Ip;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
/*
 * Gather.php
 * @description   通知平台记录ip任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Ip/Gather 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class Gather implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $ipdata] = $context->getData();
        $adapter = $context->getAdapter();
        $adapter->plan('NotifyPlat', ['path' => 'Ip/Gather', 'data' => ["data"=>$ipdata]]);
    }
}