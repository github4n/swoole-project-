<?php
namespace App\Websocket\Demo;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 登录接口
 * Demo/GuestLogin 
 *  login_device 登录设备类型：0-PC，1-手机浏览器，2-苹果手机app，3-安卓手机app
 * */

class GuestLogin implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $mysql = $config->data_guest;
        //基本信息
        $ip = ip2long($context->getClientAddr());
        $sql = "INSERT INTO guest_user SET account_name=:account_name,layer_id=:layer_id,register_ip=:register_ip,register_time=:register_time";
        $params = [
            ":account_name"=>'游客',
            ":layer_id"=>100,
            ":register_ip"=>$ip,
            ":register_time"=>time()
        ];
        try{
            $mysql->execute($sql,$params);
            $sql = 'SELECT last_insert_id() as user_id';
            foreach ($mysql->query($sql) as $row){
                $user_id = $row['user_id'];
            }
        }catch(\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"登录失败"]);
            return;
        }
        //生成user_key
        $code = (999959*$user_id)%999999;
        $key_code = sprintf("%06d", $code);
        $user_key = 'guest'.$key_code;
        //更新基本信息
        $update_sql = "UPDATE guest_user SET user_key=:user_key WHERE user_id=:user_id";
        $mysql->execute($update_sql,[":user_key"=>$user_key,":user_id"=>$user_id]);
        //生成金额
        $account_sql = "INSERT INTO account SET user_id=:user_id,user_key=:user_key,account_name=:account_name,layer_id=:layer_id,money=:money,deposit_audit=:deposit_audit,coupon_audit=:coupon_audit";
        $param = [
            ":user_id"=> $user_id,
            ":user_key"=> $user_key,
            ":account_name"=> "游客",
            ":layer_id"=> 100,
            ":money"=> 2000,
            ":deposit_audit"=> 0,
            ":coupon_audit"=> 0,
        ];
        try{
            $mysql->execute($account_sql,$param);
        }catch(\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"登录失败"]);
            return;
        }

        //存redis
        $context->setInfo('GuestId',$user_id);
        $context->setInfo('UserKey',$user_key);
        $context->setInfo('UserId',$user_id);
        $context->setInfo('DealKey','guest');
        $context->setInfo('LayerId',100) ;
        $context->setInfo('AccountName','游客') ;

        //记录缓存信息
        $session_sql = "INSERT INTO guest_session SET user_id=:user_id,client_id=:client_id,user_key=:user_key,login_time=:login_time,client_ip=:client_ip,user_agent=:user_agent";
        $param = [
            ":client_id"=>$context->clientId(),
            ":user_id"=> $user_id,
            ":user_key"=> $user_key,
            ":login_time"=> time(),
            ":client_ip"=>ip2long($context->getClientAddr()),
            ':user_agent'=>sha1($context->getInfo("User-Agent"))

        ];
        try{
            $mysql->execute($session_sql,$param);
        }catch(\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"登录失败"]);
            return;
        }
        //获取resume_key
        $sql  = "SELECT resume_key FROM guest_session WHERE client_id=:client_id";
        $param = [":client_id"=>$context->clientId()];
        $info = array();
        foreach ($mysql->query($sql,$param) as $row){
            $info = $row;
        }
        if(empty($info)){
            $context->reply(["status"=>400,"msg"=>"登录失败"]);
            return;
        } else{
            $resume_key = $info['resume_key'];
        }
        $context->reply(["status"=>200,"msg"=>"登录成功","resume_key"=>$resume_key,"user_key"=>$user_key,"guest_id"=>'guest']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Guest/Balance', ['user_id' => $user_id,'id'=>$context->clientId()],time());
        //72小时之后删除该用户
        $taskAdapter->plan('Guest/Delete', ['user_id' => $user_id],time() + 3*86400,8);

    }
}