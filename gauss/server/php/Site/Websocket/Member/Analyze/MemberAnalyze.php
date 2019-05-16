<?php

namespace Site\Websocket\Member\Analyze;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberAnalyze class.
 *
 * @description   会员管理-会员分析-有效会员
 * @Author  rose
 * @date  2019-05-08
 * @links  Member/Analyze/MemberAnalyze {"start_time":"","end_time":""}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class MemberAnalyze extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_analysis', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');
        $params = $context->getData();

        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';
        $time = '';

        if ($MasterId != 0) {
            $staffId = $MasterId;
        }

        //求账号下及会员
        $agentMysql = $config->data_staff;
        $userMysql = $config->data_user;
        $agent_list = [0];
        $layer_lists = [0];
        switch ($StaffGrade) {
            case 0:
                if (!empty($MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_lists = [];

                    foreach ($agentMysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                } else {
                    $accout_sql = 'select layer_id from layer_info';
                    foreach ($userMysql->query($accout_sql) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                }
                $sql = 'SELECT agent_id FROM staff_struct_agent';
                foreach ($agentMysql->query($sql) as $row) {
                    $agent_list[] = $row['agent_id'];
                }
                break;
            case 1:
                if (!empty($MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_lists = [];

                    foreach ($agentMysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                } else {
                    $accout_sql = 'select layer_id from layer_info';
                    foreach ($userMysql->query($accout_sql) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                }
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE major_id=:staffId';
                foreach ($agentMysql->query($sql, [':staffId' => $staffId]) as $row) {
                    $agent_list[] = $row['agent_id'];
                }
                break;
            case 2:
                if (!empty($MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_lists = [];

                    foreach ($agentMysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                } else {
                    $accout_sql = 'select layer_id from layer_info';
                    foreach ($userMysql->query($accout_sql) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                }
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE minor_id=:staffId';
                foreach ($agentMysql->query($sql, [':staffId' => $staffId]) as $row) {
                    $agent_list[] = $row['agent_id'];
                }
                break;
            case 3:
                if (!empty($MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_lists = [];

                    foreach ($agentMysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                } else {
                    $accout_sql = 'select layer_id from layer_info';
                    foreach ($userMysql->query($accout_sql) as $row) {
                        $layer_lists[] = $row['layer_id'];
                    }
                }
                $agent_list[] = $staffId;
                break;
        }
        //根据总代理id查询下属的所有账号id以及所属的数据库
        $mysqlReport = $config->data_report;
        $sql = 'select daily,count(bet_count > 0 or null) as bet_count,sum(staff_deposit_amount) as staff_deposit_amount,sum(staff_deposit_count) as staff_deposit_count,sum(bank_deposit_amount) as bank_deposit_amount,sum(bank_deposit_count) as bank_deposit_count,sum(simple_deposit_amount) as simple_deposit_amount,sum(simple_deposit_count) as simple_deposit_count,sum(withdraw_amount - staff_withdraw_amount) as online_withdraw_amount,sum(withdraw_count - staff_withdraw_count) as online_withdraw_count,sum(staff_withdraw_amount) as staff_withdraw_amount ,sum(staff_withdraw_count) as staff_withdraw_count from daily_user where agent_id in :agent_list and layer_id in :layer_list ';
        $param = [':agent_list' => $agent_list, ':layer_list' => $layer_lists];
        if ($start_time && $end_time) {
            $start_time = intval(date('Ymd', strtotime($start_time)));
            $end_time = intval(date('Ymd', strtotime($end_time)));
            $sql .= 'AND daily BETWEEN :start_time and :end_time';
            $param[':start_time'] = $start_time;
            $param[':end_time'] = $end_time;
        }
        $sql .= ' group by daily ORDER by daily DESC';
        $data = [];
        try {
            foreach ($mysqlReport->query($sql, $param) as $row) {
                //今日新注册的人数
                $starts_time = strtotime($row['daily'].' 00:00:00');
                $ends_time = strtotime($row['daily'].' 23:59:59');
                $sqls = 'select user_id from user_info_intact where agent_id in :agent_list and layer_id in :layer_list AND register_time BETWEEN :start_time AND :end_time';
                $total_user = $userMysql->execute($sqls, [':agent_list' => $agent_list, ':layer_list' => $layer_lists, ':start_time' => $starts_time, 'end_time' => $ends_time]);
                $row['is_today_register'] = $total_user;
                $row['res'] = $this->intercept_num($row['staff_deposit_amount'] + $row['bank_deposit_amount'] - $row['staff_withdraw_amount'] + $row['simple_deposit_amount'] - $row['online_withdraw_amount'] - $row['staff_withdraw_amount']);
                $row['staff_deposit_amount'] = $this->intercept_num($row['staff_deposit_amount']);
                $row['bank_deposit_amount'] = $this->intercept_num($row['bank_deposit_amount']);
                $row['simple_deposit_amount'] = $this->intercept_num($row['simple_deposit_amount']);
                $row['online_withdraw_amount'] = $this->intercept_num($row['online_withdraw_amount']);
                $row['staff_withdraw_amount'] = $this->intercept_num($row['staff_withdraw_amount']);
                $data[] = $row;
            }
            $total = count($data);
            $context->reply(['status' => 200, 'msg' => '成功', 'total' => $total, 'data' => $data]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => ' 获取列表失败']);
            throw new \PDOException($e);
        }
    }
}
