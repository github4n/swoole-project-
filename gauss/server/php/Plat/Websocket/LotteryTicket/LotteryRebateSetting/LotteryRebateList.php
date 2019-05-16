<?php

namespace Plat\Websocket\LotteryTicket\LotteryRebateSetting;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * LotteryRebateList.
 *
 * @description   彩票返点设置
 * @Author  avery
 * @date  2019-04-08
 * @links  LotteryTicket/LotteryRebateSetting/LotteryRebateList {"site_name":"测试站点A","status":1}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class LotteryRebateList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('lottery_rebate_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        //接收数据
        $data = $context->getData();
        $site_name = empty($data['site_name']) ? '' : $data['site_name'];
        $status = empty($data['status']) ? '' : $data['status'];
        $mysqlAdmin = $config->data_admin;
        $mysqlAnalysis = $config->data_analysis;
        $monthly = intval(date('Ym', strtotime('today')));

        if (is_numeric($status)) {
            if ($status >= 4 || $status < 0) {
                $context->reply(['status' => 300, 'msg' => '站点状态不正确']);

                return;
            }
        }

        if (!empty($site_name) && is_numeric($status)) {
            $sql = 'SELECT site_key,site_name from site where 1=1 AND site_name=:site_name AND status=:status';
            $param = [':site_name' => $site_name, ':status' => $status];
        } elseif (!empty($site_name)) {
            $sql = 'SELECT site_key,site_name from site where 1=1 AND site_name=:site_name';
            $param = [':site_name' => $site_name];
        } elseif (is_numeric($status)) {
            $sql = 'SELECT site_key,site_name from site where 1=1 AND status=:status';
            $param = [':status' => $status];
        } else {
            $sql = 'SELECT site_key,site_name from site where 1=1';
            $param = [];
        }

        $list = [];
        $siteList = iterator_to_array($mysqlAdmin->query($sql, $param));
        foreach ($siteList as $k => $val) {
            $sql = 'SELECT site_key,site_name,bet_all,bonus_all,(bet_all-bonus_all) as profit,rebate FROM monthly_site WHERE site_key=:site_key and monthly=:monthly';
            $param = [':site_key' => $val['site_key'], ':monthly' => $monthly];
            foreach ($mysqlAnalysis->query($sql, $param) as $row) {
                $info = $row;
            }
            $list[$k]['site_key'] = $val['site_key'];
            $list[$k]['site_name'] = $val['site_name'];
            $list[$k]['bet_all'] = empty($info['bet_all']) ? 0 : $info['bet_all'];
            $list[$k]['bonus_all'] = empty($info['bonus_all']) ? 0 : $info['bonus_all'];
            $list[$k]['profit'] = empty($info['profit']) ? 0 : $info['profit'];
            $list[$k]['rebate'] = empty($info['rebate']) ? 0 : $info['rebate'];

            $list[$k]['bet_all'] = $this->intercept_num($list[$k]['bet_all']);
            $list[$k]['bonus_all'] = $this->intercept_num($list[$k]['bonus_all']);
            $list[$k]['profit'] = $this->intercept_num($list[$k]['profit']);
            $list[$k]['rebate'] = $this->intercept_num($list[$k]['rebate']);
        }

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $list,
        ]);
    }
}
