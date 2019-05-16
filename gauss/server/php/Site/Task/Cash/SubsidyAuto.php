<?php

namespace Site\Task\Cash;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 *
 * @description  返水派发
 * @Author  Rose
 * @date  2019-05-08
 * @link Websocket: Cash/SubsidyAuto
 * @modifyAuthor
 * @modifyDate
 *
 * */

class SubsidyAuto implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        try {
            $adapter = $context->getAdapter();
            $mysqlUser = $config->data_user;
            $subsidy_sql = 'select layer_id,deliver_time from subsidy_setting where auto_deliver = 0 and deliver_time>0';
            foreach ($mysqlUser->query($subsidy_sql) as $val) {
                $length = strlen($val['deliver_time']);
                $today = strtotime('today');
                if ($length > 2) {
                    $h = substr($val['deliver_time'], 0, ($length - 2));
                    $i = substr($val['deliver_time'], ($length - 2), 2);
                    $time = $today + $h * 3600 + $i * 60;
                } else {
                    $time = $today + $val['deliver_time'] * 60;
                }
                $adapter->plan('Cash/MemberSubsidy', ['layer_id' => $val['layer_id']], $time, 8);
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Cash/BrokerageAuto', [], time(), 8);
        }
    }
}
