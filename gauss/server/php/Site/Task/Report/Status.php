<?php

/**
 * Status.php.
 *
 * @description   日数据状态插入数据任务
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

class Status implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $adapter = $context->getAdapter();
        $cache = $config->cache_site;
        ['time' => $time] = $context->getData();
        try {
            $mysqlReport = $config->data_report;
            $status_time = $cache->hget('Status', 'times');
            $run_time = date('Ymd', $time);
            $now_time = date('Ymd', time());
            if ($now_time == $status_time && $status_time > $run_time) {
                $sql = 'select daily from daily_status where daily=:daily';
                $result = $mysqlReport->execute($sql, [':daily' => $run_time]);
                if ($result > 0) {
                    $sql = 'update daily_status set frozen =:frozen where  daily=:daily';
                    $param = [':frozen' => 1, ':daily' => $run_time];
                } else {
                    $sql = 'insert into daily_status set daily=:daily, frozen =:frozen';
                    $param = [':frozen' => 1, ':daily' => $run_time];
                }
                try {
                    $mysqlReport->execute($sql, $param);
                    $adapter->plan('Cash/SubsidyAuto', [], time() + 600, 8);
                    $adapter->plan('Report/UserDeal', ['time' => $time + 86400], time(), 9);
                } catch (\PDOException $e) {
                    throw new \PDOException($e);
                }
            } else {
                $adapter->plan('Report/UserDeal', ['time' => $time], time() + 180, 9);
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $cache->hset('Status', 'times', date('Ymd', time()));
        }
    }
}
