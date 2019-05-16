<?php

namespace Site\Websocket\Cash\ManualDeposit;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * @description   现金系统-手工出款查询
 * @Author  Rose
 * @date  2019-05-08
 * @links  Cash/ManualDeposit/WithdrawSearch
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class WithdrawSearch extends CheckLogin
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
        if (!in_array('money_manual', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_withdraw_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }

        $masterId = $context->getInfo('MasterId');
        $staffId = $context->getInfo('StaffId');
        $mysqlStaff = $config->data_staff;

        $data = $context->getData();
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        $account_name = isset($data['account_name']) ? $data['account_name'] : '';
        $start_time = isset($data['start_time']) ? $data['start_time'] : '';
        $end_time = isset($data['end_time']) ? $data['end_time'] : '';
        $staff_name = isset($data['staff_name']) ? $data['staff_name'] : '';
        $withdraw_type = isset($data['withdraw_type']) ? $data['withdraw_type'] : '';

        $layerLists = [];
        if ($masterId != 0) {
            $sql = 'select deposit_limit,layer_id_list from staff_info_intact where staff_id=:staff_id';
            $deposit_info = [];
            foreach ($mysqlStaff->query($sql, [':staff_id' => $staffId]) as $row) {
                $deposit_info = $row;
            }
            $deposit_limit = $deposit_info['deposit_limit'];
            $layerLists = json_decode($deposit_info['layer_id_list'], true);
        }
        if ($masterId == 0) {
            //查询数据sql
            $sql = 'SELECT user_id,user_key,account_name,withdraw_type,money,deposit_audit,deposit_audit,coupon_audit,new_money,deal_time, staff_name,memo FROM staff_withdraw_intact WHERE 1=1 ';
            //总数sql
            $total_sql = 'SELECT deal_serial FROM staff_withdraw_intact WHERE 1=1 ';
            $param = [];
        } else {
            //查询数据sql
            $sql = 'SELECT user_id,user_key,account_name,withdraw_type,money,deposit_audit,deposit_audit,coupon_audit,new_money,deal_time, staff_name,memo FROM staff_withdraw_intact WHERE layer_id in :layer_list and money<= :money ';
            //总数sql
            $total_sql = 'SELECT deal_serial FROM staff_withdraw_intact WHERE layer_id in :layer_list and money<= :money ';
            $param = [':layer_list' => $layerLists, ':money' => $deposit_limit];
        }

        if (!empty($user_key)) {
            $sql .= ' AND user_key=:user_key';
            $total_sql .= ' AND user_key=:user_key';
            $param[':user_key'] = $user_key;
        }
        if (!empty($account_name)) {
            $sql .= ' AND account_name=:account_name';
            $total_sql .= ' AND account_name=:account_name';
            $param[':account_name'] = $account_name;
        }
        if (!empty($staff_name)) {
            $sql .= ' AND staff_name=:staff_name';
            $total_sql .= ' AND staff_name=:staff_name';
            $param[':staff_name'] = $staff_name;
        }
        if (is_numeric($withdraw_type)) {
            $sql .= ' AND withdraw_type=:withdraw_type';
            $total_sql .= ' AND withdraw_type=:withdraw_type';
            $param[':withdraw_type'] = $withdraw_type;
        }
        if (!empty($start_time) && !empty($end_time)) {
            $start = strtotime(date('Ymd', strtotime($start_time)).' 00:00:00');
            $end = strtotime(date('Ymd', strtotime($end_time)).' 23:59:59');
            $sql .= ' AND deal_time BETWEEN :start_time and :end_time';
            $total_sql .= ' AND deal_time BETWEEN :start_time and :end_time';
            $param[':start_time'] = $start;
            $param[':end_time'] = $end;
        }
        $sql .= ' LIMIT 100';
        $total_sql .= ' LIMIT 100';
        $total = 0;
        $withdraw_list = [];

        foreach ($config->deal_list as $deal) {
            $mysql = $config->__get('data_'.$deal);

            try {
                //总人数
                $total += $mysql->execute($total_sql, $param);

                $list = iterator_to_array($mysql->query($sql, $param));

                if (!empty($list)) {
                    foreach ($list as $key => $value) {
                        $withdraw = [
                            'user_id' => $value['user_id'],
                            'user_key' => $value['user_key'],
                            'account_name' => !empty($value['account_name']) ? $value['account_name'] : '',
                            'withdraw_type' => $value['withdraw_type'],
                            'money' => $value['money'],
                            'deposit_audit' => $value['deposit_audit'],
                            'coupon_audit' => $value['coupon_audit'],
                            'new_money' => $value['new_money'],
                            'deal_time' => !empty($value['deal_time']) ? date('Y-m-d H:i:s', $value['deal_time']) : '',
                            'staff_name' => $value['staff_name'],
                            'memo' => !empty($value['memo']) ? $value['memo'] : '',
                        ];
                        $withdraw_list[] = $withdraw;
                    }
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取失败']);
                throw new \PDOException($e);
            }
        }
        array_multisort(array_column($withdraw_list, 'deal_time'), SORT_DESC, $withdraw_list);

        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_manual',
            ':detail' => '查看了手工出款查询',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total' => $total,  'list' => $withdraw_list]);
    }
}
