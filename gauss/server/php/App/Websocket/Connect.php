<?php
namespace App\Websocket;

use Lib\Config;
use Lib\Websocket\Context;
use Lib\Websocket\IHandler;
/*
 * 除去期号和开奖号码的推送 其他推送需要调整一下
 * */

class Connect implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        //检测是否关闭
        $mysqlStaff = $config->data_staff;
        $sql = "select int_value from site_setting where setting_key = 'site_status'";
        foreach ($mysqlStaff->query($sql) as $row){
            $status = $row["int_value"];
        }
        if($status == 2 || $status == 3){
            $context->reply(['status' => 500,"msg"=>"维护中"]);
            return;
        }
        $random = bin2hex(random_bytes(20));
        $context->setInfo("Random",sha1($random)) ;
        $context->reply(['status' => 400,"msg"=>"正常","time"=>time(),"random"=>$random]);
    }
}
