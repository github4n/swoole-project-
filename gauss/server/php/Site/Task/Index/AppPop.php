<?php
namespace Site\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * AppPop.php
 * @description   app/弹窗消息
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Index/AppPop 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class AppPop implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        $staff_mysql = $config->data_staff;

        //app弹窗消息
        $sql = "SELECT popup_id,content FROM popup WHERE publish = 1 AND stop_time>:stop_time AND start_time<:start_time LIMIT 5";
        $param = [
            ":stop_time"=>time(),
            ":start_time"=>time(),
        ];
        try{
            $popup = iterator_to_array($staff_mysql->query($sql,$param));
        }catch(\PDOException $e){
            throw new \PDOException($e);
        }
        $adapter->plan('NotifyApp', ['path' => 'Index/PopUp', 'data' => ['popup'=>$popup]]);

    }
}
