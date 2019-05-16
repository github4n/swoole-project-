<?php
namespace App\Websocket\Login;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 登录接口
 * Login/Login {"user_name":"user123","user_password":"user123","login_device":0}
 *  login_device 登录设备类型：0-PC，1-手机浏览器，2-苹果手机app，3-安卓手机app
 * */

class Login implements IHandler
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
        
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $clientId = $context->clientId();
        $data = $context->getData();
        $mysql = $config->data_user;
        $mysqlPublic = $config->data_public;
        $cache = $config->cache_app;
        $websocketAdapter = $context->getAdapter();
        $user_name = $data["user_name"];
        $user_password = $data["user_password"];
        $login_device = intval(isset($data["login_device"])?$data["login_device"]:0);
        // 为空判断
        if (empty($user_name)) {
            $context->reply(['status' => 201, 'msg' => '请输入用户名']);
            return;
        }
        if (empty($user_password)) {
            $context->reply(['status' => 202, 'msg' => '情输入密码']);
            return;
        }
        $sql = 'CALL user_auth_verify(:user_key, :password)';
        $params = [':user_key' => $user_name, ':password' => $user_password];
        $user = array();
        foreach ($mysql->query($sql, $params) as $row) {
            $user = $row;
        }
        if (empty($user)) {
            $context->reply(['status' => 203, 'msg' => '用户名或者密码输入错误，请重新输入']);
            return;
        }
        $userId = $user['user_id'];

        //获取用户基本信息
        $sql = "SELECT layer_id,agent_id,deal_key,invite_code,account_name,broker_1_id,broker_2_id,broker_3_id,user_key FROM user_info_intact WHERE user_id=:user_id";
        $param = [":user_id"=>$userId];
        $user_info = array();
        foreach ($mysql->query($sql,$param) as $rows){
            $user_info = $rows;
        }
        if(empty($user_info)){
            $context->reply(["status"=>204,"msg"=>"连接超时,请重新登录"]);
            return;
        }
        if($user_name !== $user_info["user_key"]){
           $context->reply(["status"=>300,"msg"=>"请输入正确的登录账号"]);
           return;
        }
        //删除之前的登录信息
        $sql = "select client_id from user_session where user_id = :user_id";
        $client_list = iterator_to_array($mysql->query($sql,$param));
        if(!empty($client_list)){
            foreach ($client_list as $key=>$val){
                //删除相关的redis信息

                $taskAdapter->plan("LoginNotify",["client_id"=>$val["client_id"]],time(),1);

//                $websocketAdapter->send($val["client_id"], "Login/Notice",["status" => 800,"msg"=>"该账号已在其他地方登录"]);
            }
            $sql = "delete from user_session where user_id = :user_id";
            $mysql->execute($sql,$param);
        }
        //监测层级是否被冻结
        $layer_id = $user_info['layer_id'];
        $auth_sql = "select operate_key from layer_permit where layer_id = '$layer_id'";
        $authArray = [];
        foreach ($mysql->query($auth_sql) as $row) {
            $authArray[] = $row['operate_key'];
        }
        $stop_rebate = 0;
        if(!empty($authArray)){
            if(in_array('account_freeze',$authArray)) {
                $context->reply(["status"=>209,"msg"=>"该账号被冻结,请联系客服"]);
                return;
            }
            //禁止返点信息
            if(in_array('rebate_prohibit',$authArray)) {
                $stop_rebate = 1;
            }
        }

        $context->setInfo('Auth',json_encode($authArray));
        //更新用户的登录时间
        $sql = "UPDATE user_info SET login_ip=:login_ip, login_time=:login_time, login_device=:login_device WHERE user_id =:user_id";
        $param = [
            ":login_ip"=>ip2long($context->getClientAddr()),
            ":login_time"=>time(),
            ":login_device"=>$login_device,
            ":user_id"=>$userId
        ];
        try{
            $mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>401,"msg"=>"连接超时,请重新登录"]);
            throw new \PDOException($e);
        }
        //更新用户累计数据信息
        $sql = "UPDATE user_cumulate SET login_ip=:login_ip, login_time=:login_time, login_device=:login_device WHERE user_id =:user_id";
        $param = [
            ":login_ip"=>ip2long($context->getClientAddr()),
            ":login_time"=>time(),
            ":login_device"=>$login_device,
            ":user_id"=>$userId
        ];
        try{
            $report_mysql = $config->data_report;
            $report_mysql->execute($sql,$param);
        }catch (\PDOException $e){
            $context->reply(["status"=>402,"msg"=>"连接超时,请重新登录"]);
            throw new \PDOException($e);
        }

        //记录登录缓存
        try {
            $session_sql = "INSERT INTO user_session SET client_id = :client_id, user_id = :user_id, layer_id = :layer_id, agent_id = :agent_id, broker_1_id = :broker_1_id, broker_2_id = :broker_2_id, broker_3_id = :broker_3_id, login_time = :login_time, client_ip=:client_ip, user_agent=:user_agent";
            $params = [
                ':client_id' => $clientId,
                ':user_id' => $userId,
                ':layer_id' => $user_info["layer_id"],
                ':agent_id' => $user_info["agent_id"],
                ':broker_1_id' => $user_info["broker_1_id"],
                ':broker_2_id' => $user_info["broker_2_id"],
                ':broker_3_id' => $user_info["broker_3_id"],
                ':login_time' => time(),
                ':client_ip'=>ip2long($context->getClientAddr()),
                ':user_agent'=>sha1($context->getInfo("User-Agent"))
            ];
            $mysql->execute($session_sql, $params);
        } catch (\PDOException $e) {

            $context->reply(['status' => 403, 'msg' => '连接超时,请重新登录']);
            throw new \PDOException($e);
        }
        //获取resume_key
        $sql  = "SELECT resume_key FROM user_session WHERE client_id=:client_id";
        $param = [":client_id"=>$clientId];
        $info = array();
        foreach ($mysql->query($sql,$param) as $row){
            $info = $row;
        }
        if(empty($info)){
            $context->reply(["status"=>400,"msg"=>"连接超时,请重新登录"]);
            return;
        } else{
            $resume_key = $info['resume_key'];
        }
        $deal_key = $user_info["deal_key"];
        $invite_code = empty($user_info["invite_code"])?"0":$user_info["invite_code"];
        $account_name =  empty($user_info["account_name"])?" ":$user_info["account_name"];
        //记录登录ip
        $ip = $context->getClientAddr();
        //数据库获取用户ip地址
        $ip_info = [];
        $sql = "select country,area,region,city,county,isp from ip_address where ip_net=:ip";
        foreach ($mysqlPublic->query($sql,[":ip"=>ip2long($ip)>>8]) as $row){
            $ip_info = $row;
        }
        $ip_sql = "insert into user_ip_history set user_id=:user_id,client_ip=:client_ip,country=:country,area=:area,region=:region,city=:city,county=:county,isp=:isp,login_time=:login_time ";
        $ip_param = [
            ":user_id" => $userId,
            ":client_ip" => ip2long($ip),
            ":country" => empty($ip_info["country"]) ? "0" : $ip_info["country"],
            ":area" => empty($ip_info["area"]) ? "0" : $ip_info["area"],
            ":county" => empty($ip_info["county"]) ? "0" : $ip_info["county"],
            ":region" => empty($ip_info["region"]) ? "0" : $ip_info["region"],
            ":city" => empty($ip_info["city"]) ? "0" : $ip_info["city"],
            ":isp" => empty($ip_info["isp"]) ? "0" : $ip_info["isp"],
            ":login_time" => time(),
        ];
        $mysql->execute($ip_sql,$ip_param);

        //存redis
        $context->setInfo("UserId",$userId);
        $context->setInfo("UserKey",$user_name);
        $context->setInfo("LoginDevice",$login_device);
        $context->setInfo("LayerId",$user_info["layer_id"]);
        $context->setInfo("DealKey",$deal_key);
        $context->setInfo("InviteCode",$invite_code);
        $context->setInfo("AccountName",$account_name);
        $context->reply(["status"=>200,"msg"=>"登录成功","resume_key"=>$resume_key,"invite_code"=>$invite_code,"stop_rebate"=>$stop_rebate]);
        //会员私信

        $id = $context->clientId();

        $taskAdapter->plan('User/UserInfo', ['user_id' => $userId,'id'=>$id] ,time(),1);
        $taskAdapter->plan('Message/UserMessage', ['user_id' => $userId,'id'=>$id],time(),1);
        $taskAdapter->plan('Message/LayerMessage', ['layer_id' => $user_info["layer_id"],'id'=>$id],time(),1);
        $taskAdapter->plan('User/Balance', ['user_id' => $userId,'deal_key'=>$deal_key,'id'=>$id],time(),1);
    }
}