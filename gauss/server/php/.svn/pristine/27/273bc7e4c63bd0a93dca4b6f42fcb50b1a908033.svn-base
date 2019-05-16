<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 19-1-17
 * Time: 上午9:00
 */

namespace Plat\Task\Analysis;
use Lib\Task\Context;
use Lib\Config;
use Lib\Task\IHandler;

class Index implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['time' => $time] = $context->getData();
        $adapter = $context->getAdapter();
        $mysqlReport = $config->data_analysis;
        $daily = date('Ymd',$time);
        $sql = "select sum(bet_all) as bet_all,sum(user_all) as user_all,sum(profit_all) as profit_all from daily_site where daily = '$daily'";
        $bet_all = 0;
        $user_all = 0;
        $profit_all = 0;
        foreach ($mysqlReport->query($sql) as $item)
        {
            $bet_all = $item['bet_all'];
            $user_all = $item['user_all'];
            $profit_all = $item['profit_all'];
        }

        $adapter->plan('NotifyClient',['bet_all'=>$bet_all,'user_all'=>$user_all,'profit_all'=>$profit_all],time());
    }
}