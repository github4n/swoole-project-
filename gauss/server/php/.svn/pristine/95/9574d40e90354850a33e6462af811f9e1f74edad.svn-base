<?php
namespace Plat\Websocket;

use Lib\Websocket\Context;
use Lib\Config;

abstract class CheckPermission extends CheckLogin{
    abstract function onReceiveHasStaffPermission(Context $context,Config $config);
    function onReceiveLogined(Context $context,Config $config){
        //判断是否有操作权限
        $this->onReceiveHasStaffPermission($context,$config);
    }
}