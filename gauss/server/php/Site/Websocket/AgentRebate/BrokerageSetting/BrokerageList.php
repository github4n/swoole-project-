<?php

namespace Site\Websocket\AgentRebate\BrokerageSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * BrokerageList.php.
 *
 * @description   代理返佣--佣金设定列表
 * @Author  Luis
 * @date  2019-04-07
 * @links  参数：AgentRebate/BrokerageSetting/BrokerageList {"layer_id":0}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-10
 */
class BrokerageList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_setting', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $masterId = $context->getInfo('MasterId');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $mysqlStaff = $config->data_staff;
        $layer_id = isset($data['layer_id']) ? $data['layer_id'] : '';
        if (!empty($layer_id)) {
            if (!is_numeric($layer_id)) {
                $context->reply(['status' => 230, 'msg' => '选择的层级类型不正确']);

                return;
            }
        }
        if (empty($layer_id)) {
            $all_list = [];
            if ($masterId == 0) {
                $sql = 'SELECT * FROM brokerage_setting';
                $param = [];
            } else {
                $layer_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                $layer_lists = [];
                foreach ($mysqlStaff->query($layer_sql, [':staff_id' => $staffId]) as $row) {
                    $layer_lists[] = $row['layer_id'];
                }
                $sql = 'SELECT * FROM brokerage_setting where layer_id in :layer_list';
                $param = [':layer_list' => $layer_lists];
            }

            $lists = iterator_to_array($mysql->query($sql, $param));

            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    $list = [];
                    $sql = 'SELECT * FROM brokerage_rate WHERE layer_id=:layer_id';
                    $param = [':layer_id' => $val['layer_id']];
                    $rate_list = iterator_to_array($mysql->query($sql, $param));
                    if (!empty($rate_list)) {
                        foreach ($rate_list as $k => $v) {
                            $list[$k]['auto_deliver'] = '1';
                            $list[$k]['min_bet_amount'] = $val['min_bet_amount'];
                            $list[$k]['min_deposit'] = $val['min_deposit'];
                            $list[$k]['layer_id'] = $val['layer_id'];
                            $list[$k]['layer_name'] = $context->getInfo($val['layer_id']);
                            if ($val['auto_deliver'] == 0 || !empty($val['deliver_time'])) {
                                $list[$k]['auto_deliver'] = '0';
                            }
                            if (($val['auto_deliver'] == 1 && empty($val['deliver_time']))) {
                                $list[$k]['auto_deliver'] = '1';
                            }
                            $list[$k]['deliver_time'] = $val['deliver_time'];
                            if (strlen($val['deliver_time']) == 3) {
                                $list[$k]['deliver_time'] = substr_replace($val['deliver_time'], ':', 1, 0);
                            }
                            if (strlen($val['deliver_time']) == 4) {
                                $list[$k]['deliver_time'] = substr_replace($val['deliver_time'], ':', 2, 0);
                            }
                            if (strlen($val['deliver_time']) == 1) {
                                $time = '000';
                                $time .= $val['deliver_time'];
                                $list[$k]['deliver_time'] = substr_replace($time, ':', 2, 0);
                            }
                            if (strlen($val['deliver_time']) == 2) {
                                $time = '00';
                                $time .= $val['deliver_time'];
                                $list[$k]['deliver_time'] = substr_replace($time, ':', 2, 0);
                            }
                            $list[$k]['vigor_count'] = $v['vigor_count'];
                            $list[$k]['broker_1_rate'] = $v['broker_1_rate'];
                            $list[$k]['broker_2_rate'] = $v['broker_2_rate'];
                            $list[$k]['broker_3_rate'] = $v['broker_3_rate'];
                        }
                    }
                    $all_list[] = $list;
                }
            }
        } else {
            $all_list = [];
            $sql = 'SELECT * FROM brokerage_setting where layer_id = :layer_id';
            $lists = iterator_to_array($mysql->query($sql, [':layer_id' => $layer_id]));

            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    $list = [];
                    $sql = 'SELECT * FROM brokerage_rate WHERE layer_id=:layer_id';
                    $param = [':layer_id' => $layer_id];
                    $rate_list = iterator_to_array($mysql->query($sql, $param));
                    if (!empty($rate_list)) {
                        foreach ($rate_list as $k => $v) {
                            $list[$k]['min_bet_amount'] = $val['min_bet_amount'];
                            $list[$k]['min_deposit'] = $val['min_deposit'];
                            $list[$k]['layer_id'] = $val['layer_id'];
                            $list[$k]['layer_name'] = $context->getInfo($val['layer_id']);
                            if ($val['auto_deliver'] == 0 || !empty($val['deliver_time'])) {
                                $list[$k]['auto_deliver'] = '1';
                            }
                            if (($val['auto_deliver'] == 1 && empty(empty($val['deliver_time']))) || ($val['auto_deliver'] == 0 && empty(empty($val['deliver_time'])))) {
                                $list[$k]['auto_deliver'] = '0';
                            }
                            $list[$k]['deliver_time'] = $val['deliver_time'];
                            if (strlen($val['deliver_time']) == 3) {
                                $list[$k]['deliver_time'] = substr_replace($val['deliver_time'], ':', 1, 0);
                            }
                            if (strlen($val['deliver_time']) == 4) {
                                $list[$k]['deliver_time'] = substr_replace($val['deliver_time'], ':', 2, 0);
                            }
                            if (strlen($val['deliver_time']) == 1) {
                                $time = '000';
                                $time .= $val['deliver_time'];
                                $list[$k]['deliver_time'] = substr_replace($time, ':', 2, 0);
                            }
                            if (strlen($val['deliver_time']) == 2) {
                                $time = '00';
                                $time .= $val['deliver_time'];
                                $list[$k]['deliver_time'] = substr_replace($time, ':', 2, 0);
                            }
                            $list[$k]['vigor_count'] = $v['vigor_count'];
                            $list[$k]['broker_1_rate'] = $v['broker_1_rate'];
                            $list[$k]['broker_2_rate'] = $v['broker_2_rate'];
                            $list[$k]['broker_3_rate'] = $v['broker_3_rate'];
                        }
                    }
                    $all_list[] = $list;
                }
            }
        }

        //获取代理层级

        if ($masterId == 0) {
            $sql = 'select layer_id,layer_name from layer_info where layer_type>100';
            $param = [];
        } else {
            $layer_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
            $layer_lists = [];
            foreach ($mysqlStaff->query($layer_sql, [':staff_id' => $staffId]) as $row) {
                $layer_lists[] = $row['layer_id'];
            }
            $sql = 'select layer_id,layer_name from layer_info where layer_id in :layer_list and layer_type>100';
            $param = [':layer_list' => $layer_lists];
        }
        $layer_list = iterator_to_array($mysql->query($sql, $param));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $all_list, 'layer_list' => $layer_list]);
    }
}
