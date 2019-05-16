<?php
namespace Plat\Task\Analysis;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Tax implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['site_key' => $site_key, 'time' => $time] = $context->getData();

        $mysql = $config->data_admin;
        $site_name = $site_key;
        $sql = 'select site_name from site where site_key=:site_key';
        foreach ($mysql->query($sql, ['site_key' => $site_key]) as $row) {
            $site_name = $row['site_name'];
        }
        $sql = "select month_rent from site_rent_config where site_key=:site_key";
        $tax_rent = 0;
        foreach ($mysql->query($sql, ['site_key' => $site_key]) as $row) {
            $tax_rent = $row['month_rent'];
        }
        $siteReport = $config->__get('data_' . $site_key . '_report');
        $analysis = $config->data_analysis;
        $monthly = intval(date('Ym', $time));
        $sql = 'select sum(profit_lottery) as profit_lottery,sum(bet_lottery) as wager_lottery,sum(bonus_lottery) as bonus_lottery,'.
         'sum(profit_video) as profit_video,sum(bet_video) as wager_video,sum(bonus_video) as bonus_video,' .
            'sum(profit_game) as profit_game,sum(bet_game) as wager_game,sum(bonus_game) as bonus_game,'.
            'sum(profit_sports) as profit_sports,sum(bet_sports) as wager_sports,sum(bonus_sports) as bonus_sports,'.
            'sum(profit_cards) as profit_cards,sum(bet_cards) as wager_cards,sum(bonus_cards) as bonus_cards '.
            'from monthly_site where monthly=:monthly and site_key=:site_key';

        foreach ($analysis->query($sql, [':monthly' => $monthly,':site_key'=>$site_key]) as $row){
            $generator = $row;
            
        }
        $tax_total = 0;
        foreach (["lottery","game","video","sports","cards"] as $item){
            $tax_category = 0 ;
            $tax_sql = "select range_max,tax_rate from site_tax_config where site_key=:site_key and category=:category";
            $taxList = iterator_to_array($mysql->query($tax_sql,[":site_key"=>$site_key,":category"=>$item]));
            $generator["setting_".$item] = json_encode($taxList) ;
            foreach ($taxList as $key=>$val){
                if($generator["profit_".$item] > 0 ){
                    $tax_category += (($generator["profit_".$item]-$val["range_max"])>=0 ? ($generator["profit_".$item]-$val["range_max"]) : 0)*$val["tax_rate"] ;
                }else{
                    $tax_category = 0;
                }
            }
            $generator["tax_".$item] = $tax_category;
            $tax_total += $tax_category;
        }
        //获取外接口的提成及比例
        $tax_total += $tax_rent;
        $generator["tax_total"] = $tax_total;

        $allDta[] = $generator;
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
        foreach ($websocketAdapter->queryClients() as $clientId) {
            $websocketAdapter->send($clientId, "222", $allDta);
        }

        if (!empty($allDta)) {
            $analysis->monthly_tax->load($allDta, [
                'monthly' => $monthly, 'site_key' => $site_key, 'site_name' => $site_name, 'tax_rent'=>$tax_rent
            ], 'replace');
            $siteReport->monthly_tax->load($allDta, [
                'monthly' => $monthly,  'tax_rent'=>$tax_rent
            ], 'replace');
        }
        
    }
}
