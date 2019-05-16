<?php

namespace Plat\Websocket\Report;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * SiteReport class.
 *
 * @description   站点彩票报表
 * @Author  rose
 * @date  2019-04-12
 * @links  Report/SiteReport {"site_key":"site1","date": ""}
 * 参数：site_key:站点key值,date:(1:今日,2:昨日,3:本周,4:上周,5:本月,6:上月)
 *
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 *
 * @modifyAuthor   blake
 * @modifyTime  2019-05-14
 */
class SiteReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('report_site', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $cache = $config->cache_plat;
        $mysqls = $config->data_analysis;
        $mysqlAdmin = $config->data_admin;

        $site_key = isset($data['site_key']) ? $data['site_key'] : '';
        $date = (isset($data['date']) && !empty($data['date'])) ? $data['date'] : 'today';
        if (empty($site_key)) {
            $context->reply(['status' => 300, 'msg' => '请选择站点']);

            return;
        }

        $sqls = 'SELECT model_key,game_key FROM site_game WHERE site_key=:site_key';
        $game_list = iterator_to_array($mysqlAdmin->query($sqls, [':site_key' => $site_key]));
        //查询字段

        if ($date == 'today') {
            //今天
            $day = intval(date('Ymd', strtotime('today')));
            $sql = 'SELECT game_key,bet_count,bet_amount,bonus_amount,profit_amount FROM daily_site_lottery WHERE site_key=:site_key AND daily=:daily';
            $param = [':site_key' => $site_key, ':daily' => $day];
        } elseif ($date == 'yesterday') {
            //昨天
            $day = intval(date('Ymd', strtotime('yesterday')));
            $sql = 'SELECT game_key,bet_count,bet_amount,bonus_amount,profit_amount FROM daily_site_lottery WHERE site_key=:site_key AND daily=:daily';
            $param = [':site_key' => $site_key, ':daily' => $day];
        } elseif ($date == 'thisweek') {
            //本周
            $day = intval(date('oW', strtotime('today')));
            $sql = 'SELECT game_key,bet_count,bet_amount,bonus_amount,profit_amount FROM weekly_site_lottery WHERE site_key=:site_key AND weekly=:weekly';
            $param = [':site_key' => $site_key, ':weekly' => $day];
        } elseif ($date == 'lastweek') {
            //上周
            $day = intval(date('oW', strtotime('-1 week')));
            $sql = 'SELECT game_key,bet_count,bet_amount,bonus_amount,profit_amount FROM weekly_site_lottery WHERE site_key=:site_key AND weekly=:weekly';
            $param = [':site_key' => $site_key, ':weekly' => $day];
        } elseif ($date == 'thismonth') {
            //本月
            $day = intval(date('Ym', strtotime('today')));
            $sql = 'SELECT game_key,bet_count,bet_amount,bonus_amount,profit_amount FROM monthly_site_lottery WHERE site_key=:site_key AND monthly=:monthly';
            $param = [':site_key' => $site_key, ':monthly' => $day];
        } elseif ($date == 'lastmonth') {
            //上月
            $day = intval(date('Ym', strtotime('last month')));
            $sql = 'SELECT game_key,bet_count,bet_amount,bonus_amount,profit_amount FROM monthly_site_lottery WHERE site_key=:site_key AND monthly=:monthly';
            $param = [':site_key' => $site_key, ':monthly' => $day];
        } else {
            $context->reply(['status' => 204, 'msg' => '报表时间不合法']);

            return;
        }

        $list = [
            'bet_amount' => 0,
            'bet_count' => 0,
            'bonus_amount' => 0,
            'profit_amount' => 0,
        ];

        $lotteryInfo = [];
        foreach ($mysqls->query($sql, $param) as $row) {
            $lotteryInfo[$row['game_key']] = $row;
            $list['bet_amount'] += floatval($row['bet_amount']);
            $list['bet_count'] += intval($row['bet_count']);
            $list['bonus_amount'] += floatval($row['bonus_amount']);
            $list['profit_amount'] += floatval($row['profit_amount']);
        }
        $allList = [];

        foreach ($game_list as $k => $v) {
            $listArr = [
                'bet_amount' => 0,
                'bet_count' => 0,
                'bonus_amount' => 0,
                'profit_amount' => 0,
                'bet_rate' => 0,
                'bet_count_rate' => 0,
            ];
            $gameList['game_key'] = $v['game_key'];
            $gameList['game_name'] = $cache->hget('AllGame', $v['game_key']);
            $gameList['bet_amount'] = empty($lotteryInfo[$v['game_key']]['bet_amount']) ? 0 : floatval($lotteryInfo[$v['game_key']]['bet_amount']);
            $gameList['bet_count'] = empty($lotteryInfo[$v['game_key']]['bet_count']) ? 0 : intval($lotteryInfo[$v['game_key']]['bet_count']);
            $gameList['bonus_amount'] = empty($lotteryInfo[$v['game_key']]['bonus_amount']) ? 0 : floatval($lotteryInfo[$v['game_key']]['bonus_amount']);
            $gameList['profit_amount'] = empty($lotteryInfo[$v['game_key']]['profit_amount']) ? 0 : floatval($lotteryInfo[$v['game_key']]['profit_amount']);
            $gameList['bet_rate'] = empty($gameList['bet_amount']) ? 0 : $gameList['bet_amount'] / $list['bet_amount'];
            $gameList['bet_count_rate'] = empty($gameList['bet_count']) ? 0 : $gameList['bet_count'] / $list['bet_count'];

            $gameList['bet_amount'] = $this->intercept_num($gameList['bet_amount']);
            $gameList['bonus_amount'] = $this->intercept_num($gameList['bonus_amount']);
            $gameList['profit_amount'] = $this->intercept_num($gameList['profit_amount']);
            $gameList['bet_rate'] = sprintf('%.4f', substr($gameList['bet_rate'], 0, strpos($gameList['bet_rate'], '.') + 5));
            $gameList['bet_count_rate'] = sprintf('%.4f', substr($gameList['bet_count_rate'], 0, strpos($gameList['bet_count_rate'], '.') + 5));

            $allList[$v['model_key']]['model_name'] = $cache->hget('Model', $v['model_key']);
            if (!empty($lotteryInfo[$v['game_key']])) {
                $listArr['bet_amount'] += $gameList['bet_amount'];
                $listArr['bet_count'] += $gameList['bet_count'];
                $listArr['bonus_amount'] += $gameList['bonus_amount'];
                $listArr['profit_amount'] += $gameList['profit_amount'];
                $listArr['bet_rate'] = $listArr['bet_amount'] / $list['bet_amount'];
                $listArr['bet_count_rate'] = $listArr['bet_count'] / $list['bet_count'];
                $allList[$v['model_key']] = $listArr;
            }

            $allList[$v['model_key']]['bet_amount'] = empty($allList[$v['model_key']]['bet_amount']) ? 0 : $allList[$v['model_key']]['bet_amount'];
            $allList[$v['model_key']]['bet_count'] = empty($allList[$v['model_key']]['bet_count']) ? 0 : $allList[$v['model_key']]['bet_count'];
            $allList[$v['model_key']]['bonus_amount'] = empty($allList[$v['model_key']]['bonus_amount']) ? 0 : $allList[$v['model_key']]['bonus_amount'];
            $allList[$v['model_key']]['profit_amount'] = empty($allList[$v['model_key']]['profit_amount']) ? 0 : $allList[$v['model_key']]['profit_amount'];
            $allList[$v['model_key']]['bet_rate'] = empty($allList[$v['model_key']]['bet_rate']) ? 0 : $allList[$v['model_key']]['bet_rate'];
            $allList[$v['model_key']]['bet_count_rate'] = empty($allList[$v['model_key']]['bet_count_rate']) ? 0 : $allList[$v['model_key']]['bet_count_rate'];

            $allList[$v['model_key']]['bet_amount'] = $this->intercept_num($allList[$v['model_key']]['bet_amount']);
            $allList[$v['model_key']]['bonus_amount'] = $this->intercept_num($allList[$v['model_key']]['bonus_amount']);
            $allList[$v['model_key']]['profit_amount'] = $this->intercept_num($allList[$v['model_key']]['profit_amount']);
            $allList[$v['model_key']]['bet_rate'] = sprintf('%.4f', substr($allList[$v['model_key']]['bet_rate'], 0, strpos($allList[$v['model_key']]['bet_rate'], '.') + 5));
            $allList[$v['model_key']]['bet_count_rate'] = sprintf('%.4f', substr($allList[$v['model_key']]['bet_count_rate'], 0, strpos($allList[$v['model_key']]['bet_count_rate'], '.') + 5));

            $allList[$v['model_key']]['list'][] = $gameList;
        }

        $list['bet_amount'] = $this->intercept_num($list['bet_amount']);
        $list['bonus_amount'] = $this->intercept_num($list['bonus_amount']);
        $list['profit_amount'] = $this->intercept_num($list['profit_amount']);

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'today' => $list,
            'list' => $allList,
        ]);
    }
}
