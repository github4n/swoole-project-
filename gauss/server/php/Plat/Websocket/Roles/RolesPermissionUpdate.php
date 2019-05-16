<?php

namespace Plat\Websocket\Roles;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * RolesPermissionUpdate class.
 *
 * @description   description
 * @Author  blake
 * @date  19.02.23
 * @links  Roles/RolesPermissionUpdate {"role_id":"2","operate_key":["ticket_official_update","website_site_update","ticket_setting_select","ticket_setting_bonus"]}
 *  1.编辑角色信息提交的信息
 * 参数：role_id:角色的id  operate_key:权限值的数组数组
 * 示例：{"role_id":1,"operate_key":["cash_list","report_analysis","account_operate_select"]}
 * 状态码：
 * 200：权限分配成功
 * 202：权限id不能为空
 * 203：角色的id不能为空
 * 400：权限分配失败
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class RolesPermissionUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_role_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $role_id = $data['role_id'];
        $role_name = $data['role_name'];
        $role_auth = $data['role_auth'];
        if (empty($role_id)) {
            $context->reply(['status' => 203, 'msg' => '角色的id不能为空']);

            return;
        }
        if (empty($role_name)) {
            $context->reply(['status' => 203, 'msg' => '角色名称不能为空']);

            return;
        }
        //角色名称不能超过20位数
        if (mb_strlen($role_name) > 10 || mb_strlen($role_name) < 2) {
            $context->reply(['status' => 210, 'msg' => '角色名称2-10位']);

            return;
        }
        if (empty($role_auth)) {
            $context->reply(['status' => 204, 'msg' => '权限信息不能为空']);

            return;
        }
        if (!is_array($role_auth)) {
            $context->reply(['status' => 205, 'msg' => '权限信息格式错误']);

            return;
        }
        if ($role_id == 1) {
            $context->reply(['status' => 206, 'msg' => '超级管理员的权限不能修改']);

            return;
        }
        $mysql = $config->data_admin;
        //查询角色名是否已存在
        $sql = 'SELECT * FROM admin_role WHERE role_name=:role_name AND role_id!=:role_id';
        $params = [
            ':role_name' => $role_name,
            ':role_id' => $role_id,
        ];
        $find_name = [];
        foreach ($mysql->query($sql, $params) as $row) {
            $find_name = $row;
        }
        if (!empty($find_name)) {
            $context->reply(['status' => 206, 'msg' => '角色名称已存在']);

            return;
        }
        //判断角色id在表中是否存在
        $sql = 'SELECT role_name FROM admin_role WHERE role_id=:role_id';
        $param = [':role_id' => $role_id];
        $role_data = [];
        foreach ($mysql->query($sql, $param) as $role_row) {
            $role_data = $role_row;
        }
        if ($role_data == null) {
            $context->reply(['status' => 401, 'msg' => '修改失败']);

            return;
        }
        try {
            $sql = 'DELETE FROM admin_permit WHERE role_id=:role_id';
            $params = [
                ':role_id' => $role_id,
            ];
            $mysql->execute($sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //添加权限到admin_permit表中
        foreach ($role_auth as $v) {
            try {
                $sql = 'INSERT INTO admin_permit SET role_id=:role_id,operate_key=:operate_key';
                $params = [
                    ':role_id' => $role_id,
                    ':operate_key' => $v,
                ];
                $mysql->execute($sql, $params);
            } catch (\PDOException $e) {
                $context->reply(['status' => 403, 'msg' => '修改失败']);
                throw new \PDOException($e);
            }
        }
        //修改角色信息
        $sqls = 'UPDATE admin_role SET role_name = :role_name WHERE role_id = :role_id';
        $param = [
            ':role_id' => $role_id,
            ':role_name' => $role_name,
        ];
        try {
            $mysql->execute($sqls, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //提交到日志
        $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
        $params = [
            ':admin_id' => $context->getInfo('adminId'),
            ':operate_key' => 'account_role_update',
            ':detail' => '给角色'.$role_id.'分配权限',
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
