<?php

namespace Plat\Websocket\Staff;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * StaffList class.
 *
 * @description   员工管理
 * @Author  blake
 * @date  19.02.23
 * @links  url
 * 参数： type:搜索类型 1模糊查询，2精确匹配
 * 状态码：
 * 200：获取列表信息成功
 * 205：没有操作权限
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class StaffList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_admin_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_admin;

        $admin_name = isset($data['admin_name']) ? $data['admin_name'] : '';

        $param = [];
        //员工名精确查询
        if (!empty($admin_name)) {
            $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/';
            if (!preg_match($preg, $admin_name)) {
                $context->reply(['status' => 200, 'msg' => '请输入正确的搜索账号', 'adminlist' => []]);

                return;
            }
            $admin_name = trim($data['admin_name']);
            $param[':admin_name'] = $admin_name;
            $admin_name = ' WHERE admin_key = :admin_name';
        }

        try {
            $sql = 'SELECT admin_id,admin_name,admin_key,role_map as role_name FROM admin_info_intact'.$admin_name.' ORDER BY admin_id DESC LIMIT 1000';
            $list = [];
            foreach ($mysql->query($sql, $param) as $row) {
                $list[] = $row;
            }
            if (!empty($list)) {
                foreach ($list as $k => $v) {
                    foreach (json_decode($v['role_name']) as $item) {
                        $list[$k]['role_name'] = $item;
                    }
                }
            }

            $context->reply(['status' => 200, 'msg' => '获取列表信息成功', 'adminlist' => $list]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取列表信息失败']);
            throw new \PDOException('sql run error'.$e);
        }
    }
}
