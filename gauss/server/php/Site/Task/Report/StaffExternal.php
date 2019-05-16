<?php
/**
 * Staff_External.php.
 *
 * @description   外接口体系日报插入数据任务
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

class StaffExternal implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['time' => $time] = $context->getData();
        $adapter = $context->getAdapter();
        try {
            $daily = intval(date('Ymd', $time));
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

            $sqls = 'select major_id,major_name,minor_id,minor_name,agent_id,agent_name,category_key,interface_key,game_key,game_name,sum(bet_count) as bet_count,count(distinct(if(bet_amount>0 ,user_id ,null))) as bet_user,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,count(distinct(if(wager_amount>0 ,user_id ,null))) as wager_user,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,count(distinct (if(bonus_amount,user_id,null))) as bonus_user,sum(bonus_amount) as bonus_amount,count(distinct (if(subsidy_amount>0,user_id,null))) as subsidy_user,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount) as profit_amount from daily_user_external where  daily =:daily group by major_id,major_name,minor_id,minor_name,agent_id,agent_name,category_key,interface_key,game_key,game_name';
            $generator = iterator_to_array($mysqlReport->query($sqls, [':daily' => $daily]));
            $mysqlReport->daily_staff_external->load($generator, [
                'daily' => $daily, ], 'replace');

            //周报
            $weekly = intval(date('oW', $time));
            $first_day = date('Ymd',strtotime('this week',$time));
            $last_day = date('Ymd',strtotime($first_day) + 7 * 86400 -1);
            $sql = 'select major_id,major_name,minor_id,minor_name,agent_id,agent_name,category_key,interface_key,game_key,game_name,sum(bet_count) as bet_count,count(distinct(if(bet_amount>0 ,user_id ,null))) as bet_user,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,count(distinct(if(wager_amount>0 ,user_id ,null))) as wager_user,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,count(distinct (if(bonus_amount,user_id,null))) as bonus_user,sum(bonus_amount) as bonus_amount,count(distinct (if(subsidy_amount>0,user_id,null))) as subsidy_user,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount) as profit_amount from daily_user_external where daily between :first_day and :last_day group by major_id,major_name,minor_id,minor_name,agent_id,agent_name,category_key,interface_key,game_key,game_name';

            $generator = iterator_to_array($mysqlReport->query($sql, [':first_day' => $first_day, ':last_day' => $last_day]));
            $mysqlReport->weekly_staff_external->load($generator, [
                'weekly' => $weekly, ], 'replace');

            //月报
            $monthly = intval(date('Ym', $time));
            $first_day = date('Ym01', $time);
            $last_day = date('Ymd', strtotime(date('Y-m-01', $time).' +1 month -1 day'));
            $sql = 'select major_id,major_name,minor_id,minor_name,agent_id,agent_name,category_key,interface_key,game_key,game_name,sum(bet_count) as bet_count,count(distinct(if(bet_amount>0 ,user_id ,null))) as bet_user,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,count(distinct(if(wager_amount>0 ,user_id ,null))) as wager_user,sum(wager_amount) as wager_amount,sum(bonus_count) as bonus_count,count(distinct (if(bonus_amount,user_id,null))) as bonus_user,sum(bonus_amount) as bonus_amount,count(distinct (if(subsidy_amount>0,user_id,null))) as subsidy_user,sum(subsidy_amount) as subsidy_amount,sum(wager_amount-bonus_amount) as profit_amount from daily_user_external where daily between :first_day and :last_day group by major_id,major_name,minor_id,minor_name,agent_id,agent_name,category_key,interface_key,game_key,game_name';
            $generator = iterator_to_array($mysqlReport->query($sql, [':first_day' => $first_day, ':last_day' => $last_day]));
            $mysqlReport->monthly_staff_external->load($generator, [
                'monthly' => $monthly, ], 'replace');
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/StaffLottery', ['time' => $time], time(), 9);
        }
    }
}
