<?php

namespace Plat\Websocket\Roles;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * RolesAdd class.
 *
 * @description   添加角色
 * @Author  blake
 * @date  19.02.23
 * @links  url
 * 参数：
 * 1.添加
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
class RolesAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_role_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $AdminId = $context->getInfo('adminId');
        $mysql = $config->data_admin;
        // 获取所有权限列表
        $sqlAll = 'SELECT operate_key,operate_name FROM `operate` WHERE require_permit=1';
        $role_all = [];
        foreach ($mysql->query($sqlAll) as $all) {
            $role_all[] = $all;
        }
        //获取当前登录的用户的权限
        $sql = 'SELECT operate_key FROM admin_operate WHERE admin_id=:admin_id';
        $param = [':admin_id' => $AdminId];
        $infos = array();
        try {
            foreach ($mysql->query($sql, $param) as $rows) {
                $infos[] = $rows;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, '权限列表获取失败']);
            throw new \PDOException($e);
        }
        $auth_list = array();
        if (!empty($infos)) {
            foreach ($infos as $key => $val) {
                $auth_list[] .= $val['operate_key'];
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '权限列表获取成功',
            'list' => $auth_list,
            'roleAll' => $role_all,
            ]);
    }
}
