<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * @description   现金系统-支付管理-第三方入款通道列表
 * @Author  Rose
 * @date  2019-05-08
 * @links  Cash/PayManage/PayGateList
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class PayGateList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_deposit_route', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $mysql = $config->data_staff;
        $acceptable = isset($data['acceptable']) ? $data['acceptable'] : '';
        $sql = 'SELECT route_id,passage_name,min_money,max_money,account_number,passage_acceptable,acceptable,way_name,layer_id_list FROM deposit_route_gateway_intact';
        $total_sql = 'SELECT route_id FROM deposit_route_gateway_intact';
        if (!empty($acceptable)) {
            if ($acceptable == 1) {
                $sql .= ' WHERE acceptable = 1 AND passage_acceptable =1 ';
                $total_sql .= ' WHERE acceptable = 1 AND passage_acceptable =1 ';
            } elseif ($acceptable == 2) {
                $sql .= ' WHERE acceptable = 0 OR passage_acceptable = 0';
                $total_sql .= ' WHERE acceptable = 0 OR passage_acceptable = 0';
            } else {
                $context->reply(['status' => 204, 'msg' => '状态参数错误']);

                return;
            }
        }
        $sql .= ' order by route_id desc limit 1000';

        $list = iterator_to_array($mysql->query($sql));
        $total = $mysql->execute($total_sql);
        $lists = [];
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $lists[$key]['route_id'] = $val['route_id'];
                $lists[$key]['account_number'] = $val['account_number'];
                $lists[$key]['passage_name'] = $val['passage_name'];
                $lists[$key]['min_money'] = $val['min_money'];
                $lists[$key]['max_money'] = $val['max_money'];
                $layer = [];
                $layer_list = explode(',', $val['layer_id_list']);
                foreach ($layer_list as $item) {
                    if (!empty($context->getInfo($item))) {
                        $layer[] = $context->getInfo($item);
                    }
                }
                $lists[$key]['layer'] = $layer;

                $lists[$key]['way_name'] = $val['way_name'];
                if ($val['acceptable'] != 1 || $val['passage_acceptable'] != 1) {
                    $lists[$key]['acceptable'] = 0;
                } else {
                    $lists[$key]['acceptable'] = 1;
                }
            }
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_route',
            ':detail' => '查看三方入款支付线路列表',
        ];
        $mysql->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'total' => $total,
            'list' => $lists,
        ]);
    }
}
