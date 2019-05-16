<?php
namespace App\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class RemoveSession implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data = $context->getData();
        $mysql = $config->data_user;
        $id = $data['id'];
        $sql = "DELETE FROM user_session WHERE client_id = :client_id";
        $param = ["client_id" => $id];
        $mysql->execute($sql, $param);
    }
}
