<?php

namespace Site\Task\ExternalGame;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: FgGameLog.php
 * @description   fg获取注单记录任务
 * @Author  ayden
 * @date  2019-04-08
 * @links  initialize.php
 * @returndata 
 * @modifyAuthor
 * @modifyTime
 */

class FgGameLog implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data = $context->getData();
        $adapter = $context->getAdapter();

        $result = isset($data['data']['res']['data']) ? $data['data']['res']['data'] : '';
        $gt = isset($data['data']['gt']) ? $data['data']['gt'] : '';
        $page_key = isset($data['data']['res']['page_key']) ? $data['data']['res']['page_key'] : '';
        if (isset($data['data']['http_code']) && $data['data']['http_code'] == 200) {
            if (!empty($page_key) || $page_key != 'none')
                $adapter->plan('NotifyPlat', ['path' => 'ExternalGame/fg', 'data' => ['data' => ['gt' => $gt, 'action' => 'get_log_page', 'page_key' => $page_key]]]);
        }
        if (!empty($result)) {
            foreach ($result as $value) {
                //游戏类型
                $gt = isset($value['gt']) ? $value['gt'] : 'fish';
                $mysqlUser = $config->data_user;
                $fg_game_id = isset($value['game_id']) ? $value['game_id'] : '';
                $game_key = 'fg_' . $fg_game_id;
                $play_name = isset($value['player_name']) ? $value['player_name'] : '';
                $nice_name = isset($value['nickname']) ? $value['nickname'] : '';

                //捕鱼和其它区分投注
                if ($gt == 'fish') {
                    $fg_time = isset($value['end_time']) ? $value['end_time'] : time();
                    $player_name = $nice_name;
                    $bet_amount = isset($value['bullet_chips']) ? $value['bullet_chips'] : '';
                } else {
                    $fg_time = isset($value['time']) ? $value['time'] : time();
                    $player_name = $play_name;
                    $bet_amount = isset($value['all_bets']) ? $value['all_bets'] : '';
                }
                //截取两位
                $bet_amount = substr(sprintf("%.3f", $bet_amount), 0, -1);
                $user_id_sql = "select user_id from user_fungaming where fg_member_code = :fg_member_code";
                $param = [":fg_member_code" => $player_name];
                $user_id = '';
                foreach ($mysqlUser->query($user_id_sql, $param) as $item) {
                    $user_id = $item['user_id'];
                }
                //系统不存在该fg会员则无法入库
                if ($user_id) {
                    $user_info_sql = "select user_key,layer_id,deal_key,account_name from user_info_intact where user_id = :user_id";
                    $param = [":user_id" => $user_id];
                    foreach ($mysqlUser->query($user_info_sql, $param) as $val) {
                        $user_key = $val['user_key'];
                        $layer_id = $val['layer_id'];
                        $deal_key = $val['deal_key'];
                        $account_name = !empty($val['account_name']) ? $val['account_name'] : 0;
                    }
                    if ($deal_key) {
                        $mysqlDeal = $config->__get('data_' . $deal_key);
                        $time = time();
                        $fg_data = json_encode($value);
                        //检查数据是否重复写入
                        $check_sql = "select audit_serial from external_audit where user_key = :user_key and play_time = :play_time";
                        $param = [":user_key" => $user_key, ":play_time" => $fg_time];
                        $audit_serial = '';
                        foreach ($mysqlDeal->query($check_sql, $param) as $v) {
                            $audit_serial = $v['audit_serial'];
                        }
                        if (!$audit_serial) {
                            $audit_sql = "insert into external_audit set user_id = :user_id, user_key = :user_key,
    account_name = :account_name, external_type = :external_type, external_key = :external_key, layer_id = :layer_id, audit_amount = :audit_amount,
    audit_time= :audit_time, external_data = :external_data, game_key = :game_key, play_time = :play_time";
                            $param = [
                                ":user_id" => $user_id,
                                ":user_key" => $user_key,
                                ":account_name" => $account_name,
                                ":layer_id" => $layer_id,
                                ":audit_amount" => $bet_amount,
                                ":audit_time" => $time,
                                ":external_data" => $fg_data,
                                ":game_key" => $game_key,
                                ":play_time" => $fg_time,
                                ":external_key" => $value['id'],
                                ":external_type" => 'fg'
                            ];
                            //先入三方打码稽核表
                            $mysqlDeal->execute($audit_sql, $param);
                        }
                    }
                }
            }
        }
    }
}
