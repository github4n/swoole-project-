<?php
namespace Site\Task\System;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * Setting
 * @description   存站点设置
 * @Author  rose 
 * @date  2019-05-09
 * @links  System/Setting
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class Setting implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $cache = $config->cache_site;
        $mysqlStaff = $config->data_staff;
        $sql = "select scope_staff_id,grade1_bet_rate,grade1_profit_rate,grade1_fee_rate,grade1_tax_rate,grade2_bet_rate,grade2_profit_rate,grade2_fee_rate,grade2_tax_rate,grade3_bet_rate,grade3_profit_rate,grade3_fee_rate,grade3_tax_rate from dividend_setting";
        foreach ($mysqlStaff->query($sql) as $row){
            $cache->hset("SystemSetting",$row["scope_staff_id"],json_encode($row));
        }
    }
}