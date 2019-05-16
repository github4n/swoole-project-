<?php
/**
 * Created by PhpStorm.
 * User: hutao
 * Date: 19-2-25
 * Time: 下午5:23.
 */

namespace Site\Websocket\AgentRebate\Brokerage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * BrokerageDetails.php.
 *
 * @description   代理返佣--佣金详情
 * @Author  Luis
 * @date  2019-04-07
 * @links  参数：AgentRebate/Brokerage/BrokerageDetails {"daily":"","layer_id":"","user_key":"","deliver":"","brokeragelimit":""}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-09
 */
class BrokerageDetails extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $layer_id = isset($data['layer_id']) ? $data['layer_id'] : '';
        $daily = isset($data['daily']) ? $data['daily'] : '';
        $user_key = isset($data['user_key']) ? $data['user_key'] : '';
        $deliver = isset($data['deliver']) ? $data['deliver'] : '';
        $brokeragelimit = isset($data['brokeragelimit']) ? $data['brokeragelimit'] : '';
        $staffId = $context->getInfo('StaffId');
        $staffGrade = $context->getInfo('StaffGrade');
        $MasterId = $context->getInfo('MasterId');
        if ($MasterId != 0) {
            $staffId = $MasterId;
        }
        $param = [];
        $brokerageDate = 'SELECT user_key,user_id,layer_id,broker_1_bet,broker_1_user,broker_1_rate,brokerage_1,broker_2_bet,broker_2_user,broker_2_rate,brokerage_2,broker_3_bet,broker_3_user,broker_3_rate,brokerage_3,brokerage,cumulate_brokerage,deliver_time,broker_1_bet_user,broker_2_bet_user,broker_3_bet_user From daily_user_brokerage where layer_id = :layer_id and daily = :daily ';
        $param[':layer_id'] = $layer_id;
        $param[':daily'] = $daily;
        switch ($staffGrade) {
            case 0:
                $agent = ' and 1=1 ';
                break;
            case 1:
                $agent = ' and major_id =:staffId ';
                $param[':staffId'] = $staffId;
                break;
            case 2:
                $agent = ' and minor_id = :staffId ';
                $param[':staffId'] = $staffId;
                break;
            case 3:
                $agent = ' and agent_id = :staffId ';
                $param[':staffId'] = $staffId;
                break;
        }
        if (!empty($user_key)) {
            $brokerageDate .= ' AND user_key = :user_key ';
            $param[':user_key'] = $user_key;
        }
        if (!empty($deliver)) {
            switch ($deliver) {
            case 'y':
                $brokerageDate .= ' AND deliver_time > 0 ';
                 break;
            case 'n':
                $brokerageDate .= ' AND deliver_time = 0 ';
                break;
            }
        }
        if (!empty($brokeragelimit)) {
            $brokerageDate .= ' AND brokerage >= :brokeragelimit ';
            $param[':brokeragelimit'] = $brokeragelimit;
        }
        $order = ' ORDER BY daily DESC';
        $brokerageDate = $brokerageDate.$agent.$order;
        $report_mysql = $config->data_report;
        $brokerageDate = iterator_to_array($report_mysql->query($brokerageDate, $param));
        $list = [];
        if (!empty($brokerageDate)) {
            foreach ($brokerageDate as $k => $v) {
                $list[$k]['user_key'] = $v['user_key'];
                $list[$k]['user_id'] = $v['user_id'];
                $list[$k]['broker_1_bet_user'] = $v['broker_1_bet_user'];
                $list[$k]['broker_2_bet_user'] = $v['broker_2_bet_user'];
                $list[$k]['broker_3_bet_user'] = $v['broker_3_bet_user'];
                $list[$k]['layer_id'] = $v['layer_id'];
                $list[$k]['layer_name'] = $context->getInfo($v['layer_id']);
                $list[$k]['broker_1_bet'] = $this->intercept_num($v['broker_1_bet']);
                $list[$k]['broker_1_rate'] = $v['broker_1_rate'];
                $list[$k]['brokerage_1'] = $v['brokerage_1'];
                $list[$k]['broker_2_bet'] = $this->intercept_num($v['broker_2_bet']);
                $list[$k]['broker_2_rate'] = $v['broker_2_rate'];
                $list[$k]['brokerage_2'] = $v['brokerage_2'];
                $list[$k]['broker_3_bet'] = $this->intercept_num($v['broker_3_bet']);
                $list[$k]['broker_3_rate'] = $v['broker_3_rate'];
                $list[$k]['brokerage_3'] = $v['brokerage_3'];
                $list[$k]['brokerage'] = $v['brokerage'];
                $list[$k]['cumulate_brokerage'] = $this->intercept_num($v['cumulate_brokerage']);
                $list[$k]['deliver_time'] = date('Y-m-d H:i:s', $v['deliver_time']);
                if ($v['deliver_time'] == 0) {
                    $list[$k]['deliver_time'] = $v['deliver_time'];
                } else {
                    $list[$k]['deliver_time'] = date('Y-m-d H:i:s', $v['deliver_time']);
                }
                if ($v['deliver_time'] > 0) {
                    $list[$k]['deliver'] = '1';
                }
                if ($v['deliver_time'] == 0) {
                    $list[$k]['deliver'] = '0';
                }
            }
        }

        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $list]);
    }
}
