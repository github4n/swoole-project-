<?php

namespace Site\Websocket\Member\MemberList;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberCashPwd class.
 *
 * @description   会员管理-会员列表-修改提现密码
 * @Author  blake
 * @date  2019-05-08
 * @links   Member/MemberList/MemberCashPwd {"new_cash_password":"123456","confirm_cash_password":"123456","user_id":1}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class MemberCashPwd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffId = $context->getInfo('StaffId');
        $mysql = $config->data_user;
        $data = $context->getData();
        $user_id = $data['user_id'];
        $new_cash_password = $data['new_cash_password'];
        $confirm_cash_password = $data['confirm_cash_password'];
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
        if (empty($new_cash_password)) {
            $context->reply(['status' => 203, 'msg' => '提现密码不能为空']);

            return;
        }
        if (empty($confirm_cash_password)) {
            $context->reply(['status' => 204, 'msg' => '确认密码不能为空']);

            return;
        }
        if ($confirm_cash_password != $new_cash_password) {
            $context->reply(['status' => 206, 'msg' => '两次密码输入不一致']);

            return;
        }
        $preg = '/^[0-9]{6}$/';
        if (!preg_match($preg, $new_cash_password) && !preg_match($preg, $confirm_cash_password)) {
            $context->reply(['status' => 205, 'msg' => '密码格式不正确']);

            return;
        }
        $sql = 'SELECT bank_name FROM bank_info WHERE user_id=:user_id';
        $param = [':user_id' => $user_id];
        $bank_info = iterator_to_array($mysql->query($sql, $param));
        if (empty($bank_info)) {
            $context->reply(['status' => 207, 'msg' => '该用户还未绑定银行卡']);

            return;
        }
        $sql = 'UPDATE bank_info SET password_hash=:password_hash WHERE user_id=:user_id';
        $param = [':user_id' => $user_id, ':password_hash' => $confirm_cash_password];

        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'user_list_update',
            ':detail' => '修改会员'.$user_id.'的提现密码',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
    }
}
