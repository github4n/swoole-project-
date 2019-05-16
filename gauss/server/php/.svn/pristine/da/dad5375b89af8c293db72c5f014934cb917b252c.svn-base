<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-21
 * Time: 下午1:03
 */

namespace App\Websocket\User\Message;
use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;

/*
 * User/Message/ActivityDetail {"activity_id":"1"}
 */
class ActivityDetail extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }

        $data = $context->getData();
        $activity_id = isset($data['activity_id']) ? $data['activity_id'] : '';
        if (!is_numeric($activity_id)) {
            $context->reply(['status'=>401,'msg'=>'活动参数错误']);
        }

        $mysql = $config->data_staff;
        $sql = "SELECT title,start_time,stop_time,cover,content FROM promotion WHERE promotion_id='$activity_id'";
        $list = Array();

        try{
            foreach ($mysql->query($sql) as $rows){
                $list = $rows;
            }
        }catch (\PDOException $e){
            $context->reply(["status"=>400,"msg"=>"获取列表失败"]);
            throw new \PDOException($e);
        }

        $context->reply(['status'=>200,'msg'=>'成功','data'=>$list]);

    }
}