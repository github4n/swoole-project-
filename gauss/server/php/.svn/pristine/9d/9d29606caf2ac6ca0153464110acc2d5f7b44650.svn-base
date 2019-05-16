<?php

namespace Site\Websocket\Member\Analyze;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * Undocumented class.
 *
 * @description   下注分析
 * @Author  blake
 * @date  2019-05-08
 * @links Site/Websocket/Member/Analyze/BetAnalysis
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class BetAnalysis extends CheckLogin
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
        $agentMysql = $config->data_staff;
        $userMysql = $config->data_user;
        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');
        $params = $context->getData();
        //子账号的权限信息  //会员层级列表
        $layer_list = $this->LayerManage($context, $config);
        $user_key = isset($params['user_key']) ? $params['user_key'] : '';
        $layer_id = isset($params['layer_id']) ? $params['layer_id'] : '';
        $broker_1_key = isset($params['broker_1_key']) ? $params['broker_1_key'] : '';
        $broker_2_key = isset($params['broker_2_key']) ? $params['broker_2_key'] : '';
        $broker_3_key = isset($params['broker_3_key']) ? $params['broker_3_key'] : '';

        if ($MasterId != 0) {
            $staffId = $MasterId;
        }
        $agent_list = [0];
        $layer_lists = [0];
        //求账号下及会员

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
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE major_id=:major_id';
                foreach ($agentMysql->query($sql, [':major_id' => $staffId]) as $row) {
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
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE minor_id=:minor_id';
                foreach ($agentMysql->query($sql, [':minor_id' => $staffId]) as $row) {
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
        if (empty($layer_lists)) {
            $layer_lists = [0];
        }

        $sql = 'select user_key,broker_1_key,broker_2_key,broker_3_key,sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_amount) as wager_amount,sum(bonus_amount+rebate_amount) as bonus_amount from daily_user where agent_id in :agent_list and layer_id in :layer_list and bet_amount>0';
        $param = [':agent_list' => $agent_list, ':layer_list' => $layer_lists];
        if ($user_key) {
            $sql .= ' and user_key=:user_key';
            $param[':user_key'] = $user_key;
        }
        if ($layer_id) {
            $sql .= ' and layer_id=:layer_id';
            $param[':layer_id'] = $layer_id;
        }
        if ($broker_1_key) {
            $sql .= ' and broker_1_key=:broker_1_key';
            $param[':broker_1_key'] = $broker_1_key;
        }
        if ($broker_2_key) {
            $sql .= ' and broker_2_key=:broker_2_key';
            $param[':broker_2_key'] = $broker_2_key;
        }
        if ($broker_3_key) {
            $sql .= ' and broker_3_key=:broker_3_key';
            $param[':broker_3_key'] = $broker_3_key;
        }
        $sql .= ' group by user_key,broker_1_key,broker_2_key,broker_3_key';
        $mysqlReport = $config->data_report;
        $data = [];

        try {
            foreach ($mysqlReport->query($sql, $param) as $row) {
                $row['winningRate'] = empty($row['wager_amount']) ? 0 : $this->intercept_num($row['bonus_amount'] / $row['wager_amount']);
                $row['res'] = $this->intercept_num($row['bonus_amount'] - $row['wager_amount']);
                $row['bet_amount'] = $this->intercept_num($row['bet_amount']);
                $row['wager_amount'] = $this->intercept_num($row['wager_amount']);
                $row['bonus_amount'] = $this->intercept_num($row['bonus_amount']);
                $data[] = $row;
            }
            $total = count($data);
            $context->reply(['status' => 200, 'msg' => '成功', 'total' => $total, 'data' => $data, 'layer_list' => $layer_list]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取列表失败']);
            throw new \PDOException($e);
        }
    }
}
