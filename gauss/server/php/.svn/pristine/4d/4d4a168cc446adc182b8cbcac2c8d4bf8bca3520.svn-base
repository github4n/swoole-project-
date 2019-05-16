<?php
namespace App\Websocket\User\SafeCenter;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--安全中心--修改提现密码
 * User/SafeCenter/ModifyCashPwd {"password":"000123"}
 * */

class ModifyCashPwd extends CheckLogin
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
        $password = $data["password"];
        //验证参数
        $preg = '/^[0-9]{6}$/';
        if(!preg_match($preg,$password)) {
            $context->reply(['status' => 206, 'msg' => '新密码格式不正确']);
            return;
        }
        $sql = "UPDATE bank_info SET password_hash=:password_hash WHERE user_id=:user_id";
        $param = [":password_hash"=>$password,":user_id"=>$context->getInfo("UserId")];
        try{
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"修改失败"]);
            throw new \PDOException($e);
        }
        $context->reply(["status"=>200,"msg"=>"修改成功"]);
    }
}