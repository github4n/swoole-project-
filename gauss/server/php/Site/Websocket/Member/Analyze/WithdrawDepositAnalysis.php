<?php

namespace Site\Websocket\Member\Analyze;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * WithdrawDepositAnalysis class.
 *
 * @description   会员分析-出入款分析
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/Analyze/WithdrawDepositAnalysis
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class WithdrawDepositAnalysis extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_analysis', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        //代理层级列表
        $all_layer_list = $this->LayerManage($context, $config);

        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');
        if ($MasterId != 0) {
            $staffId = $MasterId;
        }
        $params = $context->getData();
        $user_key = isset($params['user_key']) ? $params['user_key'] : '';
        $layer_id = isset($params['layer_id']) ? $params['layer_id'] : '';
        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';

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
                $agent_list = [];
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
                $agent_list = [];
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
                $agent_list = [];
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
        if (empty($agent_list)) {
            $agent_list = [0];
        }

        $sql = 'select user_key,sum(deposit_bank_count + deposit_weixin_count + deposit_alipay_count) as three_deposit_count,sum(deposit_bank_amount + deposit_weixin_amount + deposit_alipay_amount) as three_deposit_amount,sum(bank_deposit_amount-bank_deposit_coupon) as bank_deposit_count,sum(bank_deposit_coupon) as bank_deposit_coupon,sum(bank_deposit_amount) as bank_deposit_amount,sum(simple_deposit_count) as simple_deposit_count,sum(simple_deposit_amount) as simple_deposit_amount,sum(staff_deposit_count) as staff_deposit_count,sum(staff_deposit_amount) as staff_deposit_amount,sum(staff_withdraw_amount) as staff_withdraw_amount,sum(staff_withdraw_count) as staff_withdraw_count,sum(withdraw_amount - staff_withdraw_amount) as withdraw_amount,sum(withdraw_count - staff_withdraw_count) as withdraw_count,sum(coupon_amount) as coupon_amount from daily_user where agent_id in :agent_list and layer_id in :layer_list';
        $param = [':agent_list' => $agent_list, ':layer_list' => $layer_lists];
        if ($user_key) {
            $sql .= ' and user_key=:user_key ';
            $param[':user_key'] = $user_key;
        }
        $sql .= 'group by user_key';
        $mysqlReport = $config->data_report;
        $data = [];
        try {
            foreach ($mysqlReport->query($sql, $param) as $row) {
                $row['three_deposit_amount'] = $this->intercept_num($row['three_deposit_amount']);
                $row['bank_deposit_amount'] = $this->intercept_num($row['bank_deposit_amount']);
                $row['bank_deposit_count'] = $this->intercept_num($row['bank_deposit_count']);
                $row['bank_deposit_coupon'] = $this->intercept_num($row['bank_deposit_coupon']);
                $row['coupon_amount'] = $this->intercept_num($row['coupon_amount']);
                $row['simple_deposit_amount'] = $this->intercept_num($row['simple_deposit_amount']);
                $row['staff_deposit_amount'] = $this->intercept_num($row['staff_deposit_amount']);
                $row['staff_withdraw_amount'] = $this->intercept_num($row['staff_withdraw_amount']);
                $row['withdraw_amount'] = $this->intercept_num($row['withdraw_amount']);
                $data[] = $row;
            }
            $total = count($data);
            $context->reply(['status' => 200, 'msg' => '成功', 'total' => $total, 'data' => $data, 'layer_list' => $all_layer_list]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取列表失败']);
            throw new \PDOException($e);
        }
    }
}
