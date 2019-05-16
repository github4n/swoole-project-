<?php
namespace App\Websocket\Register;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 登录接口
 * Register/Register {"user_key":"ceshi123","user_password":"user1234","confirm_password":"user1234","register_device":"Android","register_invite":"43112609","is_agree":1}
 *
 * */

class Register implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $mysqlStaff = $config->data_staff;
        $sql = "select int_value from site_setting where setting_key = 'site_status'";
        foreach ($mysqlStaff->query($sql) as $row){
            $status = $row["int_value"];
        }
        if($status == 2 || $status == 3){
            $context->reply(['status' => 500,"msg"=>"维护中"]);
            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $user_key = $data["user_key"];
        $user_password = $data["user_password"];
        $confirm_password = $data["confirm_password"];
        $register_device = intval($data["register_device"]);
        $register_invite = $data["register_invite"];
/*        $is_agree = $data["is_agree"];   //是否同意规则
        if(empty($is_agree)){
             $context->reply(["status"=>206,"msg"=>"协议未同意"]);
             return;
        }  */
        if($user_password !== $confirm_password){
            $context->reply(["status"=>205,"msg"=>"两次输入密码不一致，请重新输入"]);
            return;
        }
        // 验证规则
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,12}$/';
        if (!preg_match($preg, $user_key)) {
            $context->reply(['status' => 207, 'msg' => '请输入6-12位英文数字组合用户名']);
            return;
        }
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,12}$/';
        if (!preg_match($preg, $user_password)) {
            $context->reply(['status' => 208, 'msg' => '请输入6-12位英文数字组合密码']);
            return;
        }
        //验证邀请码
        $sql = "SELECT user_id,user_key,deal_key,layer_id,broker_1_id,broker_2_id,agent_id FROM user_info_intact WHERE invite_code=:invite_code";
        $param = [":invite_code"=>$register_invite];
        $userinfo = array();
        foreach ($mysql->query($sql,$param) as $row){
            $userinfo = $row;
        }
        if(empty($userinfo)) {
            $context->reply(["status"=>206,"msg"=>"邀请码错误，请重新输入"]);
            return;
        }
        //验证邀请码是否有效
        $operate_list = [];
        $sql = "select operate_key from layer_permit where layer_id=:layer_id";
        foreach ($mysql->query($sql,[":layer_id"=>$userinfo["layer_id"]]) as $row){
            $operate_list[] = $row["operate_key"];
        }
        if(!empty($operate_list) && in_array("invite_invalid",$operate_list)){
            $context->reply(["status"=>230,"msg"=>"该邀请码无效，请重新输入"]);
            return;
        }
        //验证登录名
        $sql = "SELECT user_key FROM user_auth WHERE user_key=:user_key";
        $param = [":user_key"=>$user_key];
        $userKey = array();
        foreach ($mysql->query($sql,$param) as $row){
            $userKey = $row;
        }
        if(!empty($userKey)){
            $context->reply(["status"=>209,"msg"=>"该账号已被注册，请重新输入"]);
            return;
        }
        //基本信息
        $deal_key = $userinfo["deal_key"];
        $agent_id = $userinfo["agent_id"];
        $ip = ip2long($context->getClientAddr());
        $sql = "INSERT INTO user_info SET deal_key=:deal_key,layer_id=:layer_id,agent_id=:agent_id,broker_1_id=:broker_1_id,broker_2_id=:broker_2_id,broker_3_id=:broker_3_id,register_invite=:register_invite,register_time=:register_time,register_ip=:register_ip,register_device=:register_device,login_time=:login_time,login_ip=:login_ip,login_device=:login_device,phone_number=:phone_number,memo=:memo";
        $params = [
            ":deal_key"=>$deal_key,
            ":layer_id"=>1,
            ":agent_id"=>$agent_id,
            ":broker_1_id"=>$userinfo["user_id"],
            ":broker_2_id"=>$userinfo["broker_1_id"],
            ":broker_3_id"=>$userinfo["broker_2_id"],
            ":register_invite"=>$register_invite,
            ":register_time"=>time(),
            ":register_ip"=>$ip,
            ":register_device"=>$register_device,
            ":login_time"=>0,
            ":login_ip"=>0,
            ":login_device"=>0,
            ":phone_number"=>0 ,
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
            $context->reply(["status"=>400,"msg"=>"注册失败,网络链接已断开"]);
            return;
        }
        //登录信息
        $sql = "INSERT INTO user_auth SET user_id=:user_id,user_key=:user_key,password_hash=:password_hash";
        $params = [":user_id"=>$user_id,":user_key"=>$user_key,":password_hash"=>$user_password];
        try{
            $mysql->execute($sql,$params);
            //记录日志
        }catch(\PDOException $e){
            $context->reply(["status"=>401,"msg"=>"注册失败,网络链接已断开"]);
            throw new \PDOException($e);
        }
        $context->reply([
            "status"=>200,
            "msg"=>"注册成功",
        ]);
        //添加report库的数据信息
        $taskAdapter = new \Lib\Task\Adapter($config->cache_app);
        $taskAdapter->plan('Report/UserCumulate', ['user_id' => $user_id],time(),9);
        $taskAdapter->plan('Report/UserEvent', ['user_id' => $user_id],time(),9);
    }
}