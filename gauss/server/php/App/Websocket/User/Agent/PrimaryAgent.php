<?php
namespace App\Websocket\User\Agent;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--代理中心--下级列表--一级代理
 * User/Agent/PrimaryAgent 
 * */

class PrimaryAgent extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }

        $data = $context->getData();
        $page = isset($data["page"]) ? intval($data["page"]) : 1;
        $num = isset($data["num"]) ? intval($data["num"]) : 15;
        $limit = " LIMIT ".($page-1)*$num.",".$num;
        $mysql = $config->data_user;
        $sql = "SELECT user_key,register_time,login_time FROM user_info_intact WHERE broker_1_id=:broker_1_id".$limit;
        $total_sql = "SELECT user_id FROM user_info_intact WHERE broker_1_id=:broker_1_id";
        $param = [":broker_1_id"=>$context->getInfo("UserId")];
        $lists = array();
        $total = 0;
        try{
            foreach ($mysql->query($sql,$param) as $rows){
                $lists[] = $rows;
            }
            $total = $mysql->execute($total_sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"获取失败"]);
        }
        if(!empty($lists)){
            foreach ($lists as $key=>$val){
                $lists[$key]["register_time"] = empty($val["register_time"])?"":date("Y-m-d/H:i:s",$val["register_time"]);
                $lists[$key]["login_time"] = empty($val["login_time"])?"":date("Y-m-d/H:i:s",$val["login_time"]);
            }
        }
        $context->reply(["status"=>200,"msg"=>"获取方式","total"=>$total,"list"=>$lists]);
    }
}