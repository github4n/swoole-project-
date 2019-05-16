<?php

namespace Plat\Websocket\Roles;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * RolesDelete class.
 *
 * @description   删除角色
 * @Author  blake
 * @date  19.02.23
 * @links  Roles/RolesDelete  {"role_id":["5","4"]}
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class RolesDelete extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_role_delete', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_admin;
        $roleId = $data['role_id'];
        if (empty($roleId)) {
            $context->reply(['status' => 203, 'msg' => '编号不能为空']);

            return;
        }
        if (!is_array($roleId)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);

            return;
        }
        foreach ($roleId as $item) {
            if ($item == 1) {
                $context->reply(['status' => 205, 'msg' => '超级管理员不能删除']);

                return;
            }
            // 检查admin_appoint表角色是否已被关联
            $sql = 'SELECT admin_id FROM admin_appoint WHERE role_id = :role_id';
            $params = ['role_id' => $item];
            $info = array();
            foreach ($mysql->query($sql, $params) as $row) {
                $info = $row;
            }
            if (!empty($info)) {
                $context->reply(['status' => 205, 'msg' => '删除失败，该角色已被关联']);

                return;
            }
            $sql = 'DELETE FROM admin_role WHERE role_id = :role_id';
            try {
                $mysql->execute($sql, $params);
                $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
                $param = [
                    ':admin_id' => $context->getInfo('adminId'),
                    ':operate_key' => 'account_role_delete',
                    ':detail' => '删除角色'.$item,
                ];
                $mysql->execute($sql, $param);
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '删除失败']);
                throw new \PDOException($e);
            }
        }
        $context->reply(['status' => 200, 'msg' => '删除成功']);
    }
}
