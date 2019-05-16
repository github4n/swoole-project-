<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-第三方入款平台列表
 * @Author  Rose
 * @date  2019-04-26
 * @links  Cash/DepositAccount/DepositGateList {"acceptable":1}  1启用，2停用
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositGateList extends CheckLogin
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

        $data = $context->getData();
        $mysql = $config->data_staff;
        $acceptable = isset($data['acceptable']) ? $data['acceptable'] : '';
        $account_name = isset($data['account_name']) ? $data['account_name'] : '';
        $sql = 'SELECT * FROM deposit_passage_gate_intact WHERE 1=1 ';
        $total_sql = 'SELECT passage_id FROM deposit_passage_gate_intact WHERE 1=1 ';
        $param = [];
        $params = [];
        if (!empty($acceptable)) {
            if ($acceptable == 1) {
                $acceptable = 1;
            } elseif ($acceptable == 2) {
                $acceptable = 0;
            } else {
                $context->reply(['status' => 204, 'msg' => '状态参数错误']);

                return;
            }
            $sql .= ' and acceptable = :acceptable ';
            $total_sql .= ' and acceptable = :acceptable ';
            $param[':acceptable'] = $acceptable;
            $params[':acceptable'] = $acceptable;
        }
        if (!empty($account_name)) {
            $sql .= ' and account_name = :account_name ';
            $total_sql .= ' and account_name = :account_name ';
            $param[':account_name'] = $account_name;
            $params[':account_name'] = $account_name;
        }
        $sql .= ' order by passage_id desc';
        $sql .= ' limit 1000';

        $list = [];
        try {
            foreach ($mysql->query($sql, $param) as $rows) {
                $list[] = $rows;
            }
            $total = $mysql->execute($total_sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 206, 'msg' => '获取列表失败']);
            throw new \PDOException($e);
        }
        $lists = array();
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $lists[$key]['passage_id'] = $val['passage_id'];
                $lists[$key]['account_name'] = $val['passage_name'];
                $lists[$key]['gate_name'] = $val['gate_name'];
                $lists[$key]['account_number'] = $val['account_number'];
                $lists[$key]['risk_control'] = $val['risk_control'];
                $lists[$key]['cumulate'] = $val['cumulate'];
                $lists[$key]['acceptable'] = $val['acceptable'];
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '信息获取成功',
            'total' => $total,
            'list' => $lists,
        ]);
    }
}
