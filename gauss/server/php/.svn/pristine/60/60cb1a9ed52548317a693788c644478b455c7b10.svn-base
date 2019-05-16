<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-17
 * Time: 上午10:40
 */

namespace App\Task\Message;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Activity implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ["id" => $id] = $context->getData();
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_app);
        $mysql = $config->data_staff;
        $sql = "SELECT promotion_id,title,start_time,stop_time,cover,content FROM promotion WHERE publish = 1  AND stop_time > :nowTime  AND start_time <= :nowTime ORDER BY start_time DESC";
        $param = [':nowTime' => time()];
        $list = Array();
        foreach ($mysql->query($sql,$param) as $row) {
            $list[] = $row;
        }
        $websocketAdapter->send($id,'Message/Activity', $list);
    }
}