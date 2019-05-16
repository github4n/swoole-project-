<?php
namespace Site\Websocket;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

class Disconnect implements IHandler{
    public function onReceive(Context $context, Config $config)
    {
        //用户掉线更新掉线时间
        $mysql = $config->data_staff;
        $sql = "CALL staff_session_lose(:client_id)";
        $param = [":client_id"=>$context->clientId()];
        $mysql->execute($sql,$param);
        $context->reply(['connect time'=>"Disconnect {$context->clientId()}\n"]);
       
    }
}