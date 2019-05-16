<?php
namespace App\Task\Lottery;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Game implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['pushData' => $pushData] = $context->getData();
        $adapter = $context->getAdapter();
        $cache = $config->cache_app;
<<<<<<< .mine
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_app);
        $Game_list = json_decode($cache->hget("LotteryList","GameList"));
        //$websocketAdapter->send($id,'Lottery/Game', $Game_list);
||||||| .r8579
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_app);
        $Game_list = json_decode($cache->hget("LotteryList","GameList"));
        $websocketAdapter->send($id,'Lottery/Game', $Game_list);
=======
        $json = json_encode($pushData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->hset('LotteryList', "GameList", $json);
        $adapter->plan('NotifyClient', ['path' => 'Lottery/Game', 'data' => $pushData]);

>>>>>>> .r8889
    }
}
