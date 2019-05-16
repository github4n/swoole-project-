<?php

namespace Site\Websocket\Member\Deposits;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberDeposit class.
 *
 * @description   会员出入款查询-会员入款
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/Deposits/MemberDeposit
 * 搜索参数：user_name:会员名,user_level:会员层级id,rel_name:真实姓名,
 * status；状态 1-入款成功 2-入款失败,3-等待入款 pay_type:支付方式  三方 银行转账 快捷支付    start_time:提交时间开始值,end_time:提交时间结束值
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class MemberDeposit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //会员层级列表
        $all_layer_list = $this->LayerManage($context, $config);
        $param = $context->getData();
        $user_name = isset($param['user_name']) ? $param['user_name'] : '';
        $user_level = isset($param['user_level']) ? $param['user_level'] : '';
        $pay_type = isset($param['pay_type']) ? $param['pay_type'] : ''; //1-银行卡 2-三方 3-快捷
        $rel_name = isset($param['rel_name']) ? $param['rel_name'] : '';
        $status = isset($param['status']) ? $param['status'] : ''; //1-已入款  2-未入款
        $start_time = isset($param['start_time']) ? $param['start_time'] : '';
        $end_time = isset($param['end_time']) ? $param['end_time'] : '';
        $staffId = $context->getInfo('StaffId');
        $staffGrade = $context->getInfo('StaffGrade');
        $MasterId = $context->getInfo('MasterId');
        if ($MasterId != 0) {
            $staffId = $MasterId;
        }
        $mysqlStaff = $config->data_staff;
        $mysqlUser = $config->data_user;
        $layer_list = [0];
        $agent_list = [0];
        switch ($staffGrade) {
            case 0:
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_list = [];

                    foreach ($mysqlStaff->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':layer_list' => $layer_list]);
                } else {
                    $user_sql = 'select user_id  from user_info_intact';
                    $query = $mysqlUser->query($user_sql);
                }

                $user_list = [];
                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
            case 1:
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE major_id=:major_id';
                foreach ($mysqlStaff->query($sql, [':major_id' => $staffId]) as $row) {
                    $agent_list[] = $row['agent_id'];
                }
                $agent_list = empty($agent_list) ? [0] : $agent_list;
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    foreach ($mysqlStaff->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list and layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list, ':layer_list' => $layer_list]);
                } else {
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list]);
                }
                $user_list = [];
                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
            case 2:
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE minor_id=:major_id';
                foreach ($mysqlStaff->query($sql, [':major_id' => $staffId]) as $row) {
                    $agent_list[] = $row['agent_id'];
                }
                $agent_list = empty($agent_list) ? [0] : $agent_list;
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    foreach ($mysqlStaff->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list and layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list, ':layer_list' => $layer_list]);
                } else {
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list]);
                }

                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
            case 3:
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_list = [];
                    foreach ($mysqlStaff->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where agent_id =:agent_id and layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':layer_list' => $layer_list, ':agent_id' => $staffId]);
                } else {
                    $user_sql = 'select user_id from user_info_intact where agent_id = :agent_id';
                    $query = $mysqlUser->query($user_sql, [':agent_id' => $staffId]);
                }
                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
        }
        if (empty($user_list)) {
            $user_list = [0];
        }

        $data = [];
        //银行入款sql
        $bank_sql = 'SELECT user_key,layer_id,launch_money,account_name,launch_time,finish_time,cancel_time FROM deposit_bank_intact WHERE user_id in :user_list ';
        //三方入款sql
        $gateway_sql = 'SELECT user_key,layer_id,account_name,launch_money,launch_time,finish_time,cancel_time FROM deposit_gateway_intact WHERE user_id in :user_list ';
        //快捷入款sql
        $simple_sql = 'SELECT user_key,layer_id,account_name,launch_money,launch_time,finish_time,cancel_time FROM deposit_simple_intact WHERE user_id in :user_list ';
        $param = [':user_list' => $user_list];

        if (!empty($user_name)) {
            $bank_sql .= ' and user_key=:user_key ';
            $gateway_sql .= ' and user_key=:user_key ';
            $simple_sql .= ' and user_key=:user_key ';
            $param[':user_key'] = $user_name;
        }

        if ($rel_name) {
            $bank_sql .= ' and account_name=:account_name ';
            $gateway_sql .= ' and account_name=:account_name ';
            $simple_sql .= ' and account_name=:account_name ';
            $param[':account_name'] = $rel_name;
        }

        if ($user_level) {
            $bank_sql .= ' and layer_id=:layer_id ';
            $gateway_sql .= ' and layer_id=:layer_id ';
            $simple_sql .= ' and layer_id=:layer_id ';
            $param[':layer_id'] = $user_level;
        }

        if ($status) {
            if ($status == 1) {
                $bank_sql .= ' and finish_time > 0 ';
                $gateway_sql .= ' and finish_time > 0 ';
                $simple_sql .= ' and finish_time > 0 ';
            } elseif ($status == 2) {
                $bank_sql .= ' and cancel_time > 0 ';
                $gateway_sql .= ' and cancel_time > 0 ';
                $simple_sql .= ' and cancel_time > 0 ';
            } elseif ($status == 3) {
                $bank_sql .= ' and launch_time > 0 and finish_time is null and cancel_time is null ';
                $gateway_sql .= ' and launch_time > 0 and finish_time is null and cancel_time is null ';
                $simple_sql .= ' and launch_time > 0 and finish_time is null and cancel_time is null ';
            }
        }

        if ($start_time && $end_time) {
            $start = strtotime($start_time.' 00:00:00');
            $end = strtotime($end_time.' 23:59:59');
            $simple_sql .= ' and launch_time BETWEEN :start_time and :end_time ';
            $bank_sql .= ' and launch_time BETWEEN :start_time and :end_time ';
            $gateway_sql .= ' and launch_time BETWEEN :start_time and :end_time ';
            $param[':start_time'] = $start;
            $param[':end_time'] = $end;
        }

        $bank_sql .= ' ORDER BY launch_time DESC limit 200 ';
        $gateway_sql .= ' ORDER BY launch_time DESC limit 200 ';
        $simple_sql .= ' ORDER BY launch_time DESC limit 200 ';

        switch ($pay_type) {
            case 1:

                foreach ($config->deal_list as $deal) {
                    $mysql = $config->__get('data_'.$deal);
                    try {
                        $list = iterator_to_array($mysql->query($bank_sql, $param));

                        if (!empty($list)) {
                            foreach ($list as $key => $value) {
                                $times = '';
                                $layer = $context->getInfo($value['layer_id']);
                                if (!empty($value['finish_time'])) {
                                    $status = '入款成功';
                                    $times = date('Y-m-d H:i:s', $value['finish_time']);
                                }
                                if (!empty($value['cancel_time'])) {
                                    $status = '入款失败';
                                    $times = date('Y-m-d H:i:s', $value['cancel_time']);
                                }
                                if (!empty($value['launch_time']) && empty($value['finish_time']) && empty($value['cancel_time'])) {
                                    $status = '等待入款';
                                    $times = '';
                                }
                                $tag = [
                                    'user_key' => $value['user_key'],
                                    'account_name' => empty(str_replace(' ', '', $value['account_name'])) ? '' : $value['account_name'],
                                    'layer_name' => $layer,
                                    'launch_money' => $this->intercept_num($value['launch_money']),
                                    'launch_time' => $value['launch_time'],
                                    'platform' => '银行转账',
                                    'status' => $status,
                                    'finish_time' => $times,
                                ];
                                $data[] = $tag;
                            }
                        }
                    } catch (\PDOException $e) {
                        $context->reply(['status' => 400, 'msg' => '获取失败']);
                        throw new \PDOException($e);
                    }
                }
                break;
            case 2:
                foreach ($config->deal_list as $deal) {
                    $mysql = $config->__get('data_'.$deal);
                    try {
                        $list = iterator_to_array($mysql->query($gateway_sql, $param));
                        if (!empty($list)) {
                            foreach ($list as $key => $value) {
                                $layer = $context->getInfo($value['layer_id']);
                                if (!empty($value['finish_time'])) {
                                    $status = '入款成功';
                                    $times = date('Y-m-d H:i:s', $value['finish_time']);
                                }
                                if (!empty($value['cancel_time'])) {
                                    $status = '入款失败';
                                    $times = date('Y-m-d H:i:s', $value['cancel_time']);
                                }
                                if (!empty($value['launch_time']) && empty($value['finish_time']) && empty($value['cancel_time'])) {
                                    $status = '等待入款';
                                    $times = '';
                                }
                                $tag = [
                                    'user_key' => $value['user_key'],
                                    'account_name' => empty(str_replace(' ', '', $value['account_name'])) ? '' : $value['account_name'],
                                    'layer_name' => $layer,
                                    'launch_money' => $this->intercept_num($value['launch_money']),
                                    'launch_time' => $value['launch_time'],
                                    'platform' => '三方入款',
                                    'status' => $status,
                                    'finish_time' => $times,
                                ];
                                $data[] = $tag;
                            }
                        }
                    } catch (\PDOException $e) {
                        $context->reply(['status' => 400, 'msg' => '获取失败']);
                        throw new \PDOException($e);
                    }
                }
                break;
            case 3:
                foreach ($config->deal_list as $deal) {
                    $mysql = $config->__get('data_'.$deal);
                    try {
                        $list = iterator_to_array($mysql->query($simple_sql, $param));
                        if (!empty($list)) {
                            foreach ($list as $key => $value) {
                                $layer = $context->getInfo($value['layer_id']);
                                if (!empty($value['finish_time'])) {
                                    $status = '入款成功';
                                    $times = date('Y-m-d H:i:s', $value['finish_time']);
                                }
                                if (!empty($value['cancel_time'])) {
                                    $status = '入款失败';
                                    $times = date('Y-m-d H:i:s', $value['cancel_time']);
                                }
                                if (!empty($value['launch_time']) && empty($value['finish_time']) && empty($value['cancel_time'])) {
                                    $status = '等待入款';
                                    $times = '';
                                }
                                $tag = [
                                    'user_key' => $value['user_key'],
                                    'account_name' => empty(str_replace(' ', '', $value['account_name'])) ? '' : $value['account_name'],
                                    'layer_name' => $layer,
                                    'launch_money' => $this->intercept_num($value['launch_money']),
                                    'launch_time' => $value['launch_time'],
                                    'platform' => '快捷入款',
                                    'status' => $status,
                                    'finish_time' => $times,
                                ];
                                $data[] = $tag;
                            }
                        }
                    } catch (\PDOException $e) {
                        $context->reply(['status' => 400, 'msg' => '获取失败']);
                        throw new \PDOException($e);
                    }
                }
                break;

            default:
                foreach ($config->deal_list as $deal) {
                    $mysql = $config->__get('data_'.$deal);
                    try {
                        $list = iterator_to_array($mysql->query($bank_sql, $param));

                        if (!empty($list)) {
                            foreach ($list as $key => $value) {
                                $layer = $context->getInfo($value['layer_id']);
                                if (!empty($value['finish_time'])) {
                                    $status = '入款成功';
                                    $times = date('Y-m-d H:i:s', $value['finish_time']);
                                }
                                if (!empty($value['cancel_time'])) {
                                    $status = '入款失败';
                                    $times = date('Y-m-d H:i:s', $value['cancel_time']);
                                }
                                if (!empty($value['launch_time']) && empty($value['finish_time']) && empty($value['cancel_time'])) {
                                    $status = '等待入款';
                                    $times = '';
                                }
                                $tag = [
                                    'user_key' => $value['user_key'],
                                    'account_name' => empty(str_replace(' ', '', $value['account_name'])) ? '' : $value['account_name'],
                                    'layer_name' => $layer,
                                    'launch_money' => $this->intercept_num($value['launch_money']),
                                    'launch_time' => $value['launch_time'],
                                    'platform' => '银行转账',
                                    'status' => $status,
                                    'finish_time' => $times,
                                ];
                                $data[] = $tag;
                            }
                        }
                    } catch (\PDOException $e) {
                        $context->reply(['status' => 400, 'msg' => '获取失败']);
                        throw new \PDOException($e);
                    }

                    try {
                        $list = iterator_to_array($mysql->query($gateway_sql, $param));
                        if (!empty($list)) {
                            foreach ($list as $key => $value) {
                                $layer = $context->getInfo($value['layer_id']);
                                if (!empty($value['finish_time'])) {
                                    $status = '入款成功';
                                    $times = date('Y-m-d H:i:s', $value['finish_time']);
                                }
                                if (!empty($value['cancel_time'])) {
                                    $status = '入款失败';
                                    $times = date('Y-m-d H:i:s', $value['cancel_time']);
                                }
                                if (!empty($value['launch_time']) && empty($value['finish_time']) && empty($value['cancel_time'])) {
                                    $status = '等待入款';
                                    $times = '';
                                }
                                $tag = [
                                    'user_key' => $value['user_key'],
                                    'account_name' => empty(str_replace(' ', '', $value['account_name'])) ? '' : $value['account_name'],
                                    'layer_name' => $layer,
                                    'launch_money' => $this->intercept_num($value['launch_money']),
                                    'launch_time' => $value['launch_time'],
                                    'platform' => '三方入款',
                                    'status' => $status,
                                    'finish_time' => $times,
                                ];
                                $data[] = $tag;
                            }
                        }
                    } catch (\PDOException $e) {
                        $context->reply(['status' => 400, 'msg' => '获取失败']);
                        throw new \PDOException($e);
                    }

                    try {
                        $list = iterator_to_array($mysql->query($simple_sql, $param));
                        if (!empty($list)) {
                            foreach ($list as $key => $value) {
                                $layer = $context->getInfo($value['layer_id']);
                                if (!empty($value['finish_time'])) {
                                    $status = '入款成功';
                                    $times = date('Y-m-d H:i:s', $value['finish_time']);
                                }
                                if (!empty($value['cancel_time'])) {
                                    $status = '入款失败';
                                    $times = date('Y-m-d H:i:s', $value['cancel_time']);
                                }
                                if (!empty($value['launch_time']) && empty($value['finish_time']) && empty($value['cancel_time'])) {
                                    $status = '等待入款';
                                    $times = '';
                                }
                                $tag = [
                                    'user_key' => $value['user_key'],
                                    'account_name' => empty(str_replace(' ', '', $value['account_name'])) ? '' : $value['account_name'],
                                    'layer_name' => $layer,
                                    'launch_money' => $this->intercept_num($value['launch_money']),
                                    'launch_time' => $value['launch_time'],
                                    'platform' => '快捷入款',
                                    'status' => $status,
                                    'finish_time' => $times,
                                ];
                                $data[] = $tag;
                            }
                        }
                    } catch (\PDOException $e) {
                        $context->reply(['status' => 400, 'msg' => '获取失败']);
                        throw new \PDOException($e);
                    }
                }

                break;
        }
        array_multisort(array_column($data, 'launch_time'), SORT_DESC, $data);

        $total = count($data);
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total' => $total, 'data' => $data, 'layer_list' => $all_layer_list]);
    }
}