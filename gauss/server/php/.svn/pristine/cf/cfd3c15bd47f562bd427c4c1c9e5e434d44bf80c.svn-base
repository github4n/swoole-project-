<?php

namespace Plat\Websocket\PayType;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * PayTypeList class.
 *
 * @description   description
 * @Author  avery
 * @date  2019-04-23
 * @links  url
 *
 * 参数：gate_key:支付方式的关键字
 * 状态码：
 * 200：获取成功
 * 201：支付方式的关键字类型不正确
 * 202：支付方式的关键字不存在
 * 400：获取失败
 *
 * @modifyAuthor   blake
 * @modifyTime  2019-04-23
 */
class PayTypeList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('cash_list', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $gate_key = trim($data['gate_key']) ?: 'beikefu';
        if (empty($gate_key)) {
            $context->reply(['status' => 201, 'msg' => '请选择支付列表关键字']);

            return;
        }
        $mysql = $config->data_public;
        $sql = 'SELECT way_name FROM deposit_gate_way WHERE gate_key=:gate_key ORDER BY display_order ASC';
        $params = [':gate_key' => $gate_key];
        $paytypelist = [];
        $paylist = [];
        foreach ($mysql->query($sql, $params) as $row) {
            $paytypelist[] = $row;
        }
        $sql = 'SELECT * FROM deposit_gate';
        foreach ($mysql->query($sql, $params) as $row) {
            $paylist[] = $row;
        }
        $context->reply([
           'status' => 200,
           'msg' => '获取成功',
           'paylist' => $paylist,
           'paytypelist' => $paytypelist,
       ]);
    }
}
