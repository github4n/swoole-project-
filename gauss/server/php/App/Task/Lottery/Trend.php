<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-14
 * Time: 下午6:27
 */

namespace App\Task\Lottery;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
class Trend implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['game_key' => $game_key, 'pushData' => $pushData] = $context->getData();
        $cache = $config->cache_app;
        $json = json_encode($pushData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        //$cache->hset('Trend', $game_key, $json);
    }
}
