<?php

namespace Site\Websocket\AgentRebate\Brokerage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * BrokerageTwo.php.
 *
 * @description   代理返佣--二级下线详情
 * @Author  Luis
 * @date  2019-04-07
 * @links  参数：AgentRebate/Brokerage/BrokerageTwo {"broker_id":1,"time":"20190124"}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
class BrokerageTwo extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $broker = $data['broker_id'];
        $daily = $data['time'];
        if (!is_numeric($broker)) {
            $context->reply(['status' => 204, 'msg' => '参数错误']);

            return;
        }

        $report_mysql = $config->data_report;
        $sql = 'select user_key,wager_amount from daily_user where broker_2_id = :broker_2_id and daily=:daily and bet_amount >0';
        $userDate = iterator_to_array($report_mysql->query($sql, [':broker_2_id' => $broker, ':daily' => $daily]));
        $brokersql = 'select broker_2_rate from daily_user_brokerage where user_id = :broker and daily = :daily';
        $brokerDate = iterator_to_array($report_mysql->query($brokersql, [':broker' => $broker, ':daily' => $daily]));
        $list = [];
        if (!empty($userDate) && !empty($brokerDate)) {
            foreach ($userDate as $k => $v) {
                $list[$k]['user_key'] = $v['user_key'];
                $list[$k]['bet_amount'] = $this->intercept_num($v['wager_amount']);
                $list[$k]['broker_2_rate'] = $brokerDate[0]['broker_2_rate'];
                $list[$k]['brokerage'] = $this->intercept_num($list[$k]['bet_amount'] * $list[$k]['broker_2_rate'] * 0.01);
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $list]);
    }
}
