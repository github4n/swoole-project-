<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 19-3-28
 * Time: 上午10:09
 */

namespace Plat\Task\Analysis;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class SiteExternal implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['time' => $time,'site_key' => $site_key] = $context->getData();
        $daily = intval(date('Ymd', $time));
        $mysql = $config->data_admin;
        $mysqlAnalysis = $config->data_analysis;
        $sql = 'select site_name from site where site_key=:site_key';
        $site_name = '';
        foreach ($mysql->query($sql,[':site_key' => $site_key]) as $row) {
            $site_name = $row['site_name'];
        }
        $siteReport = $config->__get('data_' . $site_key . '_report');
        $site_sql = 'select category_key,interface_key,game_key,game_name,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,sum(profit_amount) as profit_amount from daily_user_external where daily =:daily group by category_key,interface_key,game_key,game_name';
        $generator = $siteReport->query($site_sql,[':daily' => $daily]);
        $mysqlAnalysis->daily_site_external->import($generator, [
            'daily' => $daily,'site_key' => $site_key,'site_name' => $site_name], 'replace');


    }
}