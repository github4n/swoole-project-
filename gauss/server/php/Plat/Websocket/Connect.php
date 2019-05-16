<?php
namespace Plat\Websocket;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

class Connect implements IHandler{
    public function onReceive(Context $context, Config $config)
    {
        $context->reply(['connect time'=>time()]);
        $mysql = $config->data_public;
        
        $sql = "SELECT model_key,model_name FROM lottery_model";
        foreach ($mysql->query($sql) as $rows){
            $list[] = $rows;
        }
        $context->setInfo("ModelList",json_encode($list));
        $sql = "SELECT game_key,game_name FROM lottery_game";
        $game_list = iterator_to_array($mysql->query($sql));
        if(!empty($game_list)){
            foreach ($game_list as $key=>$val){
                $context->setInfo($val["game_key"], $val["game_name"]);
            }
        }
        $context->setInfo("GameList",json_encode($game_list));
        // 玩法
        $sql = "SELECT play_key,play_name FROM lottery_play";
        foreach ($mysql->query($sql) as $rows){
            $lists[] = $rows;
        }
        if(!empty($lists)){
            foreach ($lists as $k=>$v){
                $context->setInfo($v["play_key"], $v["play_name"]);
            }
        }
    }
}