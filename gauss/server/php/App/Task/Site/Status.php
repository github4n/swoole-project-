<?php
namespace APP\Task\Site;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Status implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['path' => $path, 'data' => $data] = $context->getData();
        
        $context->getAdapter()->plan('NotifyClient', ['path' => 'Website/Status', 'data' => $data ]);
    }
}
