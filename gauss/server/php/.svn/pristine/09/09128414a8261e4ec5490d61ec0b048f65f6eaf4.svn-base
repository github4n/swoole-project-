<?php

namespace Site\Task\User;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @description   用户出款或者入款通知到站点
 * @Author  Rose
 * @date  2019-05-08
 * @links  User/Notice
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class Notice implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $data] = $context->getData();
        $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_site);
        $mysqlStaff = $config->data_staff;
        $money = $data['money'];
        $type = $data['type'];
        $layer_id = $data['layer_id'];
        if ($type == 1) { //入款
            $sql = 'select staff_id,deposit_limit,layer_id_list,notify_status from staff_info_intact where staff_grade=0';
            $staff_list = iterator_to_array($mysqlStaff->query($sql));
            foreach ($staff_list as $key => $val) {
                if ($val['notify_status'] == 1) {
                    $sql = 'select client_id from staff_session where staff_id=:staff_id';
                    $param = [':staff_id' => $val['staff_id']];
                    foreach ($mysqlStaff->query($sql, $param) as $row) {
                        $websocketAdapter->send($row['client_id'], 'User/Notice', ['msg' => $data['msg']]);
                    }
                } else {
                    if ($val['deposit_limit'] >= $money && in_array($layer_id, json_decode($val['layer_id_list']))) {
                        $sql = 'select client_id from staff_session where staff_id=:staff_id';
                        $param = [':staff_id' => $val['staff_id']];
                        foreach ($mysqlStaff->query($sql, $param) as $row) {
                            $websocketAdapter->send($row['client_id'], 'User/Notice', ['msg' => $data['msg']]);
                        }
                    }
                }
            }
        }
        if ($type == 2) {  //出款
            $sql = 'select staff_id,withdraw_limit,layer_id_list,notify_status from staff_info_intact where staff_grade=0';
            $staff_list = iterator_to_array($mysqlStaff->query($sql));
            foreach ($staff_list as $key => $val) {
                if ($val['notify_status'] == 1) {
                    $sql = 'select client_id from staff_session where staff_id=:staff_id';
                    $param = [':staff_id' => $val['staff_id']];
                    foreach ($mysqlStaff->query($sql, $param) as $row) {
                        $websocketAdapter->send($row['client_id'], 'User/Notice', ['msg' => $data['msg']]);
                    }
                } else {
                    if ($val['withdraw_limit'] >= $money && in_array($layer_id, json_decode($val['layer_id_list']))) {
                        $sql = 'select client_id from staff_session where staff_id=:staff_id';
                        $param = [':staff_id' => $val['staff_id']];
                        foreach ($mysqlStaff->query($sql, $param) as $row) {
                            $websocketAdapter->send($row['client_id'], 'User/Notice', ['msg' => $data['msg']]);
                        }
                    }
                }
            }
        }
    }
}
