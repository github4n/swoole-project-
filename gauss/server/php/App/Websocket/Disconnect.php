<?php
namespace App\Websocket;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

class Disconnect implements IHandler{
    public function onReceive(Context $context, Config $config)
    {
        //用户掉线更新掉线时间

        $mysql = $config->data_user;
        $sql = "CALL user_session_lose(:client_id)";
        $param = [":client_id"=>$context->clientId()];
        $mysql->execute($sql,$param);

        //游客掉线时间时间更新
        $mysql = $config->data_guest;
        $sql = "CALL guest_session_lose(:client_id)";
        $param = [":client_id"=>$context->clientId()];
        $mysql->execute($sql,$param);
        $context->reply(['connect time'=>"Disconnect {$context->clientId()}\n"]);

    }
}