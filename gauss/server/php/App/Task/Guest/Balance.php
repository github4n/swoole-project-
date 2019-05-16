<?php
namespace App\Task\Guest;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Balance implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['user_id' => $userId,'id'=>$id] = $context->getData();
        $deal_mysql = $config->data_guest;
        $sql = "SELECT money,deposit_audit,coupon_audit FROM account WHERE user_id = :user_id";
        $param = [":user_id"=>$userId];
        $info = [];
        foreach ($deal_mysql->query($sql,$param) as $row){
            $info = $row;
        }
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
        $websocketAdapter->send($id,'User/Balance', $info);
    }
}
