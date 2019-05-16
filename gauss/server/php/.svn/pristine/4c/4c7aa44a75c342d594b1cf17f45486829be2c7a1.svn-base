<?php

namespace Site\Websocket\Cash\DepositRecord;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-入款记录-第三方入款
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/DepositRecord/DepositGateList
 * @modifyAuthor
 * @modifyDate
 * @param status 1等待入款 2入款成功 3入款失败
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
        if (!in_array('money_deposit_deal', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $masterId = $context->getInfo('MasterId');
        $staffId = $context->getInfo('StaffId');
        $mysqlStaff = $config->data_staff;
        $mysqlUser = $config->data_user;
        $cache = $config->cache_site;

        $data = $context->getData();
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        $deal_serial = isset($data['deal_serial']) ? $data['deal_serial'] : '';
        $passage_name = isset($data['passage_id']) ? $data['passage_id'] : '';
        $layer_id = isset($data['layer_id']) ? $data['layer_id'] : '';
        $start_launch_time = isset($data['start_launch_time']) ? $data['start_launch_time'] : '';
        $end_launch_time = isset($data['end_launch_time']) ? $data['end_launch_time'] : '';
        $min_money = isset($data['min_money']) ? $data['min_money'] : '';
        $max_money = isset($data['max_money']) ? $data['max_money'] : '';
        $start_finish_time = isset($data['start_finish_time']) ? $data['start_finish_time'] : '';
        $end_finish_time = isset($data['end_finish_time']) ? $data['end_finish_time'] : '';
        $status = isset($data['status']) ? $data['status'] : '';
        $way_key = isset($data['way_key']) ? $data['way_key'] : '';
        $gate_name = isset($data['gate_name']) ? $data['gate_name'] : '';
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
            $sql = 'SELECT user_id,deposit_serial,user_key,layer_id,launch_money,launch_time,to_account_number,gate_name,way_name,finish_money,finish_time,launch_device,passage_name,finish_staff_name,cancel_time,cancel_staff_id,cancel_staff_name,cancel_reason FROM deposit_gateway_intact WHERE 1=1 ';
            $total_sql = 'SELECT deposit_serial FROM deposit_gateway_intact WHERE 1=1 ';
            $param = [];
        } else {
            $sql = 'SELECT user_id,deposit_serial,user_key,layer_id,launch_money,launch_time,to_account_number,gate_name,way_name,finish_money,finish_time,launch_device,passage_name,finish_staff_name,cancel_time,cancel_staff_id,cancel_staff_name,cancel_reason FROM deposit_gateway_intact WHERE layer_id in :layer_list and launch_money <=:launch_money';
            $total_sql = 'SELECT deposit_serial FROM deposit_gateway_intact WHERE layer_id in :layer_list and launch_money <= :launch_money';
            $param = [':layer_list' => $layerLists, ':launch_money' => $deposit_limit];
        }

        if (!empty($gate_name)) {
            $sql .= ' AND gate_key=:gate_key';
            $total_sql .= ' AND gate_key=:gate_key';
            $param[':gate_key'] = $gate_name;
        }
        if (!empty($user_key)) {
            $sql .= ' AND user_key=:user_key';
            $total_sql .= ' AND user_key=:user_key';
            $param[':user_key'] = $user_key;
        }
        if (!empty($deal_serial)) {
            $sql .= ' AND deposit_serial=:deposit_serial';
            $total_sql .= ' AND deposit_serial=:deposit_serial';
            $param[':deposit_serial'] = $deal_serial;
        }
        if (!empty($layer_id)) {
            $sql .= ' AND layer_id=:layer_id';
            $total_sql .= ' AND layer_id=:layer_id';
            $param[':layer_id'] = $layer_id;
        }
        if (!empty($passage_name)) {
            $sql .= ' AND passage_id=:passage_id';
            $total_sql .= ' AND passage_id=:passage_id';
            $param[':passage_id'] = $passage_name;
        }
        if (!empty($start_launch_time) && !empty($end_launch_time)) {
            $start = date('Ymd', strtotime($start_launch_time)).' 00:00:00';
            $end = date('Ymd', strtotime($end_launch_time)).' 23:59:59';
            $sql .= ' AND launch_time BETWEEN :start_time and :end_time ';
            $total_sql .= ' AND launch_time BETWEEN :start_time and :end_time';
            $param[':start_time'] = strtotime($start);
            $param[':end_time'] = strtotime($end);
        }
        if (!empty($start_finish_time) && !empty($end_finish_time)) {
            $start = date('Ymd', strtotime($start_finish_time)).' 00:00:00';
            $end = date('Ymd', strtotime($end_finish_time)).' 23:59:59';
            $sql .= ' AND finish_time BETWEEN :start and :end ';
            $total_sql .= ' AND finish_time BETWEEN :start and :end';
            $param[':start'] = strtotime($start);
            $param[':end'] = strtotime($end);
        }
        if (!empty($min_money) && empty($max_money)) {
            if (!is_numeric($min_money)) {
                $context->reply(['status' => 204, 'msg' => '到账金额参数错误']);

                return;
            }
            $sql .= ' AND launch_money >= :launch_money ';
            $total_sql .= ' AND launch_money >= :launch_money ';
            $param[':launch_money'] = $min_money;
        }
        if (!empty($max_money) && empty($min_money)) {
            if (!is_numeric($max_money)) {
                $context->reply(['status' => 204, 'msg' => '到账金额参数错误']);

                return;
            }
            $sql .= ' AND launch_money >= :launch_money ';
            $total_sql .= ' AND launch_money >= :launch_money ';
            $param[':launch_money'] = $max_money;
        }
        if (!empty($min_money) && !empty($max_money)) {
            $total_sql .= ' AND launch_money BETWEEN :min_money and :max_money';
            $sql .= ' AND launch_money BETWEEN :min_money and :max_money';
            $param[':min_money'] = $min_money;
            $param[':max_money'] = $max_money;
        }
        if (!empty($status)) {
            if ($status == 1) {
                $total_sql .= ' AND launch_time > 0 AND finish_time is null AND cancel_time is null';
                $sql .= ' AND launch_time > 0 AND finish_time is null AND cancel_time is null';
            } elseif ($status == 2) {
                $total_sql .= ' AND finish_time > 0';
                $sql .= ' AND finish_time > 0';
            } elseif ($status == 3) {
                $total_sql .= ' AND cancel_time > 0';
                $sql .= ' AND cancel_time > 0';
            }
        }
        if (!empty($way_key)) {
            $sql .= ' AND way_key = :way_key';
            $total_sql .= ' AND way_key = :way_key';
            $param[':way_key'] = $way_key;
        }
        $sql .= ' ORDER BY deposit_serial DESC limit 100';
        $total_sql .= ' ORDER BY deposit_serial DESC limit 100';
        $total = 0;
        $deposit_list = [];
        $deposit_wait = [];
        $deposit_finish = [];
        foreach ($config->deal_list as $deal) {
            $mysql = $config->__get('data_'.$deal);
            $total += $mysql->execute($total_sql, $param);

            $list = iterator_to_array($mysql->query($sql, $param));
            if (!empty($list)) {
                foreach ($list as $key => $val) {
                    $sqls = 'SELECT user_id FROM deposit_intact WHERE user_id=:user_id and deal_time >0 and launch_time< :start_time';
                    $is_first = $mysql->execute($sqls, [':user_id' => $val['user_id'], ':start_time' => $val['launch_time']]);
                    $deposit['user_id'] = $val['user_id'];
                    $deposit['deposit_serial'] = $val['deposit_serial'];
                    $deposit['user_key'] = $val['user_key'];
                    $deposit['layer_id'] = $val['layer_id'];
                    $deposit['layer_name'] = $context->getInfo($val['layer_id']);
                    $deposit['to_account_number'] = $val['to_account_number'];
                    $deposit['passage_name'] = $val['passage_name'];
                    $deposit['gate_name'] = $val['gate_name'];
                    $deposit['way_name'] = $val['way_name'];
                    $deposit['launch_money'] = $val['launch_money'];
                    $deposit['launch_time'] = !empty($val['launch_time']) ? date('Y-m-d H:i:s', $val['launch_time']) : '';
                    $deposit['finish_money'] = empty($val['finish_money']) ? '0.00' : $val['finish_money'];
                    $deposit['is_first'] = $is_first > 0 ? '否' : '是';
                    if (!empty($val['launch_time']) && empty($val['finish_time']) && empty($val['cancel_time']) && empty($val['deal_time'])) {
                        $deposit['status'] = '等待';
                        $deposit['finish_time'] = '';
                        $deposit['staff_name'] = '';
                    }
                    if (!empty($val['finish_time']) && empty($val['deal_time'])) {
                        $deposit['status'] = '已入款';
                        $deposit['finish_time'] = date('Y-m-d H:i:s', $val['finish_time']);
                        $deposit['staff_name'] = empty($val['finish_staff_name']) ? '' : $val['finish_staff_name'];
                    }
                    if (!empty($val['cancel_time'])) {
                        $deposit['status'] = '入款失败';
                        $deposit['finish_time'] = date('Y-m-d H:i:s', $val['cancel_time']);
                        $deposit['staff_name'] = empty($val['cancel_staff_name']) ? '' : $val['cancel_staff_name'];
                    }
                    if ($val['launch_device'] == 0) {
                        $deposit['launch_device'] = 'PC';
                    } else {
                        $deposit['launch_device'] = '手机';
                    }

                    $deposit_list[] = $deposit;
                    if ($deposit['status'] == '等待') {
                        $deposit_wait[] = $deposit;
                    } else {
                        $deposit_finish[] = $deposit;
                    }
                }
            }
        }
        if (!empty($deposit_wait)) {
            array_multisort(array_column($deposit_wait, 'launch_time'), SORT_ASC, $deposit_wait);
        }
        if (!empty($deposit_finish)) {
            array_multisort(array_column($deposit_finish, 'finish_time'), SORT_DESC, $deposit_finish);
        }
        if (!empty($deposit_wait) && !empty($deposit_finish)) {
            $deposit_list = array_merge($deposit_wait, $deposit_finish);
        } else {
            $deposit_list = !empty($deposit_wait) ? $deposit_wait : $deposit_finish;
        }
        //三方通道
        $sql = 'select passage_id,passage_name from deposit_passage_gate_intact';
        $passage_list = iterator_to_array($mysqlStaff->query($sql));
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_deal',
            ':detail' => '查看入款记录的三方的入款记录',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $layer_list = $this->LayerManage($context, $config);
        $gate_list = json_decode($cache->hget('PayWayList', 'payGateList'));
        $way_list = json_decode($cache->hget('PayWayList', 'payWayList'));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total' => $total, 'list' => $deposit_list, 'layer_list' => $layer_list, 'gate_list' => $gate_list, 'way_list' => $way_list, 'passage_list' => $passage_list]);
    }
}
