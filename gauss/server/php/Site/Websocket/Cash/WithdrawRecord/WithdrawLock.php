<?php

namespace Site\Websocket\Cash\WithdrawRecord;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description 现金系统-出款记录-加锁
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/WithdrawRecord/WithdrawLock {"user_key":"deal3","withdraw_serial":"181219130436000013"}
 * @param status 1等待入款 2入款成功 3入款失败
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class WithdrawLock extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_withdraw_deal', $auth)) {
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
        $sql = 'INSERT INTO withdraw_lock SET withdraw_serial=:withdraw_serial, lock_type=:lock_type, lock_staff_id=:lock_staff_id, lock_staff_name=:lock_staff_name';
        $param = [
            ':withdraw_serial' => $withdraw_serial,
            ':lock_type' => 0,
            ':lock_staff_id' => $staffId,
            ':lock_staff_name' => $staffKey,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '加锁失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '锁定成功']);
        //记录操作日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_withdraw_deal',
            ':detail' => '为单号为'.$withdraw_serial.'出款申请加出款锁',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
    }
}
