<?php
/**
 * StaffLottery.php.
 *
 * @description   体系分彩种插入数据任务
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

class StaffLottery implements IHandler
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

            $sql = 'select major_id,major_name,minor_id,minor_name,agent_id,agent_name,model_key,model_name,game_key,game_name,'.
                'sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,count(distinct (if(bet_amount>0,user_id,null))) as bet_user,sum(wager_count) as wager_count,count(distinct (if(wager_amount>0,user_id,null))) as wager_user,sum(wager_amount) as wager_amount,sum(subsidy_amount>0) as subsidy_user,'.
                'sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,'.
                'sum(rebate_count) as rebate_count,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,'.
                'count(distinct(if(bonus_amount >0 ,user_id,null))) as bonus_user,count(distinct(if(rebate_amount > 0 ,user_id, null))) as rebate_user,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount from daily_user_lottery where daily=:daily group by major_id,major_name,minor_id,minor_name,agent_id,agent_name,model_key,model_name,game_key,game_name';
            $generator = $mysqlReport->query($sql, [':daily' => $daily]);
            $mysqlReport->daily_staff_lottery->import($generator, [
                'daily' => $daily, ], 'replace');

            $weekly = intval(date('oW', $time));
            $first_day = date('Ymd',strtotime('this week',$time));
            $last_day = date('Ymd',strtotime($first_day) + 7 * 86400 -1);
            $sql = 'select major_id,major_name,minor_id,minor_name,agent_id,agent_name,model_key,model_name,game_key,game_name,'.
                'sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,count(distinct(if(bet_count>0 ,user_id, null))) as bet_user,count(distinct(if(subsidy_amount>0 ,user_id, null))) as subsidy_user,'.
                'sum(bonus_count) as bonus_count,sum(wager_count) as wager_count,count(distinct(if(wager_amount > 0 ,user_id, null))) as wager_user,sum(wager_amount) as wager_amount,sum(bonus_amount) as bonus_amount,count(distinct(if(bonus_amount >0 ,user_id,null))) as bonus_user,'.
                'sum(rebate_count) as rebate_count,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,'.
                'count(distinct(if(rebate_amount > 0 ,user_id, null))) as rebate_user,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount '.
                'from daily_user_lottery where daily between :first_day and :last_day group by major_id,major_name,minor_id,'.
                'minor_name,agent_id,agent_name,model_key,model_name,game_key,game_name';
            $generator = $mysqlReport->query($sql, [':first_day' => $first_day, ':last_day' => $last_day]);
            $mysqlReport->weekly_staff_lottery->import($generator, [
                'weekly' => $weekly, ], 'replace');

            $monthly = intval(date('Ym', $time));
            $first_day = date('Ym01', $time);
            $last_day = date('Ymd', strtotime(date('Y-m-01', $time).' +1 month -1 day'));
            $sql = 'select major_id,major_name,minor_id,minor_name,agent_id,agent_name,model_key,model_name,game_key,game_name,'.
                'sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(wager_count) as wager_count,count(distinct(if(wager_amount > 0,user_id ,null))) as wager_user,sum(wager_amount) as wager_amount,count(distinct(if(bet_amount>0,user_id,null))) as bet_user,count(distinct(if(subsidy_amount>0 ,user_id, null))) as subsidy_user,'.
                'sum(bonus_count) as bonus_count,sum(bonus_amount) as bonus_amount,count(distinct(if(bonus_amount>0,user_id,null))) as bonus_user,'.
                'sum(rebate_count) as rebate_count,sum(rebate_amount) as rebate_amount,sum(subsidy_amount) as subsidy_amount,'.
                'count(distinct(if(rebate_amount>0,user_id,null))) as rebate_user,sum(wager_amount-bonus_amount-rebate_amount) as profit_amount '.
                'from daily_user_lottery where daily between :first_day and :last_day group by major_id,major_name,minor_id,'.
                'minor_name,agent_id,agent_name,model_key,model_name,game_key,game_name';

            $generator = $mysqlReport->query($sql, [':first_day' => $first_day, ':last_day' => $last_day]);
            $mysqlReport->monthly_staff_lottery->import($generator, [
                'monthly' => $monthly, ], 'replace');
        } catch (\PDOException $e) {
            throw new \PDOException($e);
        } finally {
            $adapter->plan('Report/UserGameSubsidy', ['time' => $time], time(), 9);
        }
    }
}
