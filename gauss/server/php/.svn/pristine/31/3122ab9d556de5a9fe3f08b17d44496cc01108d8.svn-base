<?php

namespace Site\Websocket\Member\Analyze;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberSearch class.
 *
 * @description   会员查询
 * @Author  rose
 * @date  2019-04-10
 * @links  Member/Analyze/MemberSearch  user_key layer_id start_time end_time broker_1_key broker_2_key broker_3_key
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class MemberSearch extends CheckLogin
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
        $broker_1_key = isset($params['broker_1_key']) ? $params['broker_1_key'] : '';
        $broker_2_key = isset($params['broker_2_key']) ? $params['broker_2_key'] : '';
        $broker_3_key = isset($params['broker_3_key']) ? $params['broker_3_key'] : '';

        //求账号下及会员
        $agentMysql = $config->data_staff;
        $userMysql = $config->data_user;
        $mysqlReport = $config->data_report;
        $mysqlPublic = $config->data_public;
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


        //根据总代理id查询下属的所有账号id以及所属的数据库
        $order = ' order by user_id desc ';
        $user_sql = 'select user_id,broker_1_key,broker_2_key,broker_3_key,user_key,user_name,register_ip,register_time,login_ip,login_time,money,login_count from user_cumulate where agent_id in :agent_list and layer_id in :layer_list ';
        $param = [':agent_list' => $agent_list, ':layer_list' => $layer_lists];
        if ($user_key) {
            $user_sql .= ' and user_key=:user_key';
            $param[':user_key'] = $user_key;
        }
        if ($layer_id) {
            $user_sql .= ' and layer_id=:layer_id';
            $param[':layer_id'] = $layer_id;
        }
        if (!empty($broker_1_key)) {
            $user_sql .= ' and broker_1_key=:broker_1_key';
            $param[':broker_1_key'] = $broker_1_key;
        }
        if (!empty($broker_2_key)) {
            $user_sql .= ' and broker_2_key=:broker_2_key';
            $param[':broker_2_key'] = $broker_2_key;
        }
        if (!empty($broker_3_key)) {
            $user_sql .= ' and broker_3_key=:broker_3_key';
            $param[':broker_3_key'] = $broker_3_key;
        }
        if ($start_time && $end_time) {
            $start = intval(strtotime(date('Ymd', strtotime($start_time)).' 00:00:00'));
            $end = intval(strtotime(date('Ymd', strtotime($end_time)).' 23:59:59'));
            $user_sql .= ' AND register_time BETWEEN :start_time and :end_time';
            $param[':start_time'] = $start;
            $param[':end_time'] = $end;
        }

        $user_sql .= ' order by user_id desc ';
        $user_list = iterator_to_array($mysqlReport->query($user_sql, $param));
        $row_data = [];

        foreach ($user_list as $key => $val) {
            $sql = 'select sum(deposit_count) as deposit_count,sum(deposit_amount) as deposit_amount,max(deposit_max) as deposit_max,sum(withdraw_count) as withdraw_count,sum(withdraw_amount) as withdraw_amount,max(withdraw_max) as withdraw_max from daily_user where user_id=:user_id limit 1000 ';

            $user_info = [];
            foreach ($mysqlReport->query($sql, ['user_id' => $val['user_id']]) as $row) {
                $user_info = $row;
            }
            $row['user_id'] = $val['user_id'];
            $row['user_key'] = $val['user_key'];
            $row['user_name'] = empty($val['user_name']) ? '' : $val['user_name'];
            $row['broker_1_key'] = empty($val['broker_1_key']) ? '' : $val['broker_1_key'];
            $row['broker_2_key'] = empty($val['broker_2_key']) ? '' : $val['broker_2_key'];
            $row['broker_3_key'] = empty($val['broker_3_key']) ? '' : $val['broker_3_key'];
            $row['money'] = $this->intercept_num($val['money']);
            $row['login_count'] = $val['login_count'];
            $row['deposit_count'] = empty($user_info['deposit_count']) ? 0 : $user_info['deposit_count'];
            $row['deposit_amount'] = empty($user_info['deposit_amount']) ? '0.00' : $this->intercept_num($user_info['deposit_amount']);
            $row['deposit_max'] = empty($user_info['deposit_max']) ? '0.00' : $this->intercept_num($user_info['deposit_max']);
            $row['withdraw_count'] = empty($user_info['withdraw_count']) ? 0 : $user_info['withdraw_count'];
            $row['withdraw_amount'] = empty($user_info['withdraw_amount']) ? '0.00' : $this->intercept_num($user_info['withdraw_amount']);
            $row['register_ip'] = empty($val['register_ip']) ? '' : long2ip($val['register_ip']);
            $row['withdraw_max'] = empty($user_info['withdraw_max']) ? '0.00' : $this->intercept_num($user_info['withdraw_max']);
            $row['register_time'] = empty($val['register_time']) ? '' : date('Y-m-d H:i:s', $val['register_time']);
            $row['login_ip'] = empty($val['login_ip']) ? '' : long2ip($val['login_ip']);
            $row['login_time'] = empty($val['login_time']) ? '' : date('Y-m-d H:i:s', $val['login_time']);
            $row['res'] = $this->intercept_num($user_info['deposit_amount'] - $user_info['withdraw_amount']);
            //登录IP地址
            if (!empty($row['login_ip'])) {
                $login_ip = ip2long($row['login_ip']) >> 8;
                $sql = 'select * from ip_address where ip_net=:ip_net';
                $login_ip_info = [];
                foreach ($mysqlPublic->query($sql, [':ip_net' => $login_ip]) as $rows) {
                    $login_ip_info = $rows;
                }
            }
            //注册ip
            if (!empty($row['register_ip'])) {
                $register_ip = ip2long($row['register_ip']) >> 8;
                $sql = 'select * from ip_address where ip_net=:ip_net';
                $register_ip_info = [];
                foreach ($mysqlPublic->query($sql, [':ip_net' => $register_ip]) as $rows) {
                    $login_ip_info = $rows;
                }
            }
            $row['login_region'] = empty($login_ip_info['region']) ? '' : $login_ip_info['region'];
            $row['login_city'] = empty($login_ip_info['city']) ? '' : $login_ip_info['city'];
            $row['login_country'] = empty($login_ip_info['country']) ? '' : $login_ip_info['country'];

            $row['register_region'] = empty($register_ip_info['region']) ? '' : $register_ip_info['region'];
            $row['register_city'] = empty($register_ip_info['city']) ? '' : $register_ip_info['city'];
            $row['register_country'] = empty($register_ip_info['country']) ? '' : $register_ip_info['country'];
            $row_data[] = $row;
        }
        $total = count($row_data);
        $layer_list = $this->LayerManage($context, $config);
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total' => $total, 'data' => $row_data, 'layer_list' => $layer_list]);
    }
}
