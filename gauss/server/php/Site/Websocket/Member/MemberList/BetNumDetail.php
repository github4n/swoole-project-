<?php

namespace Site\Websocket\Member\MemberList;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * Undocumented class.
 *
 * @description   会员管理-会员列表-佣金详情-投注人数详情
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/MemberList/BetNumDetail {"user_key":"user001","level":"broker_1"}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class BetNumDetail extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $broker_key = $data['user_key'];
        $level = $data['level'];
        if (empty($broker_key)) {
            $context->reply(['status' => 201, 'msg' => 'user_key为空']);

            return;
        }
        if (empty($level)) {
            $context->reply(['status' => 201, 'msg' => '层级为空']);

            return;
        }
        $mysql = $config->data_report;
        $broker_1_rate = $context->getInfo('broker_1_rate');
        $broker_2_rate = $context->getInfo('broker_2_rate');
        $broker_3_rate = $context->getInfo('broker_3_rate');
        $brokerList = array();
        switch ($level) {
            case 'broker_1':
                //broker_1
                $sql = 'SELECT user_key,bet_amount FROM daily_user WHERE broker_1_key=:broker_key';
                $param = [':broker_key' => $broker_key];
                $brokerList = array();
                foreach ($mysql->query($sql, $param) as $row) {
                    $list[] = $row;
                }
                if (!empty($list)) {
                    foreach ($list as $key => $val) {
                        $brokerList[$key]['user_key'] = $val['user_key'];
                        $brokerList[$key]['bet_amount'] = $val['bet_amount'];
                        $brokerList[$key]['broker_1_rate'] = $broker_1_rate;
                        $brokerList[$key]['broker_1'] = $val['bet_all'] * $brokerList[$key]['broker_1_rate'];
                    }
                }
                break;
            case 'broker_2':
                //$broker_2
                $sql = 'SELECT user_key,bet_amount FROM daily_user WHERE broker_2_key=:broker_key';
                $param = [':broker_key' => $broker_key];
                $brokerList = array();
                foreach ($mysql->query($sql, $param) as $row) {
                    $list2[] = $row;
                }
                if (!empty($list2)) {
                    foreach ($list2 as $key => $val) {
                        $brokerList[$key]['user_key'] = $val['user_key'];
                        $brokerList[$key]['bet_amount'] = $val['bet_amount'];
                        $brokerList[$key]['broker_2_rate'] = $broker_2_rate;
                        $brokerList[$key]['broker_2'] = $val['bet_amount'] * $brokerList[$key]['broker_1_rate'];
                    }
                }
                break;
            case 'broker_3':
                //$broker_3
                $sql = 'SELECT user_key,bet_amount FROM daily_user WHERE broker_3_key=:broker_key';
                $param = [':broker_key' => $broker_key];

                foreach ($mysql->query($sql, $param) as $row) {
                    $list[] = $row;
                }
                if (!empty($list)) {
                    foreach ($list as $key => $val) {
                        $brokerList[$key]['user_key'] = $val['user_key'];
                        $brokerList[$key]['bet_amount'] = $val['bet_amount'];
                        $brokerList[$key]['broker_3_rate'] = $broker_3_rate;
                        $brokerList[$key]['broker_3'] = $val['bet_amount'] * $brokerList[$key]['broker_1_rate'];
                    }
                }
                break;
        }

        $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => $brokerList]);
    }
}
