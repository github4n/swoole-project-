<?php

namespace Plat\Websocket\LotteryTicket\LotteryBetSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * BetSetting class.
 *
 * @description   获取投注额设置列表
 * @Author  avery
 * @date  2019-04-23
 * @links  LotteryTicket/LotteryBetSetting/BetSetting {"site_name":"","status":""}     0-开放 1-停止交易 2-关闭前台 3-关闭前后
 * @modifyAuthor   avery
 * @modifyTime  2019-05-09
 */
class BetSetting extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('lottery_bet_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $mysqlAdmin = $config->data_admin;
        $mysqlAnalysis = $config->data_analysis;
        $monthly = intval(date('Ym', strtotime('today')));
        $site_key = isset($data['site_name']) ? $data['site_name'] : '';
        $status = isset($data['status']) ? $data['status'] : '';

        if (is_numeric($status)) {
            if ($status >= 4 || $status < 0) {
                $context->reply(['status' => 300, 'msg' => '站点状态不正确']);

                return;
            }
        }
        $param = [];
        if (!empty($site_key) && !empty($status)) {
            $sql = 'SELECT site_key,site_name from site where site_name=:site_name and status=:status';
            $param = [':site_name' => $site_key, ':status' => $status];
        } elseif (!empty($site_key)) {
            $sql = 'SELECT site_key,site_name from site where site_name=:site_name';
            $param = [':site_name' => $site_key];
        } elseif (is_numeric($status)) {
            $sql = 'SELECT site_key,site_name from site where status=:status';
            $param = [':status' => $status];
        } else {
            $sql = 'SELECT site_key,site_name from site';
        }

        $siteList = iterator_to_array($mysqlAdmin->query($sql, $param));
        $site_list = [];
        if (!empty($siteList)) {
            foreach ($siteList as $key => $val) {
                $info = [];
                $sql = 'SELECT bet_all,bonus_all from monthly_site where site_key=:site_key and monthly=:monthly';
                $param = [':site_key' => $val['site_key'], ':monthly' => $monthly];
                foreach ($mysqlAnalysis->query($sql, $param) as $row) {
                    $info = $row;
                }
                $site_list[$key]['site_key'] = $val['site_key'];
                $site_list[$key]['site_name'] = $val['site_name'];
                $site_list[$key]['bet_all'] = empty($info['bet_all']) ? '0' : $info['bet_all'];
                $site_list[$key]['bonus_all'] = empty($info['bonus_all']) ? '0' : $info['bonus_all'];
                $site_list[$key]['profit_all'] = empty($info['bet_all'] - $info['bonus_all']) ? '0' : $info['bet_all'] - $info['bonus_all'];

                $site_list[$key]['bet_all'] = $this->intercept_num($site_list[$key]['bet_all']);
                $site_list[$key]['bonus_all'] = $this->intercept_num($site_list[$key]['bonus_all']);
                $site_list[$key]['profit_all'] = $this->intercept_num($site_list[$key]['profit_all']);
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取列表成功', 'list' => $site_list]);
    }
}
