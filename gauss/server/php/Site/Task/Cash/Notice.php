<?php

namespace Site\Task\Cash;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * @description  确认收到用户充值，发送站长通知
 * @Author  Rose
 * @date  2019-05-08
 * @link Websocket: Cash/Notice {'deposit_serial': "", 'finish_money':"", 'staff_id' : ""}
 * @modifyAuthor
 * @modifyDate
 * */

class Notice implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['deposit_serial' => $deposit_serial, 'finish_money' => $finish_money, 'staff_id' => $staffId] = $context->getData();
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_site);
        $mysqlStaff = $config->data_staff;

        $staff_name_sql = 'SELECT staff_name FROM staff_info WHERE staff_id=:staff_id';
        $staff_name = iterator_to_array($mysqlStaff->query($staff_name_sql, [':staff_id' => $staffId]));
        $staff_id_sql = 'SELECT staff_id FROM staff_info WHERE staff_grade=0';
        $staff_list = [];
        foreach ($mysqlStaff->query($staff_id_sql) as $row) {
            $staff_list[] = $row['staff_id'];
        }
        $staff_clientID_sql = 'SELECT client_id FROM staff_session WHERE staff_id in :staff_list';
        $data_list = iterator_to_array($mysqlStaff->query($staff_clientID_sql, [':staff_list' => $staff_list]));
        if (!empty($data_list)) {
            foreach ($data_list as $value) {
                $id = $value['client_id'];
                $data = ['operating_staff_id' => $staffId, 'operating_staff_name' => $staff_name[0]['staff_name'], 'recharge_money' => $finish_money];
                $websocketAdapter->send($id, 'Cash/Notice', $data);
            }
        }
    }
}
