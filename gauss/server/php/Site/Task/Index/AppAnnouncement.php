<?php
namespace Site\Task\Index;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * AppAnnouncement
 * @description   app首页通知任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Index/AppAnnouncement 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class AppAnnouncement implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        $staff_mysql = $config->data_staff;

        //首页通知
        $sql = "SELECT * FROM announcement WHERE publish=1 AND stop_time>:stop_time AND start_time<:start_time ORDER BY announcement_id DESC limit 1";
        $param = [
            ":stop_time"=>time(),
            ":start_time"=>time(),
        ];
        try{
            $announcement = iterator_to_array($staff_mysql->query($sql,$param));
        }catch (\PDOException $e){
            throw new \PDOException($e);
        }
        $adapter->plan('NotifyApp', ['path' => 'Index/Announcement', 'data' => ['announcement'=>$announcement]]);

    }
}
