<?php
namespace Plat\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class InitializeSite implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['site_key' => $site_key] = $context->getData();

        $mysqlPublic = $config->data_public;
        $sql = 'select model_key,game_key,official from lottery_game';
        $adminSiteGames = $gameOffice  = [];
        foreach ($mysqlPublic->query($sql) as $row) {
            $adminSiteGames[] = [
                'model_key' => $row['model_key'],
                'game_key' => $row['game_key'],
            ];
            $gameOffice[$row['game_key']] = $row['official'];
        }
        $sql = 'select play_key,model_key,game_key from lottery_game_play_intact';
        $adminSitePlays = $siteGame = [];
        foreach ($mysqlPublic->query($sql) as $row) {
            $adminSitePlays[] = [
                'game_key' => $row['game_key'],
                'model_key' => $row['model_key'],
                'play_key'=>$row["play_key"]
            ];
            $siteGame[$row['model_key']] = $row['game_key'];
        }
        $sql = "select category_key,game_key,interface_key from external_game";
        $adminExternalGame = [];
        foreach ($mysqlPublic->query($sql) as $row){
            $adminExternalGame[] = [
                'category_key' => $row['category_key'],
                'game_key' => $row['game_key'],
                'interface_key' => $row["interface_key"],
            ];
        }

        $mysqlAdmin = $config->data_admin;
        $mysqlAdmin->site_rent_config->load([[
            'site_key' => $site_key, 'month_rent' => 100000,
        ]], [], 'ignore');
        foreach (['lottery', 'video', 'game', 'sports', 'cards'] as $category) {
            $mysqlAdmin->site_tax_config->load([
                ['range_max' => 1000000, 'tax_rate' => 10],
                ['range_max' => 2000000, 'tax_rate' => 9],
                ['range_max' => 5000000, 'tax_rate' => 8],
                ['range_max' => 10000000, 'tax_rate' => 7.5],
                ['range_max' => 20000000, 'tax_rate' => 7],
                ['range_max' => 50000000, 'tax_rate' => 6.5],
                ['range_max' => 100000000, 'tax_rate' => 6],
            ], [
                'site_key' => $site_key, 'category' => $category,
            ], 'ignore');
        }
        $mysqlAdmin->site_game->load($adminSiteGames, [
            'site_key' => $site_key, 'acceptable' => 1,
        ], 'ignore');
        $mysqlAdmin->site_play->load($adminSitePlays, [
            'site_key' => $site_key, 'acceptable' => 1,
        ], 'ignore');
        $mysqlAdmin->site_external_game->load($adminExternalGame, [
            'site_key' => $site_key, 'acceptable' => 1,
        ], 'ignore');
        $mysqlStaff = $config->__get('data_' . $site_key . '_staff');

        $sql = 'select model_key,game_key,acceptable,rebate_max from site_game where site_key=:site_key';
        $params = ['site_key' => $site_key];
        $siteGames = [];
        foreach ($mysqlAdmin->query($sql, $params) as $row) {
            $siteGames[] = [
                'model_key' => $row['model_key'],
                'game_key' => $row['game_key'],
                'acceptable' => $row['acceptable'],
                'rebate_max' => $row['rebate_max'],
                'official' => $gameOffice[$row['game_key']],
            ];
        }
        $mysqlStaff->lottery_game->load($siteGames, [], 'ignore');

        $sql = 'select model_key,play_key,game_key,acceptable,bet_min,bet_max from site_play where site_key=:site_key';
        $sitePlays = $siteModels = [];
        foreach ($mysqlAdmin->query($sql, $params) as $row) {
            $sitePlays[] = [
                'game_key' => $row['game_key'],
                'play_key' => $row['play_key'],
                'acceptable' => $row['acceptable'],
                'bet_min' => $row['bet_min'],
                'bet_max' => $row['bet_max'],
            ];
            $siteModels[$row['play_key']] = $row['model_key'];

        }
        $mysqlStaff->lottery_game_play->load($sitePlays, [], 'ignore');

        $sql = "select category_key,game_key,acceptable,interface_key from site_external_game where site_key=:site_key";
        $adminExternalGame = [];
        foreach ($mysqlAdmin->query($sql,$params) as $row){
            $adminExternalGame[] = [
               "category_key"=>$row["category_key"],
               "game_key"=>$row["game_key"],
               "acceptable"=>$row["acceptable"],
                "interface_key"=>$row["interface_key"],
            ];
        }
        $mysqlStaff->external_game->load($adminExternalGame, [], 'ignore');
        
        $sql = "select model_key,game_key,play_key,win_key,suggest_bonus_rate from lottery_game_win_intact";
        $adminWin = [];
        foreach ($mysqlPublic->query($sql) as $row){
            $adminWin[] = [
                'model_key'=>$row['model_key'],
                'game_key'=>$row['game_key'],
                'play_key'=>$row['play_key'],
                'win_key'=>$row['win_key'],
                'bonus_rate'=>$row['suggest_bonus_rate'],
            ];
        }
        $mysqlAdmin->site_win->load($adminWin, [
            'site_key' => $site_key,
        ], 'ignore');
        $sql = 'select game_key,play_key,win_key,bonus_rate from site_win where site_key=:site_key ';
        $generator = $mysqlAdmin->query($sql,$params);
        $mysqlStaff->lottery_game_win->import($generator, [], 'ignore');

        $sql = "select status from site where site_key=:site_key";
        $siteStatus = [];
        foreach ($mysqlAdmin->query($sql,$params) as $row){
            $siteStatus[] = [
                'setting_key'=> 'site_status',
                'description'=>'站点状态： 0-开放，1-停止交易，2-关闭前台，3-关闭前后台',
                'data_type'=> 0,
                'int_value' => $row['status'] ,
                'dbl_value' =>0,
                'str_value' =>''
            ];
        }
        $mysqlStaff->site_setting->load($siteStatus, [], 'ignore');

        $sql = "select site_name from site where site_key=:site_key";
        $siteName = [];
        foreach ($mysqlAdmin->query($sql,$params) as $row){
            $siteName[] = [
                'setting_key'=> 'site_name',
                'description'=>'站点名称',
                'data_type'=> 2,
                'int_value' => 0 ,
                'dbl_value' =>0,
                'str_value' =>$row["site_name"]
            ];
        }
        $mysqlStaff->site_setting->load($siteName, [], 'ignore');
    }
}
