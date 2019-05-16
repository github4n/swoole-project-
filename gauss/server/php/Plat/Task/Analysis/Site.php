<?php
namespace Plat\Task\Analysis;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Site implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['site_key' => $site_key, 'time' => $time] = $context->getData();
        $adapter = $context->getAdapter();
        $mysql = $config->data_public;
        $sql = 'select game_key from lottery_game';
        foreach ($mysql->query($sql) as $row) {
            $game_key = $row['game_key'];
            $adapter->plan('Analysis/SiteLottery', ['site_key' => $site_key, 'game_key' => $game_key, 'time' => $time],time(),9);
        }
        $adapter->plan('Analysis/SiteExternal', ['time' => $time ,'site_key' => $site_key], time(), 9);
        $allRows = [];
        $mysql = $config->data_admin;
        $site_name = $site_key;
        $sql = 'select site_name from site where site_key=:site_key';
        foreach ($mysql->query($sql, ['site_key' => $site_key]) as $row) {
            $site_name = $row['site_name'];
        }

        $analysis = $config->data_analysis;
        $siteReport = $config->__get('data_' . $site_key . '_report');
        $bet = [];
        $user = [];
        $daily_bet_all = 0;
        $daily_bonus_all = 0;
        $daily_profit_all = 0;
        $daily_rebate_all = 0;
        $weekly_bet_all = 0;
        $weekly_bonus_all = 0;
        $weekly_profit_all = 0;
        $weekly_rebate_all = 0;
        $monthly_bet_all = 0;
        $monthly_bonus_all = 0;
        $monthly_profit_all = 0;
        $monthly_rebate_all = 0;
        $daily = intval(date('Ymd', $time));
        $sql = "select sum(user_all) as user_all,sum(user_register) as user_register,".
            "sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active ".
            "from daily_staff where daily=:daily";
        foreach ($siteReport->query($sql, [':daily' => $daily]) as $row){
            $user = $row;
        }
        if(empty($user)){
            $rows = ['user_all'=>0,
                'user_register' => 0,'user_first_deposit' => 0, 'user_active' => 0];
        }else{
            $rows = [
                'user_all'=>empty($user['user_all']) ? 0 :$user['user_all'],
                'user_register' => empty($user['user_register']) ? 0 :$user['user_register'],
                'user_first_deposit' => empty($user['user_first_deposit']) ? 0 : $user['user_first_deposit'],
                'user_active' => empty($user['user_active']) ? 0 : $user['user_active']];
        }
        $sql = "select sum(bet_amount) as bet_lottery, sum(bonus_amount) as bonus_lottery,sum(rebate_amount) as rebate_amount,".
            "sum(profit_amount) as profit_lottery from daily_staff_lottery where daily=:daily";
        foreach ($siteReport->query($sql, [':daily' => $daily]) as $row){
            $bet = $row;
        }
        if(!empty($bet)){
            $rows += ['bet_lottery' => empty($bet['bet_lottery']) ? 0 :$bet['bet_lottery'],
                'bonus_lottery' => empty($bet['bonus_lottery']) ? 0 : $bet['bonus_lottery'],
                'profit_lottery' => empty($bet['profit_lottery']) ? 0 : $bet['profit_lottery'],
            ];
            $daily_bet_all +=  $bet['bet_lottery'];
            $daily_bonus_all +=  $bet['bonus_lottery'];
            $daily_profit_all +=  $bet['profit_lottery'];
            $daily_rebate_all +=  $bet['rebate_amount'];

        }else{
            $rows += ['bet_lottery' => 0,'bonus_lottery' => 0,
                'profit_lottery' => 0,
            ];
        }
        $sql = "select category_key,sum(bet_amount) as bet_amount, sum(bonus_amount) as bonus_amount,sum(subsidy_amount) as rebate_amount,".
            "sum(profit_amount) as profit_amount,category_key from daily_staff_external ".
            "where daily=:daily group by category_key";
        $category = [];
        foreach ($siteReport->query($sql, [':daily' => $daily]) as $row){
            $category[] = $row;
        }
        if(!empty($category)){
            foreach ($category as $key=>$val){
                $rows += ['bonus_'.$val['category_key'] => $val['bonus_amount'],'bet_'.$val['category_key'] => $val['bet_amount'],'profit_'.$val['category_key'] => $val['profit_amount']];
                $daily_bet_all +=  $val['bet_amount'];
                $daily_bonus_all +=  $val['bonus_amount'];
                $daily_profit_all +=  $val['profit_amount'];
                $daily_rebate_all +=  $val['rebate_amount'];
            }
        }
        foreach (['cards','video','sports','game'] as $cate){
        $rows += [
            'bonus_'.$cate => empty($row["bonus_".$cate]) ? 0 : $row["bonus_".$cate],
            'bet_'.$cate => empty($row["bet_".$cate]) ? 0 : $row["bet_".$cate],
            'profit_'.$cate => empty($row["profit_".$cate]) ? 0 : $row["profit_".$cate]
        ];
        }

        //总计
        $rows += ["bet_all"=>$daily_bet_all,"bonus_all"=>$daily_bonus_all,"profit_all"=>$daily_profit_all,"rebate"=>$daily_rebate_all];
        $allRows[] =  $rows;
        /* */
        $analysis->daily_site->load($allRows, ['daily' => $daily, 'site_key' => $site_key, 'site_name' => $site_name], 'replace');


        $weekly = intval(date('oW', $time));
        $sql = "select sum(user_all) as user_all,sum(user_register) as user_register,".
            "sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active ".
            "from weekly_staff where weekly=:weekly";
        foreach ($siteReport->query($sql, [':weekly' => $weekly]) as $row){
            $user = $row;
        }
        $week_rows = [
            'user_all'=>empty($user['user_all']) ? 0 :$user['user_all'],
            'user_register' => empty($user['user_register']) ? 0 :$user['user_register'],
            'user_first_deposit' => empty($user['user_first_deposit']) ? 0 : $user['user_first_deposit'],
            'user_active' => empty($user['user_active']) ? 0 : $user['user_active']];
        
        $sql = "select sum(bet_amount) as bet_lottery, sum(bonus_amount) as bonus_lottery,sum(rebate_amount) as rebate_amount,".
            "sum(profit_amount) as profit_lottery from weekly_staff_lottery where weekly=:weekly";
        foreach ($siteReport->query($sql, [':weekly' => $weekly]) as $row){
            $bet = $row;
        }
        $week_rows += ['bet_lottery' => empty($bet['bet_lottery']) ? 0 :$bet['bet_lottery'],
            'bonus_lottery' => empty($bet['bonus_lottery']) ? 0 : $bet['bonus_lottery'],
            'profit_lottery' => empty($bet['profit_lottery']) ? 0 : $bet['profit_lottery'],
        ];
        $weekly_bet_all +=  $bet['bet_lottery'];
        $weekly_bonus_all +=  $bet['bonus_lottery'];
        $weekly_profit_all +=  $bet['profit_lottery'];
        $weekly_rebate_all +=  $bet['rebate_amount'];
        
        $sql = "select category_key,sum(bet_amount) as bet_amount, sum(bonus_amount) as bonus_amount,sum(subsidy_amount) as rebate_amount,".
            "sum(profit_amount) as profit_amount,category_key from weekly_staff_external ".
            "where weekly=:weekly group by category_key";
        $category = [];
        foreach ($siteReport->query($sql, [':weekly' => $weekly]) as $row){
            $category[] = $row;
        }
        if(!empty($category)){
            foreach ($category as $key=>$val){
                $week_rows += ['bonus_'.$val['category_key'] => $val['bonus_amount'],'bet_'.$val['category_key'] => $val['bet_amount'],'profit_'.$val['category_key'] => $val['profit_amount']];
                $weekly_bet_all +=  $val['bet_amount'];
                $weekly_bonus_all +=  $val['bonus_amount'];
                $weekly_profit_all +=  $val['profit_amount'];
                $weekly_rebate_all +=  $val['rebate_amount'];
            }
        }
        foreach (['cards','video','sports','game'] as $cate){
            $week_rows += ['bonus_'.$cate => empty($row["bonus_".$cate]) ? 0 : $row["bonus_".$cate],'bet_'.$cate => empty($row["bet_".$cate]) ? 0 : $row["bet_".$cate],'profit_'.$cate => empty($row["profit_".$cate]) ? 0 : $row["profit_".$cate]];
        }

        //总计
        $week_rows += ["bet_all"=>$weekly_bet_all,"bonus_all"=>$weekly_bonus_all,"profit_all"=>$weekly_profit_all,"rebate"=>$weekly_rebate_all];
        $row_weeks[] =  $week_rows;
        $analysis->weekly_site->load($row_weeks, ['weekly' => $weekly, 'site_key' => $site_key, 'site_name' => $site_name], 'replace');
        
        $monthly = intval(date('Ym', $time));
        $sql = "select sum(user_all) as user_all,sum(user_register) as user_register,".
            "sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active ".
            "from monthly_staff where monthly=:monthly";
        foreach ($siteReport->query($sql, [':monthly' => $monthly]) as $row){
            $user = $row;
        }
        $week_row = [
            'user_all'=>empty($user['user_all']) ? 0 :$user['user_all'],
            'user_register' => empty($user['user_register']) ? 0 :$user['user_register'],
            'user_first_deposit' => empty($user['user_first_deposit']) ? 0 : $user['user_first_deposit'],
            'user_active' => empty($user['user_active']) ? 0 : $user['user_active']];
        $sql = "select sum(bet_amount) as bet_lottery, sum(bonus_amount) as bonus_lottery,sum(rebate_amount) as rebate_amount,".
            "sum(profit_amount) as profit_lottery from monthly_staff_lottery where monthly=:monthly";
        foreach ($siteReport->query($sql, [':monthly' => $monthly]) as $row){
            $bet = $row;
        }
        if(!empty($bet)){
            $week_row += ['bet_lottery' => empty($bet['bet_lottery']) ? 0 :$bet['bet_lottery'],
                'bonus_lottery' => empty($bet['bonus_lottery']) ? 0 : $bet['bonus_lottery'],
                'profit_lottery' => empty($bet['profit_lottery']) ? 0 : $bet['profit_lottery'],
            ];
            $monthly_bet_all +=  $bet['bet_lottery'];
            $monthly_bonus_all +=  $bet['bonus_lottery'];
            $monthly_profit_all +=  $bet['profit_lottery'];
            $monthly_rebate_all +=  $bet['rebate_amount'];

        }else{
            $week_row += ['bet_lottery' => 0,'bonus_lottery' => 0,
                'profit_lottery' => 0,
            ];
        }
        $sql = "select category_key,sum(bet_amount) as bet_amount, sum(bonus_amount) as bonus_amount,sum(subsidy_amount) as rebate_amount,".
            "sum(profit_amount) as profit_amount,category_key from monthly_staff_external ".
            "where monthly=:monthly group by category_key";
        $category = [];
        foreach ($siteReport->query($sql, [':monthly' => $monthly]) as $row){
            $category[] = $row;
        }
        if(!empty($category)){
            foreach ($category as $key=>$val){
                $rows += ['bonus_'.$val['category_key'] => $val['bonus_amount'],'bet_'.$val['category_key'] => $val['bet_amount'],'profit_'.$val['category_key'] => $val['profit_amount']];
                $monthly_bet_all +=  $val['bet_amount'];
                $monthly_bonus_all +=  $val['bonus_amount'];
                $monthly_profit_all +=  $val['profit_amount'];
                $monthly_rebate_all +=  $val['rebate_amount'];
            }
        }
        foreach (['cards','video','sports','game'] as $cate){
            $week_row += ['bonus_'.$cate => empty($row["bonus_".$cate]) ? 0 : $row["bonus_".$cate],'bet_'.$cate => empty($row["bet_".$cate]) ? 0 : $row["bet_".$cate],'profit_'.$cate => empty($row["profit_".$cate]) ? 0 : $row["profit_".$cate]];
        }
        //总计
        $week_row += ["bet_all"=>$monthly_bet_all,"bonus_all"=>$monthly_bonus_all,"profit_all"=>$monthly_profit_all,"rebate"=>$monthly_rebate_all];
        
        $row_month[] =  $week_row;
        $analysis->monthly_site->load($row_month, ['monthly' => $monthly, 'site_key' => $site_key, 'site_name' => $site_name], 'replace');

        $monthStart = strtotime('midnight first day of this month', $time);
        $monthEnd = strtotime('midnight first day of next month', $time);
        if (time() > $monthEnd + 3600) {
            $adapter->plan('Analysis/Tax', ['site_key' => $site_key, 'time' => $monthStart], time(), 9);
        }
    }
}
