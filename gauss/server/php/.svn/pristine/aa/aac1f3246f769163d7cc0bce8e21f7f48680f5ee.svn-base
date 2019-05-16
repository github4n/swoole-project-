<?php
namespace App\Websocket\User\Recharge;

use App\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 银行详细信息
 * User/Recharge/BankInfo {"route_id":3}
 *
 * */

class BankInfo extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }
        
        $data = $context->getData();
        $route_id = $data["route_id"];
        if(!is_numeric($route_id)){
            $context->reply(["status"=>205,"msg"=>"参数错误"]);
            return;
        }
         $mysql = $config->data_staff;
         $sql = "SELECT min_money,max_money,bank_name,bank_branch,account_number,account_name FROM deposit_route_bank_intact where route_id=:route_id";
         $param = [":route_id"=>$route_id];
         $info = [];
         foreach ($mysql->query($sql,$param) as $row){
             $info = $row;
         }
         $context->reply(["status"=>200,"msg"=>"获取成功","info"=>$info]);
    }
}