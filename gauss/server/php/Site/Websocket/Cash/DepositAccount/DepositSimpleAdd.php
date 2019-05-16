<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-入款账户管理-新增便捷支付网址
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/DepositAccount/DepositSimpleAdd {"passage_name":"测试通道便捷支付","acceptable":1,"pay_url":"http://www.baidu.com","risk_control":"100000"}
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositSimpleAdd extends CheckLogin
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
        $passage_name = $data['passage_name'];
        $acceptable = $data['acceptable'];
        $pay_url = $data['pay_url'];
        $risk_control = $data['risk_control'];   // 风控金额
        if (empty($passage_name)) {
            $context->reply(['status' => 203, 'msg' => '请输入入款通道名称']);

            return;
        }
        // 验证规则
        $preg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{4,20}$/u';
        if (!preg_match($preg, $passage_name)) {
            $context->reply(['status' => 205, 'msg' => '入款通道名称,请介于4-20位字符之间']);

            return;
        }

        if (empty($risk_control)) {
            $context->reply(['status' => 207, 'msg' => '请输入金额']);

            return;
        }
        if (!is_numeric($risk_control)) {
            $context->reply(['status' => 206, 'msg' => '请输入金额']);

            return;
        }
        if ($risk_control > 9999999.99) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if (empty($pay_url)) {
            $context->reply(['status' => 208, 'msg' => '请输入支付网址']);

            return;
        }
        if ($acceptable == 1) {
            $acceptable = 1;
        } elseif ($acceptable == 0) {
            $acceptable = 0;
        } else {
            $acceptable = 1;
        }
        //查找通道名称
        $sql = 'SELECT passage_id FROM deposit_passage WHERE passage_name=:passage_name';
        $param = [':passage_name' => $passage_name];
        $infos = array();
        foreach ($mysql->query($sql, $param) as $row) {
            $infos = $row;
        }
        if (!empty($infos)) {
            $context->reply(['status' => 214, 'msg' => '通道名称已存在']);

            return;
        }
        //新增入款账户
        $sql = 'INSERT INTO deposit_passage SET passage_name=:passage_name, risk_control=:risk_control, cumulate=:cumulate, acceptable=:acceptable';
        $param = [
            ':passage_name' => $passage_name,
            ':risk_control' => empty($risk_control) ? 0 : $risk_control,
            ':cumulate' => 0,
            ':acceptable' => $acceptable,
        ];
        try {
            $mysql->execute($sql, $param);
            $sql = 'SELECT last_insert_id() as passage_id';
            foreach ($mysql->query($sql) as $row) {
                $passage_id = $row['passage_id'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 401, 'msg' => '新增失败']);
            throw new \PDOException($e);
        }

        $sqls = 'INSERT INTO deposit_passage_simple SET passage_id=:passage_id, pay_url=:pay_url';
        $param = [
            ':passage_id' => $passage_id,
            ':pay_url' => $pay_url,
        ];
        try {
            $mysql->execute($sqls, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '新增失败']);
            throw new \PDOException($e);
        }
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip=:client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_passage',
            ':detail' => '新增入款账户便捷入款通道'.$passage_id,
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '新增成功']);
    }
}
