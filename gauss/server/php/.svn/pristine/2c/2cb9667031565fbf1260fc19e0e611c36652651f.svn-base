<?php

namespace Plat\Websocket\LotteryTicket\Setting;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * Undocumented class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-18
 * @links  LotteryTicket/Setting/LotteryRebateList
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
        $site_key = empty($data['site_key']) ? null : $data['site_key'];
        $status = empty($data['status']) ? null : $data['status'];

        $analysis_mysql = $config->data_analysis;
        $sql = 'SELECT site_key,site_name,bet_all,bonus_all,(bonus_all-bet_all) as profit,rebate FROM monthly_site WHERE 1';
        $param = [];
        if (!empty($site_key)) {
            $sql = 'SELECT site_key,site_name,bet_all,bonus_all,(bonus_all-bet_all) as profit,rebate FROM monthly_site WHERE site_key=:site_key';
            $param = [':site_key' => $site_key];
        }

        $items = iterator_to_array($analysis_mysql->query($sql, $param));
        $list = [];
        foreach ($items as $k => $v) {
            $list[$k]['site_key'] = $v['site_key'];
            $list[$k]['site_name'] = $v['site_name'];
            $list[$k]['bet_all'] = $v['bet_all'];
            $list[$k]['bonus_all'] = $v['bonus_all'];
            $list[$k]['profit'] = $v['profit'];
            $list[$k]['rebate'] = $v['rebate'];
        }

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $list,
        ]);
    }
}
