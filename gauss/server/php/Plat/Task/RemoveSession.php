<?php
namespace Plat\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class RemoveSession implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data = $context->getData();
        $mysql = $config->data_admin;
        $id = $data['id'];
        $sql = "DELETE FROM admin_session WHERE client_id = :client_id";
        $param = ["client_id" => $id];
        $mysql->execute($sql, $param);
        $sql = "SELECT admin_id FROM admin_session";
        $total = $mysql->execute($sql);
        $context->getAdapter()->plan('NotifyClient', ['path' => 'Admin/Online', 'data' => ['online_num' => $total]]);
    }
}
