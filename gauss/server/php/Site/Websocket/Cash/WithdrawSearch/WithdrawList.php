<?php

namespace Site\Websocket\Cash\WithdrawSearch;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-出款查询
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/WithdrawSearch/WithdrawList {}
 * @param status:审核状态 1已审核同意出款 2 已审核-拒绝出款 3未审核
 * @param withdraw_status 出款状态  1等待出款2正在出款3出款完成4出款失败5拒绝出款
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class WithdrawList extends CheckLogin
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
        if (!in_array('money_withdraw_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staff_mysql = $config->data_staff;
        //代理层级列表
        $cache = $config->cache_site;
        $layer_list = $this->LayerManage($context, $config);

        $staffId = $context->getInfo('StaffId');
        $masterId = $context->getInfo('MasterId');
        $mysqlStaff = $config->data_staff;
        $data = $context->getData();
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        $withdraw_serial = isset($data['withdraw_serial']) ? $data['withdraw_serial'] : '';
        $staff_name = isset($data['staff_name']) ? $data['staff_name'] : '';
        $status = isset($data['status']) ? $data['status'] : '';
        $layer_id = isset($data['layer_id']) ? $data['layer_id'] : '';
        $withdraw_name = isset($data['withdraw_name']) ? $data['withdraw_name'] : '';
        $min_money = isset($data['min_money']) ? $data['min_money'] : '';
        $max_money = isset($data['max_money']) ? $data['max_money'] : '';
        $start_time = isset($data['start_time']) ? $data['start_time'] : '';
        $end_time = isset($data['end_time']) ? $data['end_time'] : '';
        $start_finish_time = isset($data['start_finish_time']) ? $data['start_finish_time'] : '';
        $end_finish_time = isset($data['end_finish_time']) ? $data['end_finish_time'] : '';
        if ($masterId != 0) {
            $sql = 'select withdraw_limit,layer_id_list from staff_info_intact where staff_id=:staff_id';
            $deposit_info = [];
            foreach ($mysqlStaff->query($sql, [':staff_id' => $staffId]) as $row) {
                $deposit_info = $row;
            }
            $withdraw_limit = $deposit_info['withdraw_limit'];
            $layerLists = json_decode($deposit_info['layer_id_list'], true);
        }
        if ($masterId == 0) {
            $sql = 'SELECT withdraw_serial,user_id,user_key,layer_id,launch_money,deposit_audit,handling_fee,withdraw_money,bank_name,bank_branch,account_number,account_name,launch_time,must_inspect,accept_staff_name,accept_time,reject_staff_name,reject_time,finish_staff_name,finish_time,cancel_staff_name,cancel_time,lock_type,lock_time,lock_staff_name,reject_reason,cancel_reason FROM withdraw_intact WHERE 1=1 ';
            $total_sql = 'SELECT withdraw_serial FROM withdraw_intact WHERE 1=1 ';
            $params = [];
        } else {
            $sql = 'SELECT withdraw_serial,user_id,user_key,layer_id,launch_money,deposit_audit,handling_fee,withdraw_money,bank_name,bank_branch,account_number,account_name,launch_time,must_inspect,accept_staff_name,accept_time,reject_staff_name,reject_time,finish_staff_name,finish_time,cancel_staff_name,cancel_time,lock_type,lock_time,lock_staff_name,reject_reason,cancel_reason FROM withdraw_intact WHERE layer_id in :layer_list and launch_money <= :launch_money ';
            $total_sql = 'SELECT withdraw_serial FROM withdraw_intact WHERE layer_id in :layer_list and launch_money <= :launch_money ';
            $params = [':layer_list' => $layerLists, ':launch_money' => $withdraw_limit];
        }
        if (!empty($user_key)) {
            $sql .= ' AND user_key = :user_key';
            $total_sql .= ' AND user_key = :user_key';
            $params[':user_key'] = $user_key;
        }
        if (!empty($withdraw_serial)) {
            $sql .= ' AND withdraw_serial=:withdraw_serial';
            $total_sql .= ' AND withdraw_serial=:withdraw_serial';
            $params[':withdraw_serial'] = $withdraw_serial;
        }
        if ($staff_name) {
            $sql .= ' AND (accept_staff_name = :accept_staff_name or reject_staff_name=:reject_staff_name)';
            $total_sql .= ' AND (accept_staff_name = :accept_staff_name or reject_staff_name=:reject_staff_name)';
            $params[':reject_staff_name'] = $staff_name;
            $params[':accept_staff_name'] = $staff_name;
        }
        if (is_numeric($status)) {
            if ($status == 1) {     //1等待出款2正在出款3出款完成4出款失败5拒绝出款
                $sql .= ' AND accept_time>0 and finish_time is null and cancel_time is null and lock_type is null';
                $total_sql .= ' AND accept_time>0 and finish_time is null and cancel_time is null and lock_type is null';
            }
            if ($status == 2) {
                $sql .= ' AND accept_time>0 and finish_time is null and cancel_time is null and lock_type=0';
                $total_sql .= ' AND accept_time>0 and finish_time is null and cancel_time is null and lock_type=0';
            }
            if ($status == 3) {
                $sql .= ' AND finish_time>0';
                $total_sql .= ' AND finish_time>0';
            }
            if ($status == 4) {
                $sql .= ' AND cancel_time>0';
                $total_sql .= ' AND cancel_time>0';
            }
            if ($status == 5) {
                $sql .= ' AND reject_time>0';
                $total_sql .= ' AND reject_time>0';
            }
        }
        if (!empty($layer_id)) {
            $sql .= ' AND layer_id = :layer_id';
            $total_sql .= ' AND layer_id = :layer_id';
            $params[':layer_id'] = $layer_id;
        }

        if ($withdraw_name) {
            $sql .= ' AND ( finish_staff_name =:finish_staff_name or cancel_staff_name=:cancel_staff_name )';
            $total_sql .= ' AND ( finish_staff_name =:finish_staff_name or cancel_staff_name=:cancel_staff_name )';
            $params[':cancel_staff_name'] = $withdraw_name;
            $params[':finish_staff_name'] = $withdraw_name;
        }
        if ($min_money && empty($max_money)) {
            if (!is_numeric($min_money)) {
                $context->reply(['status' => 204, 'msg' => '最小金额类型错误']);

                return;
            }
            $sql .= ' AND withdraw_money >=:withdraw_money';
            $total_sql .= ' AND withdraw_money >=:withdraw_money';
            $params[':withdraw_money'] = $min_money;
        }
        if ($max_money && empty($min_money)) {
            if (!is_numeric($max_money)) {
                $context->reply(['status' => 205, 'msg' => '最大金额类型错误']);

                return;
            }
            $sql .= ' AND withdraw_money <=:withdraw_money';
            $total_sql .= ' AND withdraw_money <=:withdraw_money';
            $params[':withdraw_money'] = $max_money;
        }
        if ($min_money && $max_money) {
            $sql .= ' AND withdraw_money BETWEEN :min_money AND :max_money ';
            $total_sql .= ' AND withdraw_money BETWEEN :min_money AND :max_money ';
            $params[':min_money'] = $min_money;
            $params[':max_money'] = $max_money;
        }
        if ($start_time && $end_time) {
            $start = strtotime(date('Ymd', strtotime($start_time)).' 00:00:00');
            $end = strtotime(date('Ymd', strtotime($end_time)).' 23:59:59');
            $sql .= ' AND launch_time BETWEEN :start_time AND :end_time ';
            $total_sql .= ' AND launch_time BETWEEN :start_time AND :end_time ';
            $params[':start_time'] = $start;
            $params[':end_time'] = $end;
        }
        if ($start_finish_time && $end_finish_time) {
            $start = strtotime(date('Ymd', strtotime($start_finish_time)).' 00:00:00');
            $end = strtotime(date('Ymd', strtotime($end_finish_time)).' 23:59:59');
            $sql .= ' AND (finish_time BETWEEN :start_time AND :end_time or reject_time BETWEEN :start_time AND :end_time or cancel_time BETWEEN :start_time AND :end_time)  ';
            $total_sql .= ' AND (finish_time BETWEEN :start_time AND :end_time or reject_time BETWEEN :start_time AND :end_time or cancel_time BETWEEN :start_time AND :end_time)  ';
            $params[':start_time'] = $start;
            $params[':end_time'] = $end;
        }
        $sql .= ' LIMIT 100';
        $total_sql .= ' LIMIT 100';
        $total = 0;
        $withdraw_list = [];

        foreach ($config->deal_list as $deal) {
            $mysql = $config->__get('data_'.$deal);
            try {
                $total += $mysql->execute($total_sql, $params);
                $list = iterator_to_array($mysql->query($sql, $params));
                // $context->reply($list);
                if (!empty($list)) {
                    foreach ($list as $key => $val) {
                        $withdraw['withdraw_serial'] = $val['withdraw_serial'];
                        $withdraw['user_key'] = $val['user_key'];
                        $withdraw['user_id'] = $val['user_id'];
                        $withdraw['layer_name'] = !empty($context->getInfo($val['layer_id'])) ? $context->getInfo($val['layer_id']) : '该层级被删除'.$val['layer_id'];
                        $withdraw['launch_time'] = date('Y-m-d H:i:s', $val['launch_time']);
                        $withdraw['launch_money'] = $val['launch_money'];
                        $withdraw['deposit_audit'] = $val['deposit_audit'];
                        $withdraw['handling_fee'] = $val['handling_fee'];
                        $withdraw['withdraw_money'] = $val['withdraw_money'];
                        $withdraw['bank_name'] = $val['bank_name'];
                        $withdraw['bank_branch'] = $val['bank_branch'];
                        $withdraw['account_number'] = $val['account_number'];
                        $withdraw['account_name'] = $val['account_name'];
                        $withdraw['memo'] = '';
                        if ($val['accept_time'] > 0 && empty($val['finish_time']) && empty($val['cancel_time'])) {
                            $withdraw['status'] = '正在出款';
                            $withdraw['review_name'] = empty($val['accept_staff_name']) ? '' : $val['accept_staff_name'];
                            $withdraw['staff_name'] = '';
                            $withdraw['staff_time'] = date('Y-m-d H:i:s', $val['accept_time']);
                        }
                        if ($val['launch_time'] > 0 && empty($val['reject_time']) && empty($val['accept_time']) && empty($val['finish_time']) && empty($val['cancel_time'])) {
                            $withdraw['status'] = '等待出款';
                            $withdraw['review_name'] = '';
                            $withdraw['staff_name'] = '';
                            $withdraw['staff_time'] = '';
                        }
                        if ($val['reject_time'] > 0) {
                            $withdraw['memo'] = $val['reject_reason'];
                            $withdraw['status'] = '拒绝出款';
                            $withdraw['review_name'] = empty($val['reject_staff_name']) ? '' : $val['reject_staff_name'];
                            $withdraw['staff_name'] = '';
                            $withdraw['staff_time'] = date('Y-m-d H:i:s', $val['reject_time']);
                        }
                        if ($val['finish_time'] > 0) {
                            $withdraw['status'] = '出款成功';
                            $withdraw['review_name'] = empty($val['accept_staff_name']) ? '' : $val['accept_staff_name'];
                            $withdraw['staff_name'] = empty($val['finish_staff_name']) ? '' : $val['finish_staff_name'];
                            $withdraw['staff_time'] = date('Y-m-d H:i:s', $val['finish_time']);
                        }
                        if ($val['cancel_time'] > 0) {
                            $withdraw['memo'] = $val['cancel_reason'];
                            $withdraw['status'] = '出款失败';
                            $withdraw['review_name'] = empty($val['accept_staff_name']) ? '' : $val['accept_staff_name'];
                            $withdraw['staff_name'] = empty($val['cancel_staff_name']) ? '' : $val['cancel_staff_name'];
                            $withdraw['staff_time'] = date('Y-m-d H:i:s', $val['cancel_time']);
                        }
                        $withdraw['deal_key'] = $deal;

                        $withdraw_list[] = $withdraw;
                    }
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取失败']);
                throw new \PDOException($e);
            }
        }
        array_multisort(array_column($withdraw_list, 'launch_time'), SORT_DESC, $withdraw_list);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip=:client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_withdraw_select',
            ':detail' => '查看了现金系统的出款查询',
        ];

        $staff_mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total' => $total, 'list' => $withdraw_list, 'layer_list' => $layer_list]);
    }
}
