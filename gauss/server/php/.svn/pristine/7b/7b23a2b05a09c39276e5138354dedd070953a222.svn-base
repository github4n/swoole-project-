<?php
namespace App\Websocket;

use Lib\Config;
use Lib\Websocket\Context;
use Lib\Websocket\IHandler;
/*
 * 除去期号和开奖号码的推送 其他推送需要调整一下
 * */

class ReceiveMessage implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $data = $context->getData();
        /*$random = $data["random"];
        if(empty($random)){
            $context->reply(["status"=>300,"msg"=>"连接失败"]);
            return;
        }
        if($random != $context->getInfo("Random")){
            $context->reply(["status"=>301,"msg"=>"连接失败"]);
            return;
        } */
        //检测是否关闭
        //推送彩票的彩种
        $taskAdapter = new \Lib\Task\Adapter($config->cache_app);
        $cache = $config->cache_app;
        $mysqlPublic=$config->data_public;
         $game_list = json_decode($cache->hget("LotteryList", "GameList"));

         //优惠活动
         $taskAdapter->plan('Message/Activity', ["id" => $context->clientId()], time());
         //最新一期所有彩种的一条开奖记录
         $taskAdapter->plan('Lottery/Record', ['game_list' => $game_list, "id" => $context->clientId()], time());


        $wsAdapter = $context->getAdapter();
        $clientId = $context->clientId();
        $wsAdapter->send($clientId,'Lottery/Lottery', json_decode($cache->hget("LotteryList", "LotteryList")));
        foreach ($cache->hgetall('Number') as $game_key => $json) {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Lottery/Number?' . $game_key, $pushData);
        }
        foreach ($cache->hgetall('Period') as $game_key => $json) {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Lottery/Period?' . $game_key, $pushData);
        }
        foreach ($cache->hgetall('GameWin') as $game_key => $json)
        {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Lottery/GameWin?' . $game_key, $pushData);
        }
        foreach ($cache->hgetall('GamePlay') as $game_key => $json)
        {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Lottery/GamePlay?' . $game_key, $pushData);
        }
        foreach ($cache->hgetall('Trend') as $game_key=> $json) {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Lottery/Trend?' . $game_key, $pushData);
        }

        foreach ($cache->hgetall('Index') as $key => $json) {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Index/' . $key, $pushData);
        }
        
        //新的推送
        $wsAdapter->send($clientId,'Lottery/Game', json_decode($cache->hget("LotteryList", "GameList")));

        foreach ($cache->hgetall('GameNumber') as $game_key => $json) {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Game/Number?' . $game_key, $pushData);
        }
        foreach ($cache->hgetall('LotteryPlay') as $game_key => $json)
        {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Game/LotteryPlay?' . $game_key, $pushData);
        }
        foreach ($cache->hgetall('LotteryWin') as $game_key => $json)
        {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Game/LotteryWin?' . $game_key, $pushData);
        }
        foreach ($cache->hgetall('GameTrend') as $game_key=> $json) {
            $pushData = json_decode($json, true);
            $wsAdapter->send($clientId, 'Game/Trend?' . $game_key, $pushData);
        }

        //获取IP地址
        $ip = $context->getClientAddr();
        $sql = "select ip_net from ip_address where ip_net=:ip_net";
        $ipParam = [":ip_net"=>ip2long($ip)>>8];
        $ipInfo = [];
        foreach ($mysqlPublic->query($sql,$ipParam) as $row){
            $ipInfo = $row;
        }
        if(empty($ipInfo)){
            $taskAdapter->plan('User/Ip', ["ip"=>$ip],time(),9);
        }
    }
}
