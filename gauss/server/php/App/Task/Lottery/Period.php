<?php
namespace App\Task\Lottery;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Period implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['game_key' => $game_key, 'pushData' => $pushData] = $context->getData();

        $adapter = $context->getAdapter();

        $cache = $config->cache_app;
        $json = json_encode($pushData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->hset('Period', $game_key, $json);

<<<<<<< .mine
        //$adapter->plan('NotifyClient', ['path' => 'Lottery/Period?' . $game_key, 'data' => $pushData]);
||||||| .r8579
        $adapter->plan('NotifyClient', ['path' => 'Lottery/Period?' . $game_key, 'data' => $pushData]);
=======
        //检测是否关闭
        $mysqlStaff = $config->data_staff;
        $sql = "select int_value from site_setting where setting_key = 'site_status'";
        foreach ($mysqlStaff->query($sql) as $row){
            $status = $row["int_value"];
        }
        if($status == 2 || $status == 3){
            return;
        }
        $adapter->plan('NotifyClient', ['path' => 'Lottery/Period?' . $game_key, 'data' => $pushData]);
>>>>>>> .r8889
    }
}
