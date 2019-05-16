<?php
namespace Plat\Websocket;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

abstract class CheckLogin implements IHandler{
    abstract function onReceiveLogined(Context $context,Config $config);

    public function onReceive(Context $context, Config $config){
        // check login
        if(empty($context->getInfo('adminId'))) {
            $context->reply(["status"=>201,'msg' => '你还没有登录请登录']);
            return;
        }

        $this->onReceiveLogined($context,$config);
    }
    //获取所有站点
    public function getallsite(Context $context,Config $config){
         $mysql = $config->data_admin;
         $sql = "SELECT * FROM site";
         foreach ($mysql->query($sql) as $siterows){
             $sitelist[] = $siterows;
         }
         $context->setInfo('SiteList', json_encode($sitelist));
        return $sitelist;
    }
    public function getAllGame(Context $context,Config $config){
        $mysql = $config->data_public;
        //彩种
        $gamesql = "SELECT game_key,game_name FROM lottery_game";
        foreach ($mysql->query($gamesql) as $rows){
            $gamelist[]= $rows;
        }
        $context->setInfo('LayerList', json_encode($gamelist));
        return $gamelist;
    }
    //统计在线人数
    public function onlineNum(Context $context,Config $config){
        $mysql = $config->data_admin;
        //统计在线人数
        $sql="SELECT admin_id FROM admin_session";
        $online_num = $mysql->execute($sql);
        return $online_num;
    }
}