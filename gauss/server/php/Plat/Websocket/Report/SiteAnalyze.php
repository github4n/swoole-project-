<?php

namespace Plat\Websocket\Report;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * SiteAnalyze class.
 *
 * @description   站点分析
 * @Author  rose
 * @date  2019-04-11
 * @links  Report/SiteAnalyze {"site_key":"site1","date":"day"}
 *
 * 参数：site_key:站点key值,date:(day:日,week:周,month:月)
 * 按日:条形图数据返回站点从当前统计到之前10天的每天的总投注和损益
 * 按周：条形图数据返回站点从当前周统计到前10周每周的总投注和损益
 * 按月：条形图数据返回站点从当前月开始统计到前10个月的每月的总投注和损益
 *
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 *
 * @modifyAuthor   blake
 * @modifyTime  2019-05-14
 */
class SiteAnalyze extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('report_analysis', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $mysql = $config->data_analysis;
        //获取站点信息
        $data = $context->getData();
        $site_key = isset($data['site_key']) ? $data['site_key'] : '';
        $date = $data['date'] ?: 'day';
        if (empty($site_key)) {
            $context->reply(['status' => 300, 'msg' => '请选择站点']);

            return;
        }
        if ($date == 'day') {
            $day = intval(date('Ymd', time()));
            //列表的
            $sqls = 'SELECT daily,bet_all,user_register,profit_all as profit_all FROM daily_site WHERE site_key=:site_key AND daily <= :daily Order by daily DESC limit 10';
            $param = [':site_key' => $site_key, ':daily' => $day];
        } elseif ($date == 'week') {
            $week = intval(date('oW', time()));
            $sqls = 'SELECT user_register,weekly,bet_all,profit_all as profit_all FROM weekly_site WHERE site_key=:site_key AND weekly <= :weekly Order by weekly DESC limit 10';
            $param = [':site_key' => $site_key, ':weekly' => $week];
        } elseif ($date == 'month') {
            $month = intval(date('Ym', time()));
            $sqls = 'SELECT user_register,monthly,bet_all,profit_all as profit_all FROM monthly_site WHERE site_key=:site_key AND monthly <= :monthly Order by monthly DESC limit 10';
            $param = [':site_key' => $site_key, ':monthly' => $month];
        } else {
            $context->reply(['status' => 204, 'msg' => '日期参数错误']);

            return;
        }

        $lists = array();
        $list = array();
        foreach ($mysql->query($sqls, $param) as $rows) {
            $lists[] = $rows;
        }
        $list['bet_all'] = $this->intercept_num($lists[0]['bet_all']);
        $list['profit_all'] = $this->intercept_num($lists[0]['profit_all']);
        $list['user_register'] = $lists[0]['user_register'];

        $months = ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'];

        $key_val = array();
        foreach ($lists as $key => $val) {
            unset($lists[$key]['user_register']);
            if ($date == 'day') {
                $key_val[] = $val['daily'];
                $lists[$key]['daily'] = date('m-d', strtotime($val['daily']));
                unset($list['daily']);
            } elseif ($date == 'week') {
                $key_val[] = $val['weekly'];
                $str_weekly = substr_replace($val['weekly'], '-W', 4, 0).'-7';
                $lists[$key]['weekly'] = date('m-d', strtotime($str_weekly));
                unset($list['weekly']);
            } elseif ($date == 'month') {
                $key_val[] = $val['monthly'];
                $month_key = intval(substr($val['monthly'], 4));
                $lists[$key]['monthly'] = $months[$month_key - 1];
                unset($list['monthly']);
            }
        }
        // 排序
        array_multisort($key_val, SORT_ASC, $lists);

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'info' => $list,
            'list' => $lists,
        ]);
    }
}
