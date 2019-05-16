<?php
namespace Plat\Task;

use Lib\Config;
use Lib\Task\Adapter;
use Lib\Task\Context;
use Lib\Task\IHandler;

class NotifySite implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['path' => $path, 'data' => $data] = $context->getData();

        foreach ($config->site_list as $site) {
            if(isset($data["site_key"]) && $data['site_key'] == $site){
                $cacheKey = 'cache_'.$data["site_key"];
                $adapter = new Adapter($config->$cacheKey);
                $adapter->plan($path, $data);
                break;
            }else{
                $cacheKey = 'cache_' . $site;
                $adapter = new Adapter($config->$cacheKey);
                $adapter->plan($path, $data);
            }
        }
    }
}
