<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-20
 * Time: 上午9:29
 */

namespace App\Websocket\User\Recharge;
use App\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;
/*
 *第三方支付列表
 * User/Recharge/ThirdPartyList
 */
class ThirdPartyList extends CheckLogin
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
        $sql = "SELECT route_id,passage_id,passage_name,risk_control,account_number,layer_id_list,min_money,max_money 
        FROM deposit_route_gateway_intact WHERE acceptable=1 AND passage_acceptable=1 ";
        $list = [];
        foreach ($mysql->query($sql) as $row){
                if (in_array($layer_id,explode(',',$row['layer_id_list']))) {
                    $list[] = $row;
                }
        }
        $context->reply(['status'=>200,'msg'=>'获取成功','data'=>$list]);
    }
}