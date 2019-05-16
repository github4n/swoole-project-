<?php

namespace Site\Websocket\Cash\WithdrawReview;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-审核出款-同意出款
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/WithdrawReview/WithdrawAccept {"user_key":"deal3","withdraw_serial":"181219130436000013"}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class WithdrawAccept extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_withdraw_accept', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $staffKey = $context->getInfo('StaffKey');
        $data = $context->getData();
        $mysqlUser = $config->data_user;
        $withdraw_serial = $data['withdraw_serial'];
        $user_key = $data['user_key'];
        if (empty($user_key)) {
            $context->reply(['status' => 206, 'msg' => '用户参数错误']);

            return;
        }
        if (empty($withdraw_serial)) {
            $context->reply(['status' => 205, 'msg' => '出款单号不能为空']);

            return;
        }
        $user_sql = 'select deal_key,layer_id,user_id from user_info_intact where user_key=:user_key';
        $user_info = [];
        foreach ($mysqlUser->query($user_sql, [':user_key' => $user_key]) as $row) {
            $user_info = $row;
        }
        if (empty($user_info)) {
            $context->reply(['status' => 300, 'msg' => '会员参数错误']);

            return;
        }
        $user_id = $user_info['user_id'];
        $deal_key = $user_info['deal_key'];
        $mysql = $config->__get('data_'.$deal_key);
        //判断该出款的记录没有锁定就不能操作
        $info = [];
        $sql = 'select withdraw_serial,lock_staff_id from withdraw_lock where withdraw_serial=:withdraw_serial and lock_type=1';
        foreach ($mysql->query($sql, [':withdraw_serial' => $withdraw_serial]) as $row) {
            $info = $row;
        }
        if (empty($info)) {
            $context->reply(['status' => 401, 'msg' => '还未锁定该订单']);

            return;
        } else {
            if ($info['lock_staff_id'] != $staffId) {
                $context->reply(['status' => 402, 'msg' => '该订单已被其他员工锁定']);

                return;
            }
        }
        //判断该订单是否已经操作
        $sql = 'select withdraw_serial from withdraw_intact where withdraw_serial=:withdraw_serial and (reject_time is not null or accept_time is not null)';
        $infos = [];
        foreach ($mysql->query($sql, [':withdraw_serial' => $withdraw_serial]) as $rows) {
            $infos = $rows;
        }
        if (!empty($infos)) {
            $context->reply(['status' => 210, 'msg' => '该订单已操作']);

            return;
        }

        $sql = 'INSERT INTO withdraw_accept SET withdraw_serial=:withdraw_serial, accept_staff_id=:accept_staff_id, accept_staff_name=:accept_staff_name';
        $param = [
            ':withdraw_serial' => $withdraw_serial,
            ':accept_staff_id' => $staffId,
            ':accept_staff_name' => $staffKey,
        ];

        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '操作失败']);
            throw new \PDOException($e);
        }
        //解锁
        $sql = 'DELETE FROM withdraw_lock WHERE withdraw_serial=:withdraw_serial ';
        $param = [
            ':withdraw_serial' => $withdraw_serial,
        ];
        $mysql->execute($sql, $param);
        $context->reply(['status' => 200, 'msg' => '操作成功']);
        //记录操作日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_withdraw_accept',
            ':detail' => '审核同意单号为'.$withdraw_serial.'的出款',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
    }
}
