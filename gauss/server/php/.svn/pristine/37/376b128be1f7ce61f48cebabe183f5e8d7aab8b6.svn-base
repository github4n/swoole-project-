<?php

namespace Plat\Websocket\Roles;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * RolesList class.
 *
 * @description   角色列表
 * @Author  blake
 * @date  19.02.23
 * @links  Roles/RolesList
 * 状态码：
 * 200：获取列表信息成功
 * 201：没有操作权限
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class RolesList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_role_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();

        $mysql = $config->data_admin;
        $sql = 'SELECT * FROM admin_role_intact ORDER BY role_id DESC LIMIT 1000';
        $role = [];
        $roleList = array();
        foreach ($mysql->query($sql) as $row) {
            $role[] = $row;
        }
        //获取角色权限
        if (!empty($role)) {
            foreach ($role as $key => $val) {
                $roleList[$key]['operate'] = '';
                $roleList[$key]['role_id'] = $val['role_id'];
                $roleList[$key]['role_name'] = $val['role_name'];
                $roleList[$key]['admin_num'] = $val['admin_count'];
                $operate_list = isset($val['operate_list']) ? $val['operate_list'] : [];
                foreach (json_decode($operate_list) as $item) {
                    $roleList[$key]['operate'] .= $item.',';
                }
            }
        }

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'rolelist' => $roleList,
        ]);
    }
}
