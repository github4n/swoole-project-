<?php
namespace Site\Websocket\AgentRebate\AgentLayer;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * LayerAuth.php
 *
 * @description   代理层级设置--代理权限
 * @Author  rose
 * @date  2019-04-07
 * @links 参数：AgentRebate/AgentLayer/LayerAuth
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */

class LayerAuth extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        
        $StaffGrade = $context->getInfo("StaffGrade");
        if($StaffGrade != 0){
            $context->reply(["status"=>203,"msg" =>"当前账号没有操作权限"]);
            return;
        }
        $mysql = $config->data_user;
        $sql = "SELECT operate_key,operate_name FROM operate WHERE require_permit=3 OR require_permit=2";
        $agent_layer = iterator_to_array($mysql->query($sql));
        $context->reply(["status"=>200,"msg"=>"获取成功","list"=>$agent_layer]);
    }
}