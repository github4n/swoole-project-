<?php
namespace Plat\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Initialize implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $today = strtotime('today');
        $adapter = $context->getAdapter();
        $mysql = $config->data_public;
        $cache = $config->cache_plat;

        // period history
        $start = $today - 10 * 86400;
        $stop = $today + 10 * 86400;
        $sql = 'select game_key from lottery_game';
        foreach ($mysql->query($sql) as $row) {
            $game_key = $row['game_key'];
            $adapter->plan('Lottery/Period', ['game_key' => $game_key, 'start' => $start, 'stop' => $stop]);
        }

        foreach ($config->site_list as $site) {
            $adapter->plan('InitializeSite', ['site_key' => $site], time(), 1);
            $adapter->plan('ListenSite', ['site_key' => $site], time() + 60, 7);    
        }
        //彩票信息
        $sql = "select game_key,game_name,model_key,model_name from lottery_game_intact ";
        foreach ($mysql->query($sql) as $row){
            $cache->hset("AllGame",$row["game_key"],$row["game_name"]);
            $cache->hset("Model",$row["model_key"],$row["model_name"]);
        }
    }
}
