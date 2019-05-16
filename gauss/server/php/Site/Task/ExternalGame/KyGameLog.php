<?php

namespace Site\Task\ExternalGame;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: KyGameLog.php
 * @description   ky获取注单记录任务
 * @Author  ayden
 * @date  2019-04-08
 * @links  initialize.php
 * @returndata 
 * @modifyAuthor
 * @modifyTime
 */

class KyGameLog implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data = $context->getData();
        if ($data['data']['status'] == 200) {
            $code = $data['data']['return_data']['d']['code'];
            if ($code == 0) {
                $site_key = $data['site_key'];
                $res = [];
                $result = $data['data']['return_data']['d']['list'];
                if ($result) {
                    foreach ($result as $key => $val) {
                        foreach ($val as $k => $v) {
                            $res[$k][$key] = $v;
                        }
                    }
                    foreach ($res as $key => $val) {
                        if (strpos($val['Accounts'], $site_key) !== false) {
                            $user_key = explode($site_key, $val['Accounts'])[1];
                            $mysqlUser = $config->data_user;
                            $user_sql = "select user_id,account_name,layer_id,deal_key from user_info_intact where user_key = :user_key";
                            $param = [":user_key" => $user_key];
                            $user_id = '';
                            $layer_id = '';
                            $deal_key = '';
                            $account_name = '';
                            foreach ($mysqlUser->query($user_sql, $param) as $item) {
                                $user_id = $item['user_id'];
                                $layer_id = $item['layer_id'];
                                $deal_key = $item['deal_key'];
                                $account_name = !empty($item['account_name']) ? $item['account_name'] : 0;
                            }
                            if ($deal_key) {
                                $mysqlDeal = $config->__get('data_' . $deal_key);
                                //判断是否已经存在数据
                                $external_data = json_encode($val);
                                $checkSql = "select audit_serial from external_audit where user_key = :user_key and play_time = :play_time";
                                $param = [":user_key" => $user_key, ":play_time" => strtotime($val['GameStartTime'] . " +0800")];
                                $audit_serial = '';
                                foreach ($mysqlDeal->query($checkSql, $param) as $v) {
                                    $audit_serial = $v['audit_serial'];
                                }
                                if (!$audit_serial) {
                                    $sql = 'INSERT INTO external_audit SET user_id = :user_id, user_key = :user_key, layer_id = :layer_id, 
                                account_name = :account_name,external_type = :external_type, external_key = :external_key, external_data = :external_data,
                             game_key = :game_key, play_time = :play_time,audit_amount = :audit_amount';
                                    $params = [
                                        ':user_id' => $user_id,
                                        ':user_key' => $user_key,
                                        ':layer_id' => $layer_id,
                                        ':account_name' => $account_name,
                                        ':external_type' => 'ky',
                                        ':external_data' => $external_data,
                                        ':game_key' => 'ky_' . $val['KindID'],
                                        ':play_time' => strtotime($val['GameStartTime'] . " +0800"),
                                        ':audit_amount' => $val['CellScore'],
                                        ':external_key' => $user_key . strtotime($val['GameStartTime'] . " +0800")
                                    ];
                                    $mysqlDeal->execute($sql, $params);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
