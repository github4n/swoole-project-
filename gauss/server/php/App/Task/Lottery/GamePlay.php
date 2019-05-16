<?php

namespace App\Task\Lottery;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class GamePlay implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['game_key' => $game_key, 'pushData' => $pushData] = $context->getData();
        $adapter = $context->getAdapter();
        $cache = $config->cache_app;
        $json = json_encode($pushData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cache->hset('GamePlay', $game_key, $json);
        //$adapter->plan('NotifyClient', ['path' => 'Lottery/GamePlay?' . $game_key, 'data' => $pushData]);
    }
}