<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-支付平台列表
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/DepositAccount/DepositGateWay
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositGateWay extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }

        $cache = $config->cache_site;
        $gate_list = json_decode($cache->hget('PayWayList', 'payGateList'));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $gate_list]);
    }
}
