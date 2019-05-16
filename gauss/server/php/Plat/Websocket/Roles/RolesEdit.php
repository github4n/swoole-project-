<?php

namespace Plat\Websocket\Roles;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * RolesEdit class.
 *
 * @description   修改角色
 * @Author  blake
 * @date  19.02.23
 * @links  url
 * 参数：role_name:角色名称
 * 状态码：
 * 200：添加成功
 * 201:角色名称已存在
 * 202：角色名称不能为空
 * 203：角色名称类型不正确
 * 204:角色名称不能超过20位数
 * 400：添加失败
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class RolesEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_role_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $mysql = $config->data_admin;
        $data = $context->getData();
        $role_id = $data['role_id'];
        if ($role_id == 1) {
            $context->reply(['status' => 203, 'msg' => '超级管理员不能修改']);

            return;
        }
        if (!is_numeric($role_id)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);

            return;
        }
        // 获取所有权限列表
        $sqlAll = 'SELECT operate_key,operate_name FROM `operate` WHERE require_permit=1';
        //获取修改的角色的信息
        $sql = 'SELECT role_name FROM admin_role WHERE role_id=:role_id';
        $param = [':role_id' => $role_id];
        $sqls = 'SELECT operate_key FROM admin_permit WHERE role_id=:role_id';
        $role_name = '';
        $list = array();
        $role_all = [];
        foreach ($mysql->query($sqlAll) as $all) {
            $role_all[] = $all;
        }
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $role_name = $row['role_name'];
            }
            foreach ($mysql->query($sqls, $param) as $rows) {
                $list[] .= $rows['operate_key'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取角色信息失败']);
            throw new \PDOException($e);
        }
        $info = [
            'role_name' => $role_name,
            'role_id' => $role_id,
            'role_auth' => $list,
            ];
        $context->reply([
            'status' => 200,
            'msg' => '权限列表获取成功',
            'list' => $info,
            'roleAll' => $role_all,
            ]);
    }
}
