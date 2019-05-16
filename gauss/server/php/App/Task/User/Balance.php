<?php
namespace App\Task\User;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Balance implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['user_id' => $userId,'deal_key'=>$deal_key,'id'=>$id] = $context->getData();
        $deal = "data_".$deal_key;
        $deal_mysql = $config->{$deal};
        $sql = "SELECT money,deposit_audit,coupon_audit FROM account WHERE user_id = :user_id";
        $param = [":user_id"=>$userId];
        $info = [];
        foreach ($deal_mysql->query($sql,$param) as $row){
            $info["money"] = $row["money"];
            $info["deposit_audit"] = $row["deposit_audit"];
            $info["coupon_audit"] = $row["coupon_audit"];
        }
        //更新Report库的用户余额
        if(!empty($info)){
            $sql = "update user_cumulate "."set money = :money where user_id=:user_id";
            $mysqlReport = $config->data_report;
            $mysqlReport->execute($sql,[":money"=>$info["money"],":user_id"=>$userId]);           
            $balance_info = $info;
        }else{
            $balance_info = ["money"=>0,"deposit_audit"=>0,"coupon_audit"=>0];
        }
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
        $websocketAdapter->send($id,'User/Balance', $balance_info);
    }
}
