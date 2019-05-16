<?php
namespace App\Websocket\User\Agent;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;
/*
 * 我的--代理中心--下级列表--新增下级
 * User/Agent/AddAgent {"user_key":"ceshi201","user_password":"ceshi123"}
 * register_device 注册设备类型：0-PC，1-手机浏览器，2-苹果手机app，3-安卓手机app
 * */

class AddAgent extends CheckLogin
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
        //验证当前用户是不是代理
        $user_invite = $context->getInfo("InviteCode");
        if(empty($user_invite)){
            $context->reply(["status"=>210,"msg"=>"当前登录用户不是代理，不能新增用户"]);
            return;
        }
        $user_key = $data["user_key"];
        $user_password = $data["user_password"];
        //验证参数
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/';
        if(!preg_match($preg,$user_key)){
            $context->reply(['status' => 205, 'msg' => '账户名格式不正确']);
            return;
        }
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,40}$/';
        if(!preg_match($preg,$user_password)){
            $context->reply(['status' => 206, 'msg' => '密码格式不正确']);
            return;
        }
        //验证账户名是否存在
        $sql = "SELECT user_id FROM user_auth WHERE user_key=:user_key";
        $param = [":user_key"=>$user_key];
        $info = array();
        try{
            foreach ($mysql->query($sql,$param) as $row){
                $info = $row;
            }
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"新增失败"]);
            throw new \PDOException($e);
        }
        if(!empty($info)){
            $context->reply(["status"=>207,"msg"=>"账户名已使用"]);
            return;
        }
        //当前登录用户的基本信息
        $sql = "SELECT deal_key,agent_id,invite_code,broker_1_id,broker_2_id FROM user_info_intact WHERE user_id=:user_id";
        $param = [
            ":user_id"=>$context->getInfo("UserId"),
        ];
        foreach ($mysql->query($sql,$param) as $row){
            $list = $row;
        }
        if(empty($list)){
            $context->reply(["status"=>208,"msg"=>"数据获取失败"]);
            return;
        }
        //添加用户基本信息
        $sql = "INSERT INTO user_info SET layer_id=:layer_id, deal_key=:deal_key, agent_id=:agent_id, broker_1_id=:broker_1_id, broker_2_id=:broker_2_id, broker_3_id=:broker_3_id, register_invite=:register_invite, register_time=:register_time,register_ip=:register_ip,register_device=:register_device,login_time=:login_time, login_ip=:login_ip, login_device=:login_device, phone_number=:phone_number,memo=:memo";
        $params = [
            ":layer_id"=>1,
            ":deal_key"=>$list["deal_key"],
            ":agent_id"=>$list["agent_id"],
            ":broker_1_id"=>$context->getInfo("UserId"),
            ":broker_2_id"=>empty($list["broker_1_id"])?0:$list["broker_1_id"],
            ":broker_3_id"=>empty($list["broker_2_id"])?0:$list["broker_2_id"],
            ":register_invite"=>$list["invite_code"],
            ":register_time"=>time(),
            ":register_ip"=>ip2long($context->getClientAddr()),
            ":register_device"=>$context->getInfo("LoginDevice"),
            ":login_time"=>0,
            ":login_ip"=>0,
            ":login_device"=>0,
            ":phone_number"=>0,
            ":memo"=>"新增",
        ];
        $user_id = 0;
        try{
            $mysql->execute($sql,$params);
            $sql = 'SELECT last_insert_id() as user_id';
            foreach ($mysql->query($sql) as $row){
                $user_id = $row['user_id'];
            }
            //记录日志
        }catch(\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"新增失败"]);
            throw new \PDOException($e);
        }
        //存登录信息
        $sql = "INSERT INTO user_auth SET user_id=:user_id,user_key=:user_key,password_hash=:password_hash";
        $params = [":user_id"=>$user_id,":user_key"=>$user_key,":password_hash"=>$user_password];
        try{
            $mysql->execute($sql,$params);
            //记录日志
        }catch(\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"添加失败"]);
            throw new \PDOException($e);
        }
        $context->reply(["status"=>200,"msg"=>"新增成功"]);
        //添加report库的数据信息
        $taskAdapter = new \Lib\Task\Adapter($config->cache_app);
        $taskAdapter->plan('Report/UserCumulate', ['user_id' => $user_id],time(),9);
        $taskAdapter->plan('Report/UserEvent', ['user_id' => $user_id],time(),9);
    }
}