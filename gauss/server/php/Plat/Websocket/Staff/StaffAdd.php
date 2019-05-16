<?php

namespace Plat\Websocket\Staff;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * StaffAdd class.
 *
 * @description   添加员工
 * @Author  blake
 * @date  19.02.23
 * @links  url
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class StaffAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_admin_insert', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $mysql = $config->data_admin;
        $role = [];
        $roleList = [];
        $sql = 'SELECT role_id, role_name FROM admin_role';
        foreach ($mysql->query($sql) as $row) {
            $role['role_id'] = $row['role_id'];
            $role['role_name'] = $row['role_name'];
            array_push($roleList, $role);
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'rolelist' => $roleList,
        ]);
    }
}
