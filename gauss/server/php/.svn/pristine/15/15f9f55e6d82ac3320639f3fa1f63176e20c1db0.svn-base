<?php

namespace Plat\Websocket\Staff;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * Undocumented class.
 *
 * @description   description
 * @Author  blake
 * @date  19.02.23
 * @links  url
 * 参数：
 *  修改参数：①admin_id：需要修改的用户的id ②admin_key：登录名称 ③admin_name：员工名 ④admin_password：登录密码 ⑤role_id:角色id
 *  200：添加成功
 *  201:员工名不能为空
 *  202：登录名不能为空
 *  203：登录密码不能为空
 *  204：角色id不能为空
 *  205：需修改的用户id格式不正确
 *  206：角色id格式不正确
 *  207：账号只能由数字字母组成，长度有效区间：[4-20]
 *  208：密码只能由数字字母组成，长度有效区间：[6-40]
 *  209：登录名已存在
 *  400：操作失败
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class StaffUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('account_admin_update', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();

        $admin_id = trim($data['admin_id']);
        $adminName = trim($data['admin_name']);
        $adminKey = trim($data['admin_key']);
        $adminPassword = trim($data['admin_password']);
        $roleId = trim($data['role_id']);

        // 为空判断
        if (empty($adminName)) {
            $context->reply(['status' => 202, 'msg' => '员工名称不能为空']);

            return;
        }
        if (mb_strlen($adminName) > 10 || mb_strlen($adminName) < 2) {
            $context->reply(['status' => 300, 'msg' => '请输入用户名,支持2-10位']);

            return;
        }
        if (empty($adminKey)) {
            $context->reply(['status' => 203, 'msg' => '登录名称不能为空']);

            return;
        }
        if (empty($roleId)) {
            $context->reply(['status' => 204, 'msg' => '角色id不能为空']);

            return;
        }
        if (!is_numeric($roleId)) {
            $context->reply(['status' => 206, 'msg' => '角色id格式不正确']);

            return;
        }
        // 验证规则
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/';
        if (!preg_match($preg, $adminKey)) {
            $context->reply(['status' => 207, 'msg' => '请输入正确账号,支持6-20位英文和数字']);

            return;
        }
        if (!empty($adminPassword)) {
            $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/';
            if (!preg_match($preg, $adminPassword)) {
                $context->reply(['status' => 208, 'msg' => '请输入正确密码,支持6-20位英文和数字']);

                return;
            }
        }

        // 判断登录名是否已经存在
        $mysql = $config->data_admin;
        try {
            $sql = 'SELECT admin_id FROM admin_auth WHERE admin_key = :admin_key';
            $params[':admin_key'] = $adminKey;
            foreach ($mysql->query($sql, $params) as $row) {
                if ($row['admin_id'] != $admin_id) {
                    $context->reply(['status' => 209, 'msg' => '登录名已存在']);

                    return;
                }
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '操作失败']);
            throw new \PDOException('sql run error'.$e);
        }

        try {
            // 更新info
            $sql = 'UPDATE admin_info SET admin_name = :admin_name WHERE admin_id = :admin_id';
            $params = [
                ':admin_name' => $adminName,
                ':admin_id' => $admin_id,
            ];
            $affectedRow = $mysql->execute($sql, $params);
            // 修改受影响行数为0，不记录日志
            if ($affectedRow === 1) {
                $this->logRecord($context, $mysql, $admin_id, 'updateInfo', 'account_admin_update');
            }

            if (empty($adminPassword)) {
                $sql = 'UPDATE admin_auth SET admin_key = :admin_key WHERE admin_id = :admin_id';
                $params = [
                    ':admin_key' => $adminKey,
                    ':admin_id' => $admin_id,
                ];
            } else {
                $sql = 'UPDATE admin_auth SET admin_key = :admin_key, password_hash = :admin_password WHERE admin_id = :admin_id';
                $params = [
                    ':admin_key' => $adminKey,
                    ':admin_password' => $adminPassword,
                    ':admin_id' => $admin_id,
                ];
            }
            $affectedRow = $mysql->execute($sql, $params);
            // 修改受影响行数为0，不记录日志
            if ($affectedRow === 1) {
                $this->logRecord($context, $mysql, $admin_id, 'updateAuth', 'account_admin_update');
            }

            // 更新appoint
            $sql = 'UPDATE admin_appoint SET role_id = :role_id WHERE admin_id = :admin_id';
            $params = [
                ':admin_id' => $admin_id,
                ':role_id' => $roleId,
            ];
            $affectedRow = $mysql->execute($sql, $params);
            // 修改受影响行数为0，不记录日志
            if ($affectedRow === 1) {
                $this->logRecord($context, $mysql, $admin_id, 'updateAppoint', 'account_admin_update');
            }

            $context->reply(['status' => 200, 'msg' => '操作成功']);
        } catch (\PDOException $e) {
            $context->reply(['status' => 404, 'msg' => '操作失败']);
            $this->logRecord($context, $mysql, $admin_id, 'sqlError', 'account_admin_insert');
            throw new \PDOException('sql run error'.$e);
        }
    }

    // 记录日志
    private function logRecord($context, $mysql, $admin_id, $signal, $operateKey)
    {
        switch ($signal) {
            case 'sqlError':
                $detail = '操作失败，sql错误';
                break;
            case 'updateInfo':
                $detail = '修改了编号为'.$admin_id.'的基本信息';
                break;
            case 'updateAuth':
                $detail = '修改了编号为'.$admin_id.'的登录信息';
                break;
            case 'updateAppoint':
                $detail = '修改了编号为'.$admin_id.'的角色信息';
                break;
        }
        try {
            $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $params = [
                ':admin_id' => $context->getInfo('adminId'),
                ':operate_key' => $operateKey,
                ':detail' => $detail,
            ];
            $mysql->execute($sql, $params);
        } catch (\PDOException $e) {
            throw new \PDOException('sql run error'.$e);
        }
    }
}
