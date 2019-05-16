<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-添加第三方入款账号
 * @Author  Rose
 * @date  2019-04-26
 * @links  Cash/DepositAccount/DepositGateAdd {"passage_name":"支付宝测试通道1","risk_control":500000,"acceptable":1,"gate_way":{"gate_key":"99bill","gate_name":"快钱"},"account_number":"45778","signature_key":"sdjjskhdsefeffdfwe","encrypt_key":"defrefewrffewfrewfr","jump_url":"weriueewhew","api_url":"api_url"}
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositGateAdd extends CheckLogin
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
        $passage_name = $data['passage_name'];  // 通道名称
        $risk_control = $data['risk_control'];   // 风控金额
        $acceptable = $data['acceptable']; // 启用是否 1启用 2停用
        $gate_way = $data['gate_way']; // 三方方式
        $account_number = $data['account_number']; // 商户号
        $signature_key = $data['signature_key'];  //签名key
        $encrypt_key = isset($data['encrypt_key']) ? $data['encrypt_key'] : '';  //加密key
        $api_url = isset($data['api_url']) ? $data['api_url'] : '';
        $jump_url = $data['jump_url']; //商城网址
        if (empty($passage_name)) {
            $context->reply(['status' => 203, 'msg' => '请输入入款通道']);

            return;
        }
        // 验证规则
        $preg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{4,20}$/u';
        if (!preg_match($preg, $passage_name)) {
            $context->reply(['status' => 205, 'msg' => '入款通道名称,请介于4-20位字符之间']);

            return;
        }
        if (empty($risk_control)) {
            $context->reply(['status' => 204, 'msg' => '请输入金额']);

            return;
        }
        if (!is_numeric($risk_control)) {
            $context->reply(['status' => 205, 'msg' => '请输入正确的金额']);

            return;
        }
        if (empty($gate_way)) {
            $context->reply(['status' => 206, 'msg' => '请选择支付平台']);

            return;
        }
        if (!is_array($gate_way)) {
            $context->reply(['status' => 206, 'msg' => '请选择正确的平台']);

            return;
        }
        if (empty($account_number)) {
            $context->reply(['status' => 208, 'msg' => '请输入商家id']);

            return;
        }
        if (empty($acceptable)) {
            $context->reply(['status' => 210, 'msg' => '请选择是否启用']);

            return;
        }
        if ($acceptable == 1) {
            $acceptable = 1;
        } elseif ($acceptable == 2) {
            $acceptable = 0;
        } else {
            $acceptable = 1;
        }
        if (empty($signature_key)) {
            $context->reply(['status' => 211, 'msg' => '请输入签名KEY']);

            return;
        }
        if (empty($jump_url)) {
            $context->reply(['status' => 215, 'msg' => '请输入商城网址']);

            return;
        }

        //查找通道名称
        $sql = 'SELECT passage_id FROM deposit_passage WHERE passage_name=:passage_name';
        $param = [':passage_name' => $passage_name];
        $infos = array();
        foreach ($mysql->query($sql, $param) as $row) {
            $infos = $row;
        }
        if (!empty($infos)) {
            $context->reply(['status' => 214, 'msg' => '该通道名称已被创建,请重新输入']);

            return;
        }
        //添加基本通道信息
        $sql = 'INSERT INTO deposit_passage SET passage_name=:passage_name, risk_control=:risk_control, cumulate=:cumulate, acceptable=:acceptable';
        $param = [
            ':passage_name' => $passage_name,
            ':risk_control' => $risk_control,
            ':cumulate' => 0,
            ':acceptable' => $acceptable,
        ];
        try {
            $mysql->execute($sql, $param);
            $sql = 'SELECT last_insert_id() as passage_id';
            foreach ($mysql->query($sql) as $row) {
                $passage_id = $row['passage_id'];
            }
            //记录日志
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '新增失败']);
            throw new \PDOException($e);
        }
        //新增三方信息
        $sql = 'INSERT INTO deposit_passage_gate SET passage_id=:passage_id, gate_key=:gate_key, gate_name=:gate_name, account_number=:account_number, account_name=:account_name, signature_key=:signature_key, encrypt_key=:encrypt_key,jump_url=:jump_url,api_url=:api_url';
        $param = [
            ':passage_id' => $passage_id,
            ':gate_key' => $gate_way['gate_key'],
            ':gate_name' => $gate_way['gate_name'],
            ':account_number' => $account_number,
            ':account_name' => $passage_name,
            ':signature_key' => $signature_key,
            ':encrypt_key' => $encrypt_key,
            ':jump_url' => $jump_url,
            ':api_url' => $api_url,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '新增失败']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_passage',
            ':detail' => '新增第三方入款账户的信息'.$passage_id,
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '新增成功']);
    }
}
