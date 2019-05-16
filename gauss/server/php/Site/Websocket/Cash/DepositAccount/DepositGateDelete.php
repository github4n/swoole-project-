<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-删除第三方入款账号
 * @Author  Rose
 * @date  2019-04-26
 * @links  Cash/DepositAccount/DepositGateDelete {"passage_id":2}
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositGateDelete extends CheckLogin
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
        if (!in_array('money_deposit_passage', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_staff;
        $passage_id = $data['passage_id'];
        if (!is_numeric($passage_id)) {
            $context->reply(['status' => 203, 'msg' => '参数id缺失']);

            return;
        }
        if ($this->checkOrder($passage_id, $config)) {
            $context->reply(['status' => 300, 'msg' => '还有未完成的入款订单，禁止删除']);

            return;
        }
        $sql = 'DELETE FROM deposit_passage_gate WHERE passage_id=:passage_id';
        $sqls = 'DELETE FROM deposit_passage WHERE passage_id=:passage_id';
        $param = [':passage_id' => $passage_id];
        try {
            $mysql->execute($sql, $param);
            $mysql->execute($sqls, $param);
            //记录日志
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_passage',
            ':detail' => '删除第三方入款账户的信息'.$passage_id,
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '删除成功']);
    }
}
