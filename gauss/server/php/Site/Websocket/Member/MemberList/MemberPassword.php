<?php

namespace Site\Websocket\Member\MemberList;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberPassword class.
 *
 * @description   会员管理-会员列表-会员信息-登录密码管理
 * @Author  blake
 * @date  2019-05-08
 * @links   Member/MemberList/MemberPassword {"user_id":2,"new_password":"admin123","confirm_password":"admin123"}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class MemberPassword extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_user;
        $user_id = $data['user_id'];
        $new_password = trim($data['new_password']);
        $confirm_password = trim($data['confirm_password']);
        $staffGrade = $context->getInfo('StaffGrade');
        if ($staffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_list_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        if (empty($new_password)) {
            $context->reply(['status' => 203, 'msg' => '请输入新密码']);

            return;
        }
        if (empty($confirm_password)) {
            $context->reply(['status' => 204, 'msg' => '请输入确认密码']);

            return;
        }
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/';
        if (!preg_match($preg, $new_password)) {
            $context->reply(['status' => 206, 'msg' => '密码请输入6-12位英文数字组合']);

            return;
        }
        if ($new_password !== $confirm_password) {
            $context->reply(['status' => 207, 'msg' => '两次输入的密码不一致,请重新输入']);

            return;
        }
        $sql = 'UPDATE user_auth SET password_hash = :password_hash WHERE user_id = :user_id';
        $param = [':password_hash' => $new_password, ':user_id' => $user_id];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'user_list_update',
            ':detail' => '修改会员'.$user_id.'的登录密码',
        ];
        $mysqls = $config->data_staff;
        $mysqls->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '修改成功',
        ]);
    }
}
