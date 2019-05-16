<?php
namespace Site\Websocket;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

class Connect implements IHandler{
    public function onReceive(Context $context, Config $config)
    {
        //检测是否关闭
        $mysqlStaff = $config->data_staff;
        $sql = "select int_value from site_setting where setting_key = 'site_status'";
        foreach ($mysqlStaff->query($sql) as $row){
            $status = $row["int_value"];
        }
        if($status == 3){
            $context->reply(['status' => 500,"msg"=>"维护中"]);
            return;
        }
        $context->reply(['status' => 400,"msg"=>"正常",'connect site time'=>time()]);
        //存彩种  和赔率的redis
        $mysql = $config->data_public;
        $user_mysql = $config->data_user;
        $sql = "SELECT model_key,model_name,game_key,game_name FROM lottery_game_intact";
        $list = iterator_to_array($mysql->query($sql));
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $game = [];
                $game["game_key"] = $val["game_key"];
                $game["game_name"] = $val["game_name"];
                $context->setInfo($val["game_key"], $val["game_name"]);
                $game_list[] = $game;
            }
        }
        $context->setInfo("GameList",json_encode($game_list));
        $context->setInfo("GameModelList",json_encode($list));
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
        $sql = "SELECT model_key,model_name FROM lottery_model";
        foreach ($mysql->query($sql) as $rows){
            $model_list[] = $rows;
        }
        $context->setInfo("ModelList",json_encode($model_list));
        $sql = "SELECT layer_id,layer_name FROM layer_info";
        $layer_list = iterator_to_array($user_mysql->query($sql));
        if(!empty($layer_list)){
            foreach ($layer_list as $k=>$v){
                $context->setInfo($v["layer_id"], $v["layer_name"]);
            }
        }
    }
}