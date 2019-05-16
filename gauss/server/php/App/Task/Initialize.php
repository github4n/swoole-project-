<?php
namespace App\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Initialize implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $cache = $config->cache_app;
        $mysql = $config->data_public;
        $mysqlStaff = $config->data_staff;
        $sql = "SELECT model_key,game_key,game_name FROM lottery_game";
        $list = iterator_to_array($mysql->query($sql));
        if(!empty($list)){
            foreach ($list as $key=>$vals){
                $cache->hset("Lottery",$vals["game_key"], $vals["game_name"]);
            }
        }
        $play_sql = "SELECT model_key,play_key,play_name FROM lottery_play";
        $play_list = iterator_to_array($mysql->query($play_sql));
        if(!empty($play_list)){
            foreach ($play_list as $k=>$vs){
                $cache->hset("Lottery",$vs["model_key"].'-'.$vs["play_key"],$vs["play_name"]);
            }
        }
        $sql = "SELECT model_key,model_name FROM lottery_model";
        $model_list = iterator_to_array($mysql->query($sql));
        if(!empty($model_list)){
            foreach ($model_list as $key=>$val){
                $cache->hset("Model",$val["model_key"], $val["model_name"]);
            }
        }
        $cache->hset("ModelList","ModelList",json_encode($model_list));
        $sql = "SELECT play_key,win_key,win_name FROM lottery_win ";
        $win_list = iterator_to_array($mysql->query($sql));
        if(!empty($win_list)){
            foreach ($win_list as $k=>$v){
                $cache->hset("WinList",$v["play_key"]."-".$v["win_key"],$v["win_name"]);
            }
        }
        //获取用户出入款的优惠比例及金额
        $sql = "select setting_key,dbl_value from site_setting where data_type=1";
        foreach ($mysqlStaff->query($sql) as $row){
            $cache->hset("SiteSetting",$row["setting_key"],$row["dbl_value"]);
        }
        $sql = "select setting_key,int_value from site_setting where data_type=0";
        foreach ($mysqlStaff->query($sql) as $row){
            $cache->hset("SiteSetting",$row["setting_key"],$row["int_value"]);
        }
        //获取彩票数据
        $sql = "SELECT model_key,game_key FROM lottery_game where acceptable = 1";
        $gameList = iterator_to_array($mysqlStaff->query($sql));
        if(!empty($gameList)){
            foreach ($gameList as $key=>$v){
                $gameList[$key]["game_name"] = $cache->hget("Lottery",$v["game_key"]);
            }
        }
        $adapter = $context->getAdapter();
        $adapter->plan("Lottery/Game",['pushData' => $gameList]);
    }
}
