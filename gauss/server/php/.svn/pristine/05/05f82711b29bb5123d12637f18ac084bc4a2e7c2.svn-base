<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description    现金系统-入款账户管理-便捷支付列表
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/DepositAccount/DepositSimpleList
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositSimpleList extends CheckLogin
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
        $acceptable = isset($data['acceptable']) && !empty($data['acceptable']) ? $data['acceptable'] : '';
        $sql = 'SELECT * FROM deposit_passage_simple_intact';
        $total_sql = 'SELECT passage_id FROM deposit_passage_simple_intact';
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
            $sql .= ' where acceptable = :acceptable ';
            $total_sql .= ' where acceptable = :acceptable ';
            $param[':acceptable'] = $acceptable;
            $params[':acceptable'] = $acceptable;
        }
        $sql .= ' order by passage_id desc';
        $sql .= ' limit 1000';
        $list = array();
        try {
            foreach ($mysql->query($sql, $param) as $rows) {
                $list[] = $rows;
            }
            $total = $mysql->execute($total_sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 206, 'msg' => '获取列表失败']);
            throw new \PDOException($e);
        }
        $context->reply([
            'status' => 200,
            'msg' => '信息获取成功',
            'total' => $total,
            'list' => $list,
        ]);
    }
}
