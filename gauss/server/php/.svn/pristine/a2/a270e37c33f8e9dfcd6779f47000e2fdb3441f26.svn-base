<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-修改公司入款账号
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/DepositAccount/DepositGateUpdate {"passage_id":1,"passage_name":"小额支付","risk_control":500000,"acceptable":1,"gate_key":"99bill","gate_name":"快钱","account_number":"45778","signature_key":"sdjjskhdsefeffdfwe","encrypt_key":"defrefewrffewfrewfr","jump_url":"sadsafdsfsfdsfsd","api_url":""}
 * @modifyAuthor
 * @modifyDate
 *
 *  */

class DepositGateUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_staff;
        $passage_id = $data['passage_id'];
        $account_name = $data['passage_name'];  // 通道名称
        $risk_control = $data['risk_control'];   // 风控金额
        $acceptable = $data['acceptable']; // 启用是否 1启用 2停用
        $gate_key = $data['gate_key']; // 三方key
        $gate_name = $data['gate_name']; //三方名字
        $account_number = $data['account_number']; // 商户号
        $signature_key = $data['signature_key'];  //签名key
        $encrypt_key = $data['encrypt_key'];  //加密key
        $jump_url = $data['jump_url'];  //加密key
        $api_url = isset($data['api_url']) ? $data['api_url'] : '';
        if (!is_numeric($passage_id)) {
            $context->reply(['status' => 209, 'msg' => '参数id缺失']);

            return;
        }
        if (empty($account_name)) {
            $context->reply(['status' => 203, 'msg' => '通道名称不能为空']);

            return;
        }
        // 验证规则
        $preg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{4,20}$/u';
        if (!preg_match($preg, $account_name)) {
            $context->reply(['status' => 205, 'msg' => '入款通道名称,请介于4-20位之间']);

            return;
        }
        if (empty($risk_control)) {
            $context->reply(['status' => 204, 'msg' => '请输入金额']);

            return;
        }
        if (!is_numeric($risk_control)) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if ($risk_control > 9999999.99) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if (empty($gate_key)) {
            $context->reply(['status' => 207, 'msg' => '请选择支付平台']);

            return;
        }
        if (empty($gate_name)) {
            $context->reply(['status' => 206, 'msg' => '请选择支付平台']);

            return;
        }
        if (empty($account_number)) {
            $context->reply(['status' => 208, 'msg' => '请先写商家id']);

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
            $context->reply(['status' => 211, 'msg' => '请输入签名key']);

            return;
        }
        if (empty($jump_url)) {
            $context->reply(['status' => 215, 'msg' => '请输入商城网址']);

            return;
        }
        //更新通道信息
        $sql = 'UPDATE deposit_passage SET risk_control=:risk_control, acceptable=:acceptable , passage_name=:passage_name WHERE passage_id=:passage_id';
        $param = [
            ':risk_control' => $risk_control,
            ':acceptable' => $acceptable,
            ':passage_id' => $passage_id,
            ':passage_name' => $account_name,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //更新三方信息
        $sql = 'UPDATE deposit_passage_gate SET gate_key=:gate_key,gate_name=:gate_name,account_number=:account_number,account_name=:account_name,signature_key=:signature_key,encrypt_key=:encrypt_key,jump_url=:jump_url,api_url=:api_url WHERE passage_id=:passage_id';
        $param = [
            ':gate_key' => $gate_key,
            ':gate_name' => $gate_name,
            ':account_number' => $account_number,
            ':account_name' => $account_name,
            ':signature_key' => $signature_key,
            ':encrypt_key' => $encrypt_key,
            ':passage_id' => $passage_id,
            ':jump_url' => $jump_url,
            ':api_url' => $api_url,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_passage',
            ':detail' => '修改三方入款账户的信息'.$passage_id,
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
