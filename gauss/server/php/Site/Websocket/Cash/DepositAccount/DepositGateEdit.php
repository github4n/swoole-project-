<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description    现金系统-添加公司入款账号
 * @Author  Rose
 * @date  2019-04-26
 * @links  Cash/DepositAccount/DepositGateEdit {"passage_id":2}
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositGateEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_staff;
        $passage_id = $data['passage_id'];
        if (!is_numeric($passage_id)) {
            $context->reply(['status' => 203, 'msg' => '参数类型错误']);

            return;
        }
        $sql = 'SELECT passage_id, passage_name ,gate_key,gate_name,acceptable,account_number,risk_control,signature_key,encrypt_key,jump_url,api_url FROM deposit_passage_gate_intact WHERE passage_id=:passage_id';
        $param = [':passage_id' => $passage_id];
        $info = array();
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '信息获取失败']);
            throw new \PDOException($e);
        }
        if (empty($info)) {
            $context->reply(['status' => 204, 'msg' => '检查参数是否正确']);

            return;
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'info' => $info]);
    }
}
