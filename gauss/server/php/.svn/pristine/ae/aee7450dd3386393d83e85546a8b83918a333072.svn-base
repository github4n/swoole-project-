<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-支付管理-银行卡入款通道列表
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/PayManage/PayBankList {}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class PayBankList extends CheckLogin
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

        $acceptable = isset($data['acceptable']) && !empty($data['acceptable']) ? $data['acceptable'] : '';
        $sql = 'SELECT * FROM deposit_route_bank_intact';
        $total_sql = 'SELECT route_id FROM deposit_route_bank_intact';
        if ($acceptable) {
            if ($acceptable == 1) {
                $sql .= ' WHERE acceptable = 1 and passage_acceptable =1';
                $total_sql .= ' WHERE acceptable = 1 and passage_acceptable =1';
            } elseif ($acceptable == 2) {
                $sql .= ' WHERE acceptable = 0 or passage_acceptable = 0';
                $total_sql .= ' WHERE acceptable = 0 or passage_acceptable = 0';
            } else {
                $context->reply(['status' => 204, 'msg' => '状态参数错误']);

                return;
            }
        }
        $sql .= ' order by route_id desc limit 1000';
        $list = iterator_to_array($mysql->query($sql));
        $total = $mysql->execute($total_sql);
        $bank_list = [];
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $layer = [];
                $layer_list = explode(',', $val['layer_id_list']);
                foreach ($layer_list as $item) {
                    if (!empty($context->getInfo($item))) {
                        $layer[] = $context->getInfo($item);
                    }
                }
                $bank_list[$key]['route_id'] = $val['route_id'];
                $bank_list[$key]['passage_name'] = $val['passage_name'];
                $bank_list[$key]['min_money'] = $val['min_money'];
                $bank_list[$key]['max_money'] = $val['max_money'];
                $bank_list[$key]['bank_name'] = $val['bank_name'];
                $bank_list[$key]['bank_branch'] = $val['bank_branch'];
                $bank_list[$key]['account_number'] = $val['account_number'];
                $bank_list[$key]['account_name'] = $val['account_name'];
                if ($val['acceptable'] == 1 && $val['passage_acceptable'] == 1) {
                    $bank_list[$key]['acceptable'] = 1;
                } else {
                    $bank_list[$key]['acceptable'] = 0;
                }
                $bank_list[$key]['layer'] = $layer;
                $bank_list[$key]['coupon_rate'] = $val['coupon_rate'];
                $bank_list[$key]['coupon_max'] = $val['coupon_max'];
                $bank_list[$key]['coupon_times'] = $val['coupon_times'];
                $bank_list[$key]['coupon_audit_rate'] = $val['coupon_audit_rate'];
            }
        }
        sort($list, 1);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_route',
            ':detail' => '查看银行转账支付线路',
        ];
        $mysql->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'total' => $total,
            'list' => $bank_list,
        ]);
    }
}
