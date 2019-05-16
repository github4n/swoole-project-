<?php
namespace App\Websocket\User\Recharge;

use App\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 快捷支付列表
 *  User/Recharge/SimpleList
 *
 * */

class SimpleList extends CheckLogin
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
        $sql = "SELECT route_id,passage_id,passage_name,pay_url FROM deposit_route_simple_intact where  acceptable=1 AND passage_acceptable=1 AND find_in_set(:layer_id,layer_id_list)";
        $param = [":layer_id"=>$layer_id];
        $list = iterator_to_array($mysql->query($sql,$param));
        $context->reply(["status"=>200,"msg"=>"获取成功","list"=>$list]);
    }
}