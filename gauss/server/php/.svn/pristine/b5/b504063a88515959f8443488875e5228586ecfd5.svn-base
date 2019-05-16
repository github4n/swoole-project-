<?php

namespace Site\Websocket\Cash\DepositRecord;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * @description   现金系统-入款记录-公司入款记录
 * @Author  Rose
 * @date  2019-04-09
 * @links  Cash/DepositRecord/DepositBankList
 * @modifyAuthor
 * @modifyDate
 * @param status 1等待入款 2入款成功 3入款失败
 *
 * */

class DepositBankList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限权限']);

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
        $data = $context->getData();
        $mysqlStaff = $config->data_staff;
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        $deal_serial = isset($data['deal_serial']) ? $data['deal_serial'] : '';
        $bank = isset($data['bank']) ? $data['bank'] : '';
        $passage_id = isset($data['passage_id']) ? $data['passage_id'] : '';
        $layer_id = isset($data['layer_id']) ? $data['layer_id'] : '';
        $start_launch_time = isset($data['start_launch_time']) ? $data['start_launch_time'] : '';
        $end_launch_time = isset($data['end_launch_time']) ? $data['end_launch_time'] : '';
        $min_money = isset($data['min_money']) ? $data['min_money'] : '';
        $max_money = isset($data['max_money']) ? $data['max_money'] : '';
        $start_finish_time = isset($data['start_finish_time']) ? $data['start_finish_time'] : '';
        $end_finish_time = isset($data['end_finish_time']) ? $data['end_finish_time'] : '';
        $status = isset($data['status']) ? $data['status'] : '';
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
            $sql = 'SELECT user_id,deposit_serial,user_key,layer_id,launch_money,coupon_audit,finish_money,passage_name,passage_id,to_bank_name,'.
                'to_bank_branch,to_account_number,to_account_name, launch_time,launch_device,finish_time,from_name,from_type,'.
                'cancel_time,finish_staff_name,cancel_staff_name FROM deposit_bank_intact WHERE 1=1';
            $total_sql = 'SELECT deposit_serial FROM deposit_bank_intact WHERE 1=1';
            $param = [];
        } else {
            $sql = 'SELECT user_id,deposit_serial,user_key,layer_id,launch_money,coupon_audit,finish_money,passage_name,passage_id,to_bank_name,'.
                'to_bank_branch,to_account_number,to_account_name, launch_time,launch_device,finish_time,from_name,from_type,'.
                'cancel_time,finish_staff_name,cancel_staff_name FROM deposit_bank_intact WHERE layer_id in :layer_list and launch_money <= :launch_money';
            $total_sql = 'SELECT deposit_serial FROM deposit_bank_intact WHERE layer_id in :layer_list and launch_money <=:launch_money';
            $param = [':layer_list' => $layerLists, ':launch_money' => $deposit_limit];
        }
        if (!empty($user_key)) {
            $total_sql .= ' and user_key=:user_key';
            $sql .= ' and user_key=:user_key';
            $param[':user_key'] = $user_key;
        }
        if (!empty($deal_serial)) {
            $total_sql .= ' and deposit_serial=:deposit_serial';
            $sql .= ' and deposit_serial=:deposit_serial';
            $param[':deposit_serial'] = $deal_serial;
        }
        if (!empty($bank)) {
            $total_sql .= ' and to_bank_name=:to_bank_name';
            $sql .= ' and to_bank_name=:to_bank_name';
            $param[':to_bank_name'] = $bank;
        }
        if (!empty($layer_id)) {
            $total_sql .= ' and layer_id=:layer_id';
            $sql .= ' and layer_id=:layer_id';
            $param[':layer_id'] = $layer_id;
        }
        if (!empty($passage_id)) {
            $sql .= ' and passage_id=:passage_id';
            $total_sql .= ' and passage_id=:passage_id';
            $param[':passage_id'] = $passage_id;
        }
        if (!empty($start_launch_time) && !empty($end_launch_time)) {
            $start = date('Ymd', strtotime($start_launch_time)).' 00:00:00';
            $end = date('Ymd', strtotime($end_launch_time)).' 23:59:59';
            $total_sql .= ' and launch_time BETWEEN :start_time and :end_time';
            $sql .= ' and launch_time BETWEEN :start_time and :end_time';
            $param[':start_time'] = strtotime($start);
            $param[':end_time'] = strtotime($end);
        }
        if (!empty($start_finish_time) && !empty($end_finish_time)) {
            $start = date('Ymd', strtotime($start_finish_time)).' 00:00:00';
            $end = date('Ymd', strtotime($end_finish_time)).' 23:59:59';
            $total_sql .= ' and ((finish_time BETWEEN :start and :end) or (cancel_time BETWEEN :start and :end))';
            $sql .= ' and ((finish_time BETWEEN :start and :end) or (cancel_time BETWEEN :start and :end))';
            $param[':start'] = strtotime($start);
            $param[':end'] = strtotime($end);
        }
        if (!empty($min_money)) {
            if (!is_numeric($min_money)) {
                $context->reply(['status' => 204, 'msg' => '到账金额参数错误']);

                return;
            }
            $total_sql .= ' and finish_money >= :finish_money';
            $sql .= ' and finish_money >= :finish_money';
            $param[':finish_money'] = $min_money;
        }
        if (!empty($max_money)) {
            if (!is_numeric($max_money)) {
                $context->reply(['status' => 204, 'msg' => '到账金额参数错误']);

                return;
            }
            $total_sql .= ' and finish_money <= :finish_money';
            $sql .= ' and finish_money <= :finish_money';
            $param[':finish_money'] = $max_money;
        }
        if (!empty($min_money) && !empty($max_money)) {
            $total_sql .= ' AND finish_money BETWEEN :min_money and :max_money';
            $sql .= ' AND finish_money BETWEEN :min_money and :max_money';
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
        $total_sql .= ' ORDER BY deposit_serial DESC LIMIT 100';
        $sql .= ' ORDER BY deposit_serial DESC LIMIT 100';
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
                    $start_time = $val['launch_time'];
                    $sqls = 'SELECT user_id FROM deposit_intact WHERE user_id=:user_id and deal_time >0 and launch_time< :start_time';
                    $is_first = $mysql->execute($sqls, [':user_id' => $val['user_id'], ':start_time' => $start_time]);
                    $deposit = [
                        'user_id' => $val['user_id'],
                        'deposit_serial' => $val['deposit_serial'],
                        'user_key' => $val['user_key'],
                        'layer_name' => $context->getInfo($val['layer_id']),
                        'launch_money' => $val['launch_money'],
                        'finish_money' => empty($val['finish_money']) ? '0.00' : $val['finish_money'],
                        'coupon_money' => empty($val['coupon_audit']) ? '0.00' : $val['coupon_audit'],
                        'launch_time' => date('Y-m-d H:i:s', $val['launch_time']),
                        'passage_name' => $val['passage_name'],
                        'to_bank_name' => $val['to_bank_name'],
                        'to_bank_branch' => $val['to_bank_branch'],
                        'to_account_number' => $val['to_account_number'],
                        'to_account_name' => $val['to_account_name'],
                        'from_name' => $val['from_name'],
                        'is_first' => $is_first > 0 ? '否' : '是',
                    ];
                    if ($val['from_type'] == 1) {
                        $deposit['from_type'] = '网银';
                    }
                    if ($val['from_type'] == 2) {
                        $deposit['from_type'] = '手机银行';
                    }
                    if ($val['from_type'] == 3) {
                        $deposit['from_type'] = 'ATM自动柜员';
                    }
                    if ($val['from_type'] == 4) {
                        $deposit['from_type'] = 'ATM现金';
                    }
                    if ($val['from_type'] == 5) {
                        $deposit['from_type'] = '银行柜台';
                    }
                    if ($val['from_type'] == 11) {
                        $deposit['from_type'] = '支付宝';
                    }
                    if ($val['from_type'] == 21) {
                        $deposit['from_type'] = '微信';
                    }
                    if ($val['from_type'] == 22) {
                        $deposit['from_type'] = 'QQ钱包';
                    }
                    if ($val['from_type'] == 23) {
                        $deposit['from_type'] = '财付通';
                    }
                    if (!empty($val['launch_time']) && empty($val['finish_time']) && empty($val['cancel_time'])) {
                        $deposit['status'] = '等待确认';
                        $deposit['staff_name'] = '';
                        $deposit['finish_time'] = '';
                    }
                    if (!empty($val['finish_time']) && empty($val['cancel_time'])) {
                        $deposit['status'] = '入款成功';
                        $deposit['staff_name'] = $val['finish_staff_name'];
                        $deposit['finish_time'] = date('Y-m-d H:i:s', $val['finish_time']);
                    }
                    if (!empty($val['cancel_time'])) {
                        $deposit['status'] = '入款失败';
                        $deposit['staff_name'] = $val['cancel_staff_name'];
                        $deposit['finish_time'] = date('Y-m-d H:i:s', $val['cancel_time']);
                    }
                    if ($val['launch_device'] == 0) {
                        $deposit['launch_device'] = 'PC';
                    } else {
                        $deposit['launch_device'] = '手机';
                    }
                    $deposit_list[] = $deposit;
                    if ($deposit['status'] == '等待确认') {
                        $deposit_wait[] = $deposit;
                    } else {
                        $deposit_finish[] = $deposit;
                    }
                }
            }
        }
        array_multisort(array_column($deposit_wait, 'launch_time'), SORT_ASC, $deposit_wait);
        array_multisort(array_column($deposit_finish, 'finish_time'), SORT_DESC, $deposit_finish);
        if (!empty($deposit_wait) && !empty($deposit_finish)) {
            $deposit_list = array_merge($deposit_wait, $deposit_finish);
        } else {
            $deposit_list = !empty($deposit_wait) ? $deposit_wait : $deposit_finish;
        }
        //公司通道
        $sql = 'select passage_id,passage_name from deposit_passage_bank_intact';
        $passage_list = iterator_to_array($mysqlStaff->query($sql));
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip=:client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_deal',
            ':detail' => '查看入款记录的公司入款记录',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $layer_list = $this->LayerManage($context, $config);
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'total' => $total,
            'list' => $deposit_list,
            'layer_list' => $layer_list,
            'passage_list' => $passage_list,
        ]);
    }
}
