<?php
namespace App\Websocket\User\Agent;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--升级代理--成为代理会员
 * User/Agent/Upgrade {"user_id":2}
 * */

class Upgrade extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }

        $mysql = $config->data_user;
        $user_id = $context->getInfo("UserId");
        if(!is_numeric($user_id)){
            $context->reply(["status"=>204,"msg"=>"参数类型错误"]);
            return;
        }
        $invite_codes = $context->getInfo("InviteCode");
        if(!empty($invite_codes)){
            $context->reply(["status"=>205,"msg"=>"已经升级为代理","invite_code"=>$invite_codes]);
            return;
        }
        $sql = "SELECT layer_id,layer_name FROM layer_info WHERE layer_type = 103";
        $layer_id = '';
        $layer_name = '';
        foreach ($mysql->query($sql) as $row){
            $layer_id = $row["layer_id"];
            $layer_name = $row["layer_name"];
        }
        //生成邀请码
        $code = (43112609*$user_id)%99999999;
        $invite_code = sprintf("%08d", $code);
        //更新累计数据表
        $sql = "UPDATE user_cumulate SET layer_id=:layer_id,layer_name=:layer_name,invite_code=:invite_code WHERE user_id=:user_id";
        $params = [
            ":layer_id"=>$layer_id,
            ":layer_name"=>$layer_name,
            ":user_id"=>$user_id,
            ":invite_code"=>$invite_code,
            ];
        $report_mysql = $config->data_report;
        try{
            $report_mysql->execute($sql,$params);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"升级失败"]);
            throw new \PDOException($e);
        }
        //修改基本信息
        $sql = "UPDATE user_info SET layer_id = :layer_id WHERE user_id=:user_id";
        $param = [":user_id"=>$user_id,":layer_id"=>$layer_id];
        try{
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"升级失败"]);
            throw new \PDOException($e);
        }
        $sql = "INSERT INTO invite_info SET invite_code=:invite_code,user_id=:user_id";
        $param = [
            ":invite_code"=>$invite_code,
            ":user_id"=>$user_id,
        ];
        try{
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"升级失败"]);
            throw new \PDOException($e);
        }
        $context->setInfo("InviteCode",$invite_code);
        $context->reply(["status"=>200,"msg"=>"升级成功","invite_code"=>$invite_code]);

    }
}