<?php

namespace Plat\Websocket\Website\GameCommission;

use Lib\Config;
use Lib\Websocket\Context;
use Plat\Websocket\CheckLogin;

/**
 * CommissionList class.
 *
 * @description   游戏提成比例设置
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/GameCommission/CommissionList {"site_key":"site1"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class CommissionList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_tax_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysqlAdmin = $config->data_admin;
        $site_key = $data['site_key'];
        if (empty($site_key)) {
            $context->reply(['status' => 205, 'msg' => '请选择站点']);

            return;
        }
        //服务费
        $sql = 'select month_rent from site_rent_config where site_key=:site_key';
        $rent = 0;
        foreach ($mysqlAdmin->query($sql, [':site_key' => $site_key]) as $row) {
            $rent = $row['month_rent'];
        }
        //抽成比例
        $sql = 'select range_max,tax_rate,category from site_tax_config where site_key=:site_key';
        $list = iterator_to_array($mysqlAdmin->query($sql, [':site_key' => $site_key]));
        $tax_list = [];
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $tax_list[$val['category']][] = [
                    'range_max' => $val['range_max'],
                    'tax_rate' => $val['tax_rate'],
                ];
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'rent' => $rent, 'list' => $tax_list]);
    }
}
