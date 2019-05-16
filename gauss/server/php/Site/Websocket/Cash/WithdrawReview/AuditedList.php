<?php

namespace Site\Websocket\Cash\WithdrawReview;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-出款审核-已审核
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/WithdrawReview/AuditedList
 * @param status 1等待入款 2入款成功 3入款失败
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class AuditedList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $cache = $config->cache_site;
        $layer_list = $this->LayerManage($context, $config);
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_withdraw_accept', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $masterId = $context->getInfo('MasterId');
        $staffId = $context->getInfo('StaffId');
        $mysqlStaff = $config->data_staff;
        $data = $context->getData();
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        $layer_id = isset($data['layer_id']) ? $data['layer_id'] : '';
        $deal_serial = isset($data['withdraw_serial']) ? $data['withdraw_serial'] : '';
        $start_launch_time = isset($data['start_launch_time']) ? $data['start_launch_time'] : '';
        $end_launch_time = isset($data['end_launch_time']) ? $data['end_launch_time'] : '';
        $min_money = isset($data['min_money']) ? $data['min_money'] : '';
        $max_money = isset($data['max_money']) ? $data['max_money'] : '';
        $status = isset($data['status']) ? $data['status'] : '';
        $staff_name = isset($data['staff_key']) ? $data['staff_key'] : '';
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
            $sql = 'SELECT user_id,withdraw_serial,user_key,layer_id,launch_money,bank_branch,account_number,account_name,'.
                'handling_fee,deposit_audit,withdraw_money,bank_name,accept_staff_name,reject_staff_name,reject_reason,must_inspect, '.
                'FROM_UNIXTIME(launch_time) as launch_time,accept_staff_id,launch_device,finish_time,cancel_time,reject_time,'.
                'accept_time FROM withdraw_intact WHERE lock_type is null and must_inspect=1 AND (accept_time >0 OR reject_time>0) ';
            $total_sql = 'SELECT withdraw_serial FROM withdraw_intact WHERE lock_type is null and must_inspect=1 AND (accept_time >0 OR reject_time>0) ';
            $params = [];
        } else {
            $sql = 'SELECT user_id,withdraw_serial,user_key,layer_id,launch_money,bank_branch,account_number,account_name,'.
                'handling_fee,deposit_audit,withdraw_money,bank_name,accept_staff_name,reject_staff_name,reject_reason,must_inspect, '.
                'FROM_UNIXTIME(launch_time) as launch_time,accept_staff_id,launch_device,finish_time,cancel_time,reject_time,'.
                'accept_time FROM withdraw_intact WHERE lock_type is null and must_inspect=1 AND (accept_time >0 OR reject_time>0) AND layer_id in :layer_list and launch_money <= :launch_money';
            $total_sql = 'SELECT withdraw_serial FROM withdraw_intact WHERE lock_type is null and must_inspect=1 AND (accept_time >0 OR reject_time>0) AND layer_id in :layer_list and launch_money <= :launch_money';
            $params = [':layer_list' => $layerLists, ':launch_money' => $withdraw_limit];
        }

        if (!empty($user_key)) {
            $sql .= ' AND user_key = :user_key';
            $total_sql .= ' AND user_key = :user_key';
            $params[':user_key'] = $user_key;
        }
        if (!empty($deal_serial)) {
            $sql .= ' AND withdraw_serial=:withdraw_serial';
            $total_sql .= ' AND withdraw_serial=:withdraw_serial';
            $params[':withdraw_serial'] = $deal_serial;
        }
        if (!empty($start_launch_time) && !empty($end_launch_time)) {
            $start = date('Ymd', strtotime($start_launch_time)).' 00:00:00';
            $end = date('Ymd', strtotime($end_launch_time)).' 23:59:59';
            $sql .= ' AND launch_time BETWEEN :start_time AND :end_time';
            $total_sql .= ' AND launch_time BETWEEN :start_time AND :end_time';
            $params[':start_time'] = strtotime($start);
            $params[':end_time'] = strtotime($end);
        }

        if (!empty($min_money) && empty($max_money)) {
            if (!is_numeric($min_money)) {
                $context->reply(['status' => 204, 'msg' => '到账金额参数错误']);

                return;
            }
            $sql .= ' AND launch_money>=:launch_money';
            $total_sql .= ' AND launch_money>=:launch_money';
            $params[':launch_money'] = $min_money;
        }
        if (!empty($max_money) && empty($min_money)) {
            if (!is_numeric($max_money)) {
                $context->reply(['status' => 204, 'msg' => '到账金额参数错误']);

                return;
            }
            $sql .= ' AND launch_money <=:launch_money';
            $total_sql .= ' AND launch_money <=:launch_money';
            $params[':launch_money'] = $max_money;
        }
        if (!empty($min_money) && !empty($max_money)) {
            $sql .= ' AND launch_money  BETWEEN :min_money AND :max_money';
            $total_sql .= ' AND launch_money  BETWEEN :min_money AND :max_money';
            $params[':min_money'] = $min_money;
            $params[':max_money'] = $max_money;
        }
        if (!empty($status)) {
            if ($status == 1) {
                $sql .= ' AND accept_time > 0';
                $total_sql .= ' AND accept_time > 0';
            } elseif ($status == 2) {
                $sql .= ' AND reject_time > 0';
                $total_sql .= ' AND reject_time > 0';
            }
        }
        if (!empty($staff_name)) {
            $sql .= ' AND (reject_staff_name = :reject_staff_name or accept_staff_name=:accept_staff_name)';
            $total_sql .= ' AND (reject_staff_name = :reject_staff_name or accept_staff_name=:accept_staff_name)';
            $params[':reject_staff_name'] = $staff_name;
            $params[':accept_staff_name'] = $staff_name;
        }

        if (!empty($layer_id)) {
            $sql .= ' AND layer_id = :layer_id';
            $total_sql .= ' AND layer_id = :layer_id';
            $params[':layer_id'] = $layer_id;
        }
        if ($start_finish_time && $end_finish_time) {
            $start = strtotime(date('Ymd', strtotime($start_finish_time)).' 00:00:00');
            $end = strtotime(date('Ymd', strtotime($end_finish_time)).' 23:59:59');
            $sql .= ' AND (accept_time BETWEEN :start_time AND :end_time  OR reject_time BETWEEN :start_time AND :end_time )  ';
            $total_sql .= ' AND (accept_time BETWEEN :start_time AND :end_time OR reject_time BETWEEN :start_time AND :end_time )  ';
            $params[':start_time'] = $start;
            $params[':end_time'] = $end;
        }

        $sql .= ' LIMIT 100';
        $total_sql .= ' LIMIT 100';

        $total = 0;
        $withdraw_list = [];
        foreach ($config->deal_list as $deal) {
            $mysql = $config->__get('data_'.$deal);
            $total += $mysql->execute($total_sql, $params);
            $list = iterator_to_array($mysql->query($sql, $params));
            if (!empty($list)) {
                foreach ($list as $key => $val) {
                    $withdraw['user_id'] = $val['user_id'];
                    $withdraw['withdraw_serial'] = $val['withdraw_serial'];
                    $withdraw['user_key'] = $val['user_key'];
                    $withdraw['layer_id'] = $val['layer_id'];
                    $withdraw['layer_name'] = $context->getInfo($val['layer_id']) ?: '该层级已被删除'.$val['layer_id'];
                    $withdraw['handling_fee'] = $val['handling_fee'];
                    $withdraw['deposit_audit'] = $val['deposit_audit'];
                    $withdraw['withdraw_money'] = $val['withdraw_money'];
                    $withdraw['bank_branch'] = $val['bank_branch'];
                    $withdraw['bank_name'] = $val['bank_name'];
                    $withdraw['account_number'] = $val['account_number'];
                    $withdraw['account_name'] = $val['account_name'];
                    $withdraw['launch_money'] = $val['launch_money'];
                    $withdraw['launch_time'] = $val['launch_time'];
                    $withdraw['is_review'] = $val['must_inspect'];
                    $withdraw['memo'] = $val['reject_reason'];
                    if (empty($val['finish_time']) && empty($val['cancel_time']) && empty($val['reject_time']) && empty($val['accept_time'])) {
                        $withdraw['status'] = '待审核';
                        $withdraw['staff_key'] = '';
                        $withdraw['staff_time'] = '';
                    }
                    if (empty($val['finish_time']) && empty($val['cancel_time']) && !empty($val['accept_time'])) {
                        $withdraw['status'] = '待出款';
                        $withdraw['staff_key'] = '';
                        $withdraw['staff_time'] = date('Y-m-d H:i:s', $val['accept_time']);
                    }
                    if (!empty($val['launch_time']) && !empty($val['reject_time'])) {
                        $withdraw['status'] = '拒绝出款';
                        $withdraw['staff_key'] = $val['reject_staff_name'];
                        $withdraw['staff_time'] = date('Y-m-d H:i:s', $val['reject_time']);
                    }
                    if (!empty($val['launch_time']) && !empty($val['accept_time'])) {
                        $withdraw['status'] = '同意出款';
                        $withdraw['staff_key'] = $val['accept_staff_name'];
                        $withdraw['staff_time'] = date('Y-m-d H:i:s', $val['accept_time']);
                    }
                    if ($val['launch_device'] == 0) {
                        $withdraw['launch_device'] = 'PC';
                    } else {
                        $withdraw['launch_device'] = '手机';
                    }
                    $sqls = 'SELECT * FROM withdraw_lock WHERE withdraw_serial=:withdraw_serial';
                    $param = [':withdraw_serial' => $val['withdraw_serial']];
                    $result = $mysql->execute($sqls, $param);
                    $withdraw['lock'] = $result > 0 ? 1 : 0;
                    $withdraw_list[] = $withdraw;
                }
            }
        }
        array_multisort(array_column($withdraw_list, 'launch_time'), SORT_DESC, $withdraw_list);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_withdraw_accept',
            ':detail' => '查看未出款记录',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total' => $total, 'list' => $withdraw_list, 'layer_list' => $layer_list]);
    }
}
