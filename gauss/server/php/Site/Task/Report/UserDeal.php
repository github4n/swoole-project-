<?php
/**
 * UserDeal.php.
 *
 * @description   用户交易日报插入数据任务
 * @Author  nathan
 * @date  2019-04-07
 * @links  Initialize.php
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */

namespace Site\Task\Report;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class UserDeal implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        ['time' => $time] = $context->getData();
        try {
            // TODO: Implement onTask() method.
            $daily = intval(date('Ymd', $time));
            $start_time = strtotime('today', $time);
            $end_time = $start_time + 86399;
            $mysqlReport = $config->data_report;

            //检测数据是否锁定
            $dailyInfo = [];
            $sql = 'select daily from daily_status where daily=:daily and frozen=1';
            foreach ($mysqlReport->query($sql, [':daily' => $daily]) as $row) {
                $dailyInfo = $row;
            }
            if (!empty($dailyInfo)) {
                return;
            }
            $sql = 'select user_id,deal_type,sum(vary_money) as vary_money,sum(vary_deposit_audit) as vary_deposit_audit,sum(vary_coupon_audit) as vary_coupon_audit,count(deal_serial) as deal_count from deal where deal_time between :start_time and :end_time group by user_id,deal_type,user_key';

            $data = array();
            foreach ($config->deal_list as $deal) {
                $mysql = $config->__get('data_'.$deal);
                foreach ($mysql->query($sql, [':start_time' => $start_time, ':end_time' => $end_time]) as $row) {
                    $param = [':user_id' => $row['user_id']];
                    $user_sql = 'select user_key,user_name,layer_id,layer_name,major_id,major_name,minor_id,minor_name,agent_id,agent_name,broker_1_id,broker_1_key,broker_1_name,broker_2_id,broker_2_key,broker_2_name,broker_3_id,broker_3_key,broker_3_name from user_cumulate where user_id=:user_id';
                    $userInfo = [];
                    foreach ($mysqlReport->query($user_sql, $param) as $value) {
                        $userInfo = $value;
                    }
                    if (!empty($userInfo)) {
                        $result = [
                            'daily' => $daily,
                            'user_id' => $row['user_id'],
                            'user_key' => $userInfo['user_key'],
                            'user_name' => $userInfo['user_name'],
                            'layer_id' => $userInfo['layer_id'],
                            'layer_name' => $userInfo['layer_name'],
                            'major_id' => $userInfo['major_id'],
                            'major_name' => $userInfo['major_name'],
                            'minor_id' => $userInfo['minor_id'],
                            'minor_name' => $userInfo['minor_name'],
                            'agent_id' => $userInfo['agent_id'],
                            'agent_name' => $userInfo['agent_name'],
                            'broker_1_id' => $userInfo['broker_1_id'],
                            'broker_1_key' => $userInfo['broker_1_key'],
                            'broker_1_name' => $userInfo['broker_1_name'],
                            'broker_2_id' => $userInfo['broker_2_id'],
                            'broker_2_key' => $userInfo['broker_2_key'],
                            'broker_2_name' => $userInfo['broker_2_name'],
                            'broker_3_id' => $userInfo['broker_3_id'],
                            'broker_3_key' => $userInfo['broker_3_key'],
                            'broker_3_name' => $userInfo['broker_3_name'],
                            'deal_type' => $row['deal_type'],
                            'deal_count' => $row['deal_count'],
                            'vary_money' => $row['vary_money'],
                            'vary_deposit_audit' => $row['vary_deposit_audit'],
                            'vary_coupon_audit' => $row['vary_coupon_audit'],
                        ];
                        $data[] = $result;
                    }
                }
            }

            $mysqlReport->daily_user_deal->load($data, [], 'replace');
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/UserLottery', ['time' => $time], time(), 9);
        }
    }
}
