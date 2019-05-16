<?php

namespace Plat\Websocket\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * Index class.
 *
 * @description   description
 * @Author  rose
 * @date  2019-05-08
 * @links Index/index
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class Index extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $mysql = $config->data_analysis;
        $mysqlAdmin = $config->data_admin;
        $data = $context->getData();

        $date = isset($data['date']) ? $data['date'] : '';
        if (empty($date)) {
            $date = 'today';
        }

        if ($date == 'today') {
            $time = intval(date('Ymd', strtotime('today')));
            $sql = 'select sum(user_all) as user_all,sum(user_register) as user_register,sum(user_first_deposit) as user_first_deposit,'.
                'sum(user_active) as user_active,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all,sum(profit_all) as profit_all '.
            'from daily_site where daily =:daily ';

            $site_sql = 'select sum(bet_cards) as bet_cards,sum(profit_cards) as profit_cards ,site_key,site_name,sum(user_all) as user_all,sum(user_register) as user_register,'.
                'sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active,sum(bet_all) as bet_all,'.
                'sum(bonus_all) as bonus_all,sum(profit_all) as profit_all,sum(bet_game) as bet_game,sum(profit_game) as profit_game,'.
                'sum(bet_lottery) as bet_lottery, sum(bonus_lottery) as bonus_lottery ,sum(profit_lottery) as profit_lottery '.
                'from daily_site where daily =:daily group by site_key,site_name';

            $rank_sql = 'select site_key,site_name,bet_all from daily_site where daily =:daily order by bet_all desc';
            foreach ($mysql->query($sql, [':daily' => $time]) as $row) {
                $totalData = $row;
            }
            $siteData = iterator_to_array($mysql->query($site_sql, [':daily' => $time]));

            $rankData = [];
            foreach ($mysql->query($rank_sql, [':daily' => $time]) as $row) {
                $rankData[] = $row;
            }
        } elseif ($date == 'yesterday') {
            $time = intval(date('Ymd', strtotime('yesterday')));
            $sql = 'select  sum(user_all) as user_all,sum(user_register) as user_register,sum(user_first_deposit) as user_first_deposit,'.
                'sum(user_active) as user_active,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all,sum(profit_all) as profit_all '.
                'from daily_site where daily =:daily ';

            $site_sql = 'select sum(bet_cards) as bet_cards,sum(profit_cards) as profit_cards ,site_key,site_name,sum(user_all) as user_all,sum(user_register) as user_register,'.
                'sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active,sum(bet_all) as bet_all,'.
                'sum(bonus_all) as bonus_all,sum(profit_all) as profit_all,sum(bet_game) as bet_game,sum(profit_game) as profit_game,'.
                'sum(bet_lottery) as bet_lottery, sum(bonus_lottery) as bonus_lottery ,sum(profit_lottery) as profit_lottery '.
                'from daily_site where daily =:daily group by site_key,site_name';

            $rank_sql = 'select site_key,site_name,bet_all from daily_site where daily =:daily order by bet_all desc';
            foreach ($mysql->query($sql, [':daily' => $time]) as $row) {
                $totalData = $row;
            }
            $siteData = iterator_to_array($mysql->query($site_sql, [':daily' => $time]));

            $rankData = [];
            foreach ($mysql->query($rank_sql, [':daily' => $time]) as $row) {
                $rankData[] = $row;
            }
        } elseif ($date == 'thisWeek') {
            $time = intval(date('oW', strtotime('today')));
            $sql = 'select sum(user_all) as user_all,sum(user_register) as user_register,sum(user_first_deposit) as user_first_deposit,'.
                'sum(user_active) as user_active,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all,sum(profit_all) as profit_all '.
                'from weekly_site where weekly =:weekly';

            $site_sql = 'select sum(bet_cards) as bet_cards,sum(profit_cards) as profit_cards, site_key,site_name,sum(user_all) as user_all,sum(user_register) as user_register,'.
                'sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active,sum(bet_all) as bet_all,'.
                'sum(bonus_all) as bonus_all,sum(profit_all) as profit_all,sum(bet_game) as bet_game,sum(profit_game) as profit_game,'.
                'sum(bet_lottery) as bet_lottery, sum(bonus_lottery) as bonus_lottery ,sum(profit_lottery) as profit_lottery '.
                'from weekly_site where weekly =:weekly  group by site_key,site_name';

            $rank_sql = 'select site_key,site_name,bet_all from weekly_site where weekly =:weekly order by bet_all desc';
            foreach ($mysql->query($sql, [':weekly' => $time]) as $row) {
                $totalData = $row;
            }
            $siteData = iterator_to_array($mysql->query($site_sql, [':weekly' => $time]));

            $rankData = [];
            foreach ($mysql->query($rank_sql, [':weekly' => $time]) as $row) {
                $rankData[] = $row;
            }
        } elseif ($date == 'LastWeek') {
            $time = intval(date('oW', strtotime('-1 week')));
            $sql = 'select sum(user_all) as user_all,sum(user_register) as user_register,sum(user_first_deposit) as user_first_deposit,'.
                'sum(user_active) as user_active,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all,sum(profit_all) as profit_all '.
                'from weekly_site where weekly =:weekly';

            $site_sql = 'select  sum(bet_cards) as bet_cards,sum(profit_cards) as profit_cards ,  site_key,site_name,sum(user_all) as user_all,sum(user_register) as user_register,'.
                'sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active,sum(bet_all) as bet_all,'.
                'sum(bonus_all) as bonus_all,sum(profit_all) as profit_all,sum(bet_game) as bet_game,sum(profit_game) as profit_game,'.
                'sum(bet_lottery) as bet_lottery, sum(bonus_lottery) as bonus_lottery ,sum(profit_lottery) as profit_lottery '.
                'from weekly_site where weekly =:weekly  group by site_key,site_name';

            $rank_sql = 'select site_key,site_name,bet_all from weekly_site where weekly =:weekly  order by bet_all desc';
            foreach ($mysql->query($sql, [':weekly' => $time]) as $row) {
                $totalData = $row;
            }
            $siteData = iterator_to_array($mysql->query($site_sql, [':weekly' => $time]));

            $rankData = [];
            foreach ($mysql->query($rank_sql, [':weekly' => $time]) as $row) {
                $rankData[] = $row;
            }
        } elseif ($date == 'thisMonth') {
            $time = intval(date('Ym', strtotime('today')));
            $sql = 'select  sum(user_all) as user_all,sum(user_register) as user_register,sum(user_first_deposit) as user_first_deposit,'.
                'sum(user_active) as user_active,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all,sum(profit_all) as profit_all '.
                'from monthly_site where monthly =:monthly';

            $site_sql = 'select sum(bet_cards) as bet_cards,sum(profit_cards) as profit_cards ,site_key,site_name,sum(user_all) as user_all,sum(user_register) as user_register,'.
                'sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active,sum(bet_all) as bet_all,'.
                'sum(bonus_all) as bonus_all,sum(profit_all) as profit_all,sum(bet_game) as bet_game,sum(profit_game) as profit_game,'.
                'sum(bet_lottery) as bet_lottery, sum(bonus_lottery) as bonus_lottery ,sum(profit_lottery) as profit_lottery '.
                'from monthly_site where monthly =:monthly  group by site_key,site_name';

            $rank_sql = 'select site_key,site_name,bet_all from monthly_site where monthly =:monthly  order by bet_all desc';
            foreach ($mysql->query($sql, [':monthly' => $time]) as $row) {
                $totalData = $row;
            }
            $siteData = iterator_to_array($mysql->query($site_sql, [':monthly' => $time]));

            $rankData = [];
            foreach ($mysql->query($rank_sql, [':monthly' => $time]) as $row) {
                $rankData[] = $row;
            }
        } elseif ($date == 'LastMonth') {
            $time = intval(date('Ym', strtotime('last month')));
            $sql = 'select  sum(user_all) as user_all,sum(user_register) as user_register,sum(user_first_deposit) as user_first_deposit,'.
                'sum(user_active) as user_active,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all,sum(profit_all) as profit_all '.
                'from monthly_site where monthly =:monthly';

            $site_sql = 'select sum(bet_cards) as bet_cards,sum(profit_cards) as profit_cards, site_key,site_name,sum(user_all) as user_all,sum(user_register) as user_register,'.
                'sum(user_first_deposit) as user_first_deposit,sum(user_active) as user_active,sum(bet_all) as bet_all,'.
                'sum(bonus_all) as bonus_all,sum(profit_all) as profit_all,sum(bet_game) as bet_game,sum(profit_game) as profit_game,'.
                'sum(bet_lottery) as bet_lottery, sum(bonus_lottery) as bonus_lottery ,sum(profit_lottery) as profit_lottery '.
                'from monthly_site where monthly =:monthly  group by site_key,site_name';

            $rank_sql = 'select site_key,site_name,bet_all from monthly_site where monthly =:monthly order by bet_all desc';
            foreach ($mysql->query($sql, [':monthly' => $time]) as $row) {
                $totalData = $row;
            }
            $siteData = iterator_to_array($mysql->query($site_sql, [':monthly' => $time]));

            $rankData = [];
            foreach ($mysql->query($rank_sql, [':monthly' => $time]) as $row) {
                $rankData[] = $row;
            }
        } else {
            $context->reply(['status' => 300, 'msg' => '时间参数错误']);

            return;
        }
        $total_data = [
            'user_all' => empty($totalData['user_all']) ? 0 : $totalData['user_all'],
            'user_active' => empty($totalData['user_active']) ? 0 : $totalData['user_active'],
            'user_register' => empty($totalData['user_register']) ? 0 : $totalData['user_register'],
            'user_first_deposit' => empty($totalData['user_first_deposit']) ? 0 : $totalData['user_first_deposit'],
            'bet_all' => empty($totalData['bet_all']) ? 0 : $totalData['bet_all'],
            'profit_all' => empty($totalData['profit_all']) ? 0 : $totalData['profit_all'],
        ];
        $total_data['bet_all'] = $this->intercept_num($total_data['bet_all']);
        $total_data['profit_all'] = $this->intercept_num($total_data['profit_all']);

        $sql = 'select site_key,site_name from site';
        $site_list = iterator_to_array($mysqlAdmin->query($sql));
        $site = [];
        foreach ($siteData as $key => $val) {
            $site[$key]['site_key'] = $val['site_key'];
            $site[$key]['site_name'] = $val['site_name'];
            $site[$key]['user_all'] = empty($val['user_all']) ? 0 : $val['user_all'];
            $site[$key]['user_active'] = empty($val['user_active']) ? 0 : $val['user_active'];
            $site[$key]['user_register'] = empty($val['user_register']) ? 0 : $val['user_register'];
            $site[$key]['user_first_deposit'] = empty($val['user_first_deposit']) ? 0 : $val['user_first_deposit'];
            $site[$key]['bet_all'] = empty($val['bet_all']) ? 0 : $val['bet_all'];
            $site[$key]['profit_all'] = empty($val['profit_all']) ? 0 : $val['profit_all'];
            $site[$key]['bet_lottery'] = empty($val['bet_lottery']) ? 0 : $val['bet_lottery'];
            $site[$key]['profit_lottery'] = empty($val['profit_lottery']) ? 0 : $val['profit_lottery'];
            $site[$key]['bet_game'] = empty($val['bet_game']) ? 0 : $val['bet_game'];
            $site[$key]['profit_game'] = empty($val['profit_game']) ? 0 : $val['profit_game'];
            $site[$key]['bet_cards'] = empty($val['bet_cards']) ? 0 : $val['bet_cards'];
            $site[$key]['profit_cards'] = empty($val['profit_cards']) ? 0 : $val['profit_cards'];

            $site[$key]['bet_all'] = $this->intercept_num($site[$key]['bet_all']);
            $site[$key]['profit_all'] = $this->intercept_num($site[$key]['profit_all']);
            $site[$key]['bet_lottery'] = $this->intercept_num($site[$key]['bet_lottery']);
            $site[$key]['profit_lottery'] = $this->intercept_num($site[$key]['profit_lottery']);
            $site[$key]['bet_game'] = $this->intercept_num($site[$key]['bet_game']);
            $site[$key]['profit_game'] = $this->intercept_num($site[$key]['profit_game']);
            $site[$key]['bet_cards'] = $this->intercept_num($site[$key]['bet_cards']);
            $site[$key]['profit_cards'] = $this->intercept_num($site[$key]['profit_cards']);
        }
        if (empty($site)) {
            foreach ($site_list as $kk => $vv) {
                $site[$kk]['site_key'] = $vv['site_key'];
                $site[$kk]['site_name'] = $vv['site_name'];
                $site[$kk]['user_all'] = 0;
                $site[$kk]['user_active'] = 0;
                $site[$kk]['user_register'] = 0;
                $site[$kk]['user_first_deposit'] = 0;
                $site[$kk]['bet_all'] = 0;
                $site[$kk]['profit_all'] = 0;
                $site[$kk]['bet_lottery'] = 0;
                $site[$kk]['profit_lottery'] = 0;
                $site[$kk]['bet_game'] = 0;
                $site[$kk]['profit_game'] = 0;
                $site[$kk]['bet_cards'] = 0;
                $site[$kk]['profit_cards'] = 0;

                $site[$kk]['bet_all'] = $this->intercept_num($site[$kk]['bet_all']);
                $site[$kk]['profit_all'] = $this->intercept_num($site[$kk]['profit_all']);
                $site[$kk]['bet_lottery'] = $this->intercept_num($site[$kk]['bet_lottery']);
                $site[$kk]['profit_lottery'] = $this->intercept_num($site[$kk]['profit_lottery']);
                $site[$kk]['bet_game'] = $this->intercept_num($site[$kk]['bet_game']);
                $site[$kk]['profit_game'] = $this->intercept_num($site[$kk]['profit_game']);
                $site[$kk]['bet_cards'] = $this->intercept_num($site[$kk]['bet_cards']);
                $site[$kk]['profit_cards'] = $this->intercept_num($site[$kk]['profit_cards']);
            }
        }
        $rank = [];
        if (!empty($rankData)) {
            foreach ($rankData as $k => $v) {
                $rank[$k]['site_key'] = $v['site_key'];
                $rank[$k]['site_name'] = $v['site_name'];
                $rank[$k]['bet_all'] = empty($v['bet_all']) ? 0 : $v['bet_all'];
                $rank[$k]['bet_all'] = $this->intercept_num($rank[$k]['bet_all']);
            }
        } else {
            foreach ($site_list as $ks => $vs) {
                $rank[$ks]['site_key'] = $vs['site_key'];
                $rank[$ks]['site_name'] = $vs['site_name'];
                $rank[$ks]['bet_all'] = 0;
                $rank[$ks]['bet_all'] = $this->intercept_num($rank[$ks]['bet_all']);
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => ['totalData' => $total_data, 'siteData' => $site, 'rank' => $rank]]);
    }
}
