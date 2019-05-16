<?php
namespace App\Websocket\User\Recharge;

use App\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 银行卡列表
 *  User/Recharge/BankRechargeList
 *
 * */

class BankRechargeList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }

        $mysql = $config->data_staff;
        $layer_id = $context->getInfo("LayerId");
        $sql = "SELECT route_id,risk_control,bank_name,bank_branch,account_number,account_name,min_money,max_money FROM deposit_route_bank_intact where  acceptable=1 AND passage_acceptable=1 AND find_in_set(:layer_id,layer_id_list)";
        $param = [":layer_id"=>$layer_id];
        $list = iterator_to_array($mysql->query($sql,$param));
        $context->reply(["status"=>200,"msg"=>"获取成功","list"=>$list]);
    }
}