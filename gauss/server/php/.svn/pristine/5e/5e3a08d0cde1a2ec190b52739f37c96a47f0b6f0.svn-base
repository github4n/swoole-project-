<?php

namespace Plat\Websocket\Admin;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * ModifyPassWord class.
 *
 * @description   修改密码
 * @Author  avery
 * @date  2019-05-08
 * @links  url
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class ModifyPassWord extends CheckLogin
{
    //修改用户自己登录密码，然后退出登录
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $old_password = trim($data['admin_old_password']);
        $new_password = trim($data['admin_new_password']);
        $confirm_password = trim($data['admin_confirm_password']);

        if (empty($old_password)) {
            $context->reply(['status' => 201, 'msg' => '旧密码不能为空']);

            return;
        }
        if (empty($new_password)) {
            $context->reply(['status' => 203, 'msg' => '新密码不能为空']);

            return;
        }
        if (empty($confirm_password)) {
            $context->reply(['status' => 204, 'msg' => '确认密码不能为空']);

            return;
        }
        if ($new_password != $confirm_password) {
            $context->reply(['status' => 205, 'msg' => '两次填写的新密码不一致']);

            return;
        }

        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,40}$/';
        if (!preg_match($preg, $new_password)) {
            $context->reply(['status' => 206, 'msg' => '新密码格式不正确']);

            return;
        }
        $mysql = $config->data_admin;
        //查询用户名
        $sql = 'SELECT admin_key FROM admin_auth WHERE admin_id=:admin_id';
        $params = [
            ':admin_id' => $context->getInfo('adminId'),
        ];
        $adminn_key = [];
        foreach ($mysql->query($sql, $params) as $row) {
            $adminn_key = $row;
        }
        if (empty($adminn_key)) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);

            return;
        }

        //查询原密码
        $adminn_key = $adminn_key['admin_key'];
        $sql = 'CALL admin_auth_verify(:adminKey, :password)';
        $params = [
            ':adminKey' => $adminn_key,
            ':password' => $old_password,
        ];
        $old_password = [];
        foreach ($mysql->query($sql, $params) as $row) {
            $old_password = $row;
        }
        if (empty($old_password)) {
            $context->reply(['status' => 202, 'msg' => '旧密码输入错误']);

            return;
        }

        //存入新密码
        try {
            $sql = 'UPDATE admin_auth SET password_hash=:password_hash WHERE admin_id=:admin_id';
            $params = [
                ':password_hash' => $new_password,
                ':admin_id' => $context->getInfo('adminId'),
            ];
            $res = $mysql->execute($sql, $params);
            if ($res == 0) {
                $context->reply(['status' => 400, 'msg' => '修改失败']);
            } else {
                //提交到日志
                $sql = 'INSERT INTO operate_log SET admin_id=:admin_id,operate_key=:operate_key,detail=:detail';
                $params = [
                    ':admin_id' => $context->getInfo('adminId'),
                    ':operate_key' => 'self_password',
                    ':detail' => '修改登录密码',
                ];
                $mysql->execute($sql, $params);
                $context->reply(['status' => 200, 'msg' => '修改成功']);
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException('修改密码运行sql语句错误'.$e);
        }
    }
}
