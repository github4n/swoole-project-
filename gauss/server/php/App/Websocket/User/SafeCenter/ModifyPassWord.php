<?php
namespace App\Websocket\User\SafeCenter;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--安全中心--修改登录密码
 * User/SafeCenter/ModifyPassWord {"old_password":"user123","new_password":"123456","confirm_password":"12345678"}
 * */

class ModifyPassWord extends CheckLogin
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
        $old_password = $data["old_password"];
        $new_password = $data["new_password"];
        $confirm_password = $data["confirm_password"];
        //验证参数
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,40}$/';
        if(!preg_match($preg,$confirm_password)){
            $context->reply(['status' => 206, 'msg' => '新密码格式不正确']);
            return;
        }
        if($new_password !== $confirm_password){
            $context->reply(["status"=>205,"msg"=>"新密码和确认密码不一致"]);
            return;
        }
        //验证旧密码
        $sql = 'CALL user_auth_verify(:user_key, :password)';
        $params = [':user_key' => $context->getInfo("UserKey"), ':password' => $old_password];
        $info = [];
        foreach ($mysql->query($sql,$params) as $row){
            $info = $row;
        }
        if(empty($info)){
            $context->reply(["status"=>206,"msg"=>"旧密码输入错误"]);
            return;
        }
        $sql = "UPDATE user_auth SET password_hash=:password_hash WHERE user_key=:user_key";
        $param = [":password_hash"=>$confirm_password,":user_key"=>$context->getInfo("UserKey")];
        try{
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"修改失败"]);
            throw new \PDOException($e);
        }
        $context->reply(["status"=>200,"msg"=>"修改成功"]);
    }
}