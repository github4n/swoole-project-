<?php
namespace App\Websocket\Demo;

use Lib\Config;
use Lib\Websocket\Context;
use Lib\Websocket\IHandler;

/*
 * 恢复登录
 *  Demo/ResumeLogin {"resume_key":"fdf3c558a8742845152e9b21190bb5cdef41206a"}
 * */

class ResumeLogin implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $data = $context->getData();
        $resume_key = $data['resume_key'];
        if(empty($resume_key)){
            $context->reply(["status"=>202,"msg"=>"恢复的key不能为空"]);
            return;
        }
        $user_agent = sha1($context->getInfo("User-Agent"));
        $sql = "SELECT * FROM guest_session WHERE resume_key=:resume_key AND user_agent=:user_agent and lose_time > :lose_time";
        $params = [
            ":resume_key"=>$resume_key,
            ":user_agent"=>$user_agent,
            ":lose_time"=>time()-600
        ];
        $mysql = $config->data_guest;
        $info = array();
        foreach ($mysql->query($sql,$params) as $row){
            $info = $row;
        }
        if(empty($info)){
            $context->reply(["status"=>400,"msg"=>"恢复登录失败"]);
            return;
        }else{
            $userId = $info["user_id"];
            //更新缓存信息
            try{
                //用户掉线10分钟内 重新上线更新用户的信息
                $sql = "UPDATE guest_session SET client_id=:client_id WHERE resume_key = :resume_key";
                $param = [':client_id'=>$context->clientId(),':resume_key'=>$resume_key];
                $mysql->execute($sql,$param);
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '恢复失败请重新登录']);
                throw new \PDOException($e);
            }
            //获取用户基本信息
            $sql = "SELECT * FROM account WHERE user_id=:user_id";
            $param = [":user_id"=>$userId];
            $user_info = array();
            foreach ($mysql->query($sql,$param) as $rows){
                $user_info = $rows;
            }
            if(empty($user_info)){
                $context->reply(["status"=>204,"msg"=>"登录失败"]);
                return;
            }
            
            $context->reply(['status' => 200, 'msg' => '恢复登录成功','resume_key'=>$resume_key,"user_key"=>$user_info['user_key'],"guest_id"=>'guest']);
            //存redis
            $context->setInfo("GuestId",$userId);
            $context->setInfo('UserKey',$user_info['user_key']);
            $context->setInfo('UserId',$userId);
            $context->setInfo('DealKey','guest');
            $context->setInfo('LayerId',100) ;
            $context->setInfo('AccountName','游客') ;
            //会员私信
            $id = $context->clientId();
            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $taskAdapter->plan('User/Balance', ['user_id' => $userId,'id'=>$id,"deal_key"=>"guest"]);
        }
    }
}
