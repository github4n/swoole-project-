<?php

namespace Site\Task\Cash;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 *
 * @description  佣金自动派发
 * @Author  Rose
 * @date  2019-05-07
 * @link Websocket: Cash/MemberBrokerage {'layer_id': ""}
 * @modifyAuthor
 * @modifyDate
 *
 * */

class MemberBrokerage implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        try {
            ['layer_id' => $layer_id] = $context->getData();
            $daily = date('Ymd', strtotime('today') - 86400);
            $adapter = $context->getAdapter();
            $mysqlReport = $config->data_report;
            $mysqlUser = $config->data_user;
            $check_sql = "select operate_key from layer_permit where layer_id = :layer_id and operate_key = 'brokerage_stop'";
            $operate_key = '';
            $user_list = [];
            foreach ($mysqlUser->query($check_sql, [':layer_id' => $layer_id]) as $value) {
                $operate_key = $value['operate_key'];
            }
            if ($operate_key) {
                return;
            }

            //检测佣金是否派发
            $sql = 'Select `deliver_finish_time` From `daily_layer_brokerage` Where `daily` = :daily And `layer_id` = :layerId And `deliver_finish_time` > 0';
            foreach ($mysqlReport->query($sql, [':daily' => $daily, ':layerId' => $layer_id]) as $row) {
                $deliver_finish_time = $row['deliver_finish_time'];
            }
            if (!empty($deliver_finish_time)) {
                return;
            }

            $deal_list = [];
            $sql = 'select daily,user_id,user_key,layer_id,brokerage from daily_user_brokerage where daily=:daily And `layer_id` = :layerId';
            $brokerage_list = iterator_to_array($mysqlReport->query($sql, [':daily' => $daily, ':layerId' => $layer_id]));
            if (!empty($brokerage_list)) {
                $user_list = array_column($brokerage_list, 'user_id');
                foreach ($brokerage_list as $key => $val) {
                    $sqls = 'select deal_key,account_name from user_info_intact where user_id=:user_id';
                    $param = [':user_id' => $val['user_id']];
                    foreach ($mysqlUser->query($sqls, $param) as $row) {
                        $deal_list[] = ['user_id' => $val['user_id'], 'user_key' => $val['user_key'], 'account_name' => $row['account_name'], 'layer_id' => $val['layer_id'], 'daily' => $daily, 'brokerage' => $val['brokerage'], 'deal_key' => $row['deal_key']];
                    }
                }
            }

            if (!empty($deal_list)) {
                foreach ($deal_list as $k => $v) {
                    $list = [];
                    $mysql = $config->__get('data_'.$v['deal_key']);
                    $list[] = [
                        'user_id' => $v['user_id'],
                        'user_key' => $v['user_key'],
                        'account_name' => empty($v['account_name']) ? '' : $v['account_name'],
                        'layer_id' => $v['layer_id'],
                        'daily' => $daily,
                        'brokerage' => $v['brokerage'],
                    ];
                    $mysql->brokerage_deliver->load($list, [], '');
                    //更新派发时间
                    $sqls = 'update daily_user_brokerage '.'set deliver_time=:deliver_time where user_id=:user_id and daily=:daily';
                    $mysqlReport->execute($sqls, [':deliver_time' => time(), ':user_id' => $v['user_id'], ':daily' => $daily]);
                    $end_time = time();

                    $layer_sql = 'update daily_layer_brokerage set auto_deliver = :auto_deliver,deliver_staff_id = :deliver_staff_id,deliver_staff_name = :deliver_staff_name,deliver_launch_time = :deliver_launch_time,deliver_finish_time = :deliver_finish_time where daily = :daily and layer_id = :layer_id';
                    $params = [
                        'auto_deliver' => 0,
                        'deliver_staff_id' => 0,
                        'deliver_staff_name' => 0,
                        'deliver_launch_time' => time(),
                        'deliver_finish_time' => $end_time,
                        'daily' => $daily,
                        'layer_id' => $layer_id,
                    ];
                    $mysqlReport->execute($layer_sql, $params);
                }
            }

            //推送用户余额
            if (!empty($user_list)) {
                $adapter->plan('User/Balance', ['user_list' => $user_list], time(), 6);
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        }
    }
}
