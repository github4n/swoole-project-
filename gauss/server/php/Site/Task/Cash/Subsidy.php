<?php

namespace Site\Task\Cash;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 *
 * @description  手动派发返水
 * @Author  Rose
 * @date  2019-05-08
 * @link Websocket: Cash/Subsidy
 * @modifyAuthor
 * @modifyDate 2019-05-09
 *
 * */

class Subsidy implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['layer_id' => $layer_id,'start_time' => $start_time,'staff_id' => $staff_id,'staff_name' => $staff_name,'daily' => $daily,'auto_deliver' => $auto_deliver] = $context->getData();
        $mysqlReport = $config->data_report;
        $mysqlUser = $config->data_user;
        $check_sql = "select operate_key from layer_permit where layer_id = :layer_id and operate_key = 'subsidy_stop'";
        $operate_key = '';
        foreach ($mysqlUser->query($check_sql, [':layer_id' => $layer_id]) as $value) {
            $operate_key = $value['operate_key'];
        }
        if ($operate_key) {
            return;
        }
        //检测是否派发
        $sql = 'select deliver_finish_time from daily_layer_subsidy where daily=:daily And layer_id = :layer_id and deliver_finish_time>0';
        foreach ($mysqlReport->query($sql, [':daily' => $daily, ':layer_id' => $layer_id]) as $row) {
            $deliver_finish_time = $row['deliver_finish_time'];
        }
        if (!empty($deliver_finish_time)) {
            return;
        }
        $report_sql = 'select user_id,subsidy from daily_user_subsidy where daily = :daily and layer_id = :layer_id And deliver_time = 0';
        $user_sql = 'select user_key,account_name,deal_key,layer_id from user_info_intact where user_id = :user_id';
        //昨日报表的反水和相关用户信息
        $userInfo = [];
        $report = iterator_to_array($mysqlReport->query($report_sql, [':daily' => $daily, ':layer_id' => $layer_id]));
        foreach ($report as $item) {
            if (!empty($item) && $item['subsidy'] > 0) {
                $param = [
                    'user_id' => $item['user_id'],
                ];
                $user_key = '';
                // $layer_id = '';
                $account_name = '';
                $deal_key = '';
                //查找user_info
                foreach ($mysqlUser->query($user_sql, $param) as $val) {
                    $user_key = $val['user_key'];
                    $account_name = $val['account_name'];
                    // $layer_id = $val['layer_id'];
                    $deal_key = $val['deal_key'];
                }
                // 如果用户不存在则不派发返水
                if ($deal_key) {
                    $userInfo[] = [
                        'user_id' => $item['user_id'],
                        'user_key' => $user_key,
                        'account_name' => $account_name,
                        'layer_id' => $layer_id,
                        'deal_key' => $deal_key,
                        'daily' => $daily,
                        'subsidy' => $item['subsidy'],
                    ];
                }
            }
        }

        if (!empty($userInfo)) {
            foreach ($userInfo as $row) {
                $data = [];
                $mysqlSubsidy = $config->__get('data_'.$row['deal_key']);
                $data[] = [
                    'user_id' => $row['user_id'],
                    'user_key' => $row['user_key'],
                    'account_name' => !empty($row['account_name']) ? $row['account_name'] : '',
                    'layer_id' => $row['layer_id'],
                    'daily' => $daily,
                    'subsidy' => $row['subsidy'],
                    'deliver_time' => time(),
                ];

                $mysqlSubsidy->subsidy_deliver->load($data, [], '');
                //更新report的派发时间
                $update_sql = 'update daily_user_subsidy set deliver_time = :deliver_time where daily = :daily and user_id = :user_id';
                $param = [
                    'deliver_time' => $data[0]['deliver_time'],
                    'user_id' => $row['user_id'],
                    'daily' => $daily,
                ];
                $mysqlReport->execute($update_sql, $param);
            }

            $end_time = time();
            $layer_sql = 'update daily_layer_subsidy set auto_deliver = :auto_deliver,deliver_staff_id = :deliver_staff_id,deliver_staff_name = :deliver_staff_name,deliver_launch_time = :deliver_launch_time,deliver_finish_time = :deliver_finish_time where daily = :daily and layer_id = :layer_id';
            $params = [
                'auto_deliver' => $auto_deliver,
                'deliver_staff_id' => $staff_id,
                'deliver_staff_name' => $staff_name,
                'deliver_launch_time' => $start_time,
                'deliver_finish_time' => $end_time,
                'daily' => $daily,
                'layer_id' => $layer_id,
            ];
            $mysqlReport->execute($layer_sql, $params);
        } else {
            // 若层级中的所有用户都已经被返水，则更新层级返水派发时间
            $end_time = time();
            $layer_sql = 'update daily_layer_subsidy set auto_deliver = :auto_deliver,deliver_staff_id = :deliver_staff_id,deliver_staff_name = :deliver_staff_name,deliver_launch_time = :deliver_launch_time,deliver_finish_time = :deliver_finish_time where daily = :daily and layer_id = :layer_id';
            $params = [
                'auto_deliver' => $auto_deliver,
                'deliver_staff_id' => $staff_id,
                'deliver_staff_name' => $staff_name,
                'deliver_launch_time' => $start_time,
                'deliver_finish_time' => $end_time,
                'daily' => $daily,
                'layer_id' => $layer_id,
            ];
            $mysqlReport->execute($layer_sql, $params);
        }
        //推送用户余额
        $sql = 'select distinct user_id from daily_user_subsidy where daily=:daily and layer_id=:layer_id';
        $user_list = [];
        foreach ($mysqlReport->query($sql, [':daily' => $daily, ':layer_id' => $layer_id]) as $row) {
            $user_list[] = $row['user_id'];
        }
        if (!empty($user_list)) {
            $taskAdapter = $context->getAdapter();
            $taskAdapter->plan('User/Balance', ['user_list' => $user_list], time(), 6);
        }
    }
}
