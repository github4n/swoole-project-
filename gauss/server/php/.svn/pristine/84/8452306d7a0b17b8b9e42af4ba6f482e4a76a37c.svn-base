<?php
namespace App\Websocket;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

abstract class CheckLogin implements IHandler{
    abstract function onReceiveLogined(Context $context,Config $config);

    public function onReceive(Context $context, Config $config){
        // check login (普通验证)
        //检测是否关闭
        $mysqlStaff = $config->data_staff;
        $mysqlUser = $config->data_user;
        $sql = "select int_value from site_setting where setting_key = 'site_status'";
        foreach ($mysqlStaff->query($sql) as $row){
            $status = $row["int_value"];
        }
        if($status == 2 || $status == 3){
            $context->reply(['status' => 500,"msg"=>"维护中"]);
            return;
        }
        $user_id = $context->getInfo("UserId");
        $deal_key = $context->getInfo("DealKey");
        if(empty($user_id) && empty($deal_key)){
            $context->reply(["status"=>201,"msg"=>"没有登录，请先登录"]);
            return;
        }
        //验证账号是否冻结
        $layer_id = $context->getInfo("LayerId");
        $auth_sql = "select operate_key from layer_permit where layer_id = '$layer_id'";
        $authArray = [];
        foreach ($mysqlUser->query($auth_sql) as $row) {
            $authArray[] = $row['operate_key'];
        }
        $clientId = $context->clientId();
        if(!empty($authArray)){
            if(in_array('account_freeze',$authArray)) {
                $context->reply(["status"=>209,"msg"=>"该账号被冻结"]);
                $taskAdapter = new \Lib\Task\Adapter($config->cache_app);
                $taskAdapter->plan('User/LoginOut', ["id"=>$clientId,"user_id"=>$user_id],time(),9);
                return;
            }
        }

        $this->onReceiveLogined($context,$config);
    }
}