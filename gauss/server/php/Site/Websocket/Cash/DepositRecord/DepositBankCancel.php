<?php

namespace Site\Websocket\Cash\DepositRecord;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-入款记录-未收到
 * @Author  Rose
 * @date  2019-04-26
 * @links  Cash/DepositRecord/DepositBankCancel {"deposit_serial":"181217155352000009","deal_key":"deal4"}
 * @modifyAuthor
 * @modifyDate
 * @param status 1等待入款 2入款成功 3入款失败
 * */

class DepositBankCancel extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_deposit_deal', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $mysqlUser = $config->data_user;
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $deposit_serial = $data['deposit_serial'];
        $user_key = $data['user_key'];
        if (empty($deposit_serial)) {
            $context->reply(['status' => 204, 'msg' => '参数错误']);

            return;
        }
        if (empty($user_key)) {
            $context->reply(['status' => 206, 'msg' => '用户参数错误']);

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

        $sql = 'select deposit_serial from deposit_finish where deposit_serial=:deposit_serial';
        $result = $mysql->execute($sql, [':deposit_serial' => $deposit_serial]);
        if ($result > 0) {
            $context->reply(['status' => 300, 'msg' => '该入款已处理']);

            return;
        }

        $sqls = 'INSERT INTO deposit_cancel SET deposit_serial=:deposit_serial, cancel_reason=:cancel_reason, cancel_staff_id=:cancel_staff_id,cancel_staff_name=:cancel_staff_name';
        $params = [
            ':deposit_serial' => $deposit_serial,
            ':cancel_reason' => '未收到款',
            ':cancel_staff_id' => $staffId,
            ':cancel_staff_name' => $context->getInfo('StaffKey'),
        ];
        try {
            $mysql->execute($sqls, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '操作失败']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_deal',
            ':detail' => '未收到入款单号为'.$deposit_serial.'的入款',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '操作成功']);
    }
}
