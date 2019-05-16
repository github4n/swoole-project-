<?php

namespace Site\Websocket\Cash\DepositRecord;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-入款记录-第三方入款-强制入款
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/DepositRecord/DepositGateForce   deposit_serial deal_key finish_money
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositGateForce extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_deposit_deal', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staff_mysql = $config->data_staff;
        $mysqlUser = $config->data_user;
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');
        $data = $context->getData();
        $deposit_serial = $data['deposit_serial'];
        $user_key = $data['user_key'];

        if (empty($deposit_serial)) {
            $context->reply(['status' => 205, 'msg' => '入款单号不能为空']);

            return;
        }

        if (empty($user_key)) {
            $context->reply(['status' => 206, 'msg' => '用户参数错误']);

            return;
        }
        $user_sql = 'select deal_key,user_id from user_info_intact where user_key=:user_key';
        $user_info = [];
        foreach ($mysqlUser->query($user_sql, [':user_key' => $user_key]) as $row) {
            $user_info = $row;
        }
        if (empty($user_info)) {
            $context->reply(['status' => 300, 'msg' => '会员参数错误']);

            return;
        }
        $deal_key = $user_info['deal_key'];
        $user_id = $user_info['user_id'];
        $mysql = $config->__get('data_'.$deal_key);

        $sql = 'SELECT launch_money,passage_id FROM deposit_launch WHERE deposit_serial=:deposit_serial';
        $param = [':deposit_serial' => $deposit_serial];
        $finish_money = 0;
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $finish_money = $row['launch_money'];
                $passage_id = $row['passage_id'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '参数错误']);
            throw new \PDOException($e);
        }
        if (empty($passage_id)) {
            $context->reply(['status' => 300, 'msg' => '请输入正确入款单号']);

            return;
        }
        if ($MasterId != 0) {
            $sql = 'SELECT deposit_limit FROM staff_credit WHERE staff_id=:staff_id';
            $param = [':staff_id' => $staffId];
            $deposit = 0;
            foreach ($staff_mysql->query($sql, $param) as $row) {
                $deposit = $row['deposit_limit'];
            }
            if ($finish_money > $deposit) {
                $context->reply(['status' => 207, 'msg' => '该流水单号的入款限额大于当前登录账号的操作限额']);

                return;
            }
        }
        $sql = 'INSERT INTO deposit_finish SET deposit_serial=:deposit_serial, finish_money=:finish_money,coupon_audit=:coupon_audit,deposit_audit=:deposit_audit, finish_staff_id=:finish_staff_id,finish_staff_name=:finish_staff_name';
        $param = [
            ':deposit_serial' => $deposit_serial,
            ':finish_money' => $finish_money,
            ':coupon_audit' => 0,
            ':deposit_audit' => $finish_money,
            ':finish_staff_id' => $staffId,
            ':finish_staff_name' => $context->getInfo('StaffKey'),
        ];

        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 200, 'msg' => '入款成功']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':operate_key' => 'money_deposit_deal',
            ':client_ip' => ip2long($context->getClientAddr()),
            ':detail' => '强制为单号为'.$deposit_serial.'的入款',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '入款成功']);
        //更新账户的目前存款
        $sql = 'update deposit_passage set cumulate =+ :cumulate where passage_id=:passage_id';
        $staff_mysql->execute($sql, [':cumulate' => $finish_money, ':passage_id' => $passage_id]);
        //推送用户最新余额
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $user_mysql = $config->data_user;
        $sql = 'SELECT client_id FROM user_session WHERE user_id=:user_id';
        $param = ['user_id' => $user_id];
        foreach ($user_mysql->query($sql, $param) as $row) {
            $id = $row['client_id'];
            $taskAdapter->plan('NotifyApp', ['path' => 'User/Balance', 'data' => ['user_id' => $user_id, 'id' => $id, 'deal_key' => $deal_key]]);
        }
    }
}
