<?php
namespace App\Websocket\User\SafeCenter;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--安全中心--验证旧密码
 * User/SafeCenter/ConfirmPwd {"old_password":"012345"}
 * */

class ConfirmPwd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }
        
        $data = $context->getData();
        $mysql = $config->data_user;
        $password = $data["old_password"];
        $preg = '/^[0-9]{6}$/';
        if(!preg_match($preg,$password)){
            $context->reply(['status' => 206, 'msg' => '请输入6位交易密码']);
            return;
        }
        $sql = "SELECT * FROM bank_info WHERE user_id = :user_id AND password_hash = sha1(concat(password_salt,sha1(:password)))";
        $param = [":user_id"=>$context->getInfo("UserId"),":password"=>$password];
        try{
            foreach ($mysql->query($sql,$param) as $rows){
                $info = $rows;
            }
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"密码错误"]);
            throw new \PDOException($e);
        }
        if(empty($info)){
            $context->reply(["status"=>400,"msg"=>"密码错误"]);
        } else{
            $context->reply(["status"=>200,"msg"=>"旧密码正确"]);
        }

    }
}