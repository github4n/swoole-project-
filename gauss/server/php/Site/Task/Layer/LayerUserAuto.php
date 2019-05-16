<?php

namespace Site\Task\Layer;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @description   会员自动升级
 * @Author  Rose
 * @date  2019-05-08
 * @links  Layer/LayerUserAuto
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class LayerUserAuto implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        try {
            $mysqlReport = $config->data_report;
            $mysqlUser = $config->data_user;
            $daily = date('Ymd', strtotime('today') - 86400);

            $sql = 'select layer_id from layer_info where layer_type=2';
            $layer_list = [];

            foreach ($mysqlUser->query($sql) as $row) {
                $layer_list[] = $row['layer_id'];
            }

            $sql = 'select user_id,layer_id from user_info_intact where layer_id in :layer_list ';
            $user_list = iterator_to_array($mysqlUser->query($sql, [':layer_list' => $layer_list]));

            if (!empty($user_list)) {
                foreach ($user_list as $key => $val) {
                    $data_sql = 'select sum(deposit_amount) as deposit_amount,sum(wager_amount) as wager_amount from daily_user where user_id = :user_id and daily <= :daily ';
                    $userData = [];
                    foreach ($mysqlReport->query($data_sql, [':user_id' => $val['user_id'], ':daily' => $daily]) as $user) {
                        $userData = $user;
                    }
                    $layer_sql = 'select layer_id,layer_name,min_deposit_amount,min_bet_amount  from layer_info where layer_type=2 order by min_deposit_amount,min_bet_amount asc';
                    $all_layer_list = iterator_to_array($mysqlUser->query($layer_sql));
                    if (!empty($all_layer_list)) {
                        foreach ($all_layer_list as $k => $v) {
                            if ($userData['deposit_amount'] >= $v['min_deposit_amount'] && $userData['wager_amount'] >= $v['min_bet_amount']) {
                                $param = [
                                    'layer_id' => $v['layer_id'],
                                    'user_id' => $val['user_id'],
                                ];
                                $update_user_sql = 'update user_info set layer_id = :layer_id where user_id = :user_id';
                                $mysqlUser->execute($update_user_sql, $param);
                                $params = [
                                    'layer_id' => $v['layer_id'],
                                    'layer_name' => $v['layer_name'],
                                    'user_id' => $val['user_id'],
                                ];
                                $update_event_sql = 'update user_event set layer_id = :layer_id,layer_name = :layer_name where user_id = :user_id';
                                $mysqlReport->execute($update_event_sql, $params);
                                $update_cumulate_sql = 'update user_cumulate set layer_id = :layer_id,layer_name = :layer_name where user_id = :user_id';
                                $mysqlReport->execute($update_cumulate_sql, $params);
                            }
                        }
                    }
                }
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter = $context->getAdapter();
            $adapter->plan('Layer/LayerMemberAuto', [], time(), 8);
        }
    }
}
