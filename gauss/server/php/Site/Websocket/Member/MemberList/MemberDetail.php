<?php

namespace Site\Websocket\Member\MemberList;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberDetail class.
 *
 * @description   会员管理-会员列表-会员详细信息
 * @Author  blake
 * @date  2019-05-08
 * @links   Member/MemberList/MemberDetail {"user_id":1}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-10
 */
class MemberDetail extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $user_id = isset($data['user_id']) ? $data['user_id'] : '';
        $mysql = $config->data_user;
        $mysqlStaff = $config->data_staff;
        $sql = 'SELECT user_key,deal_key,account_name,layer_id,layer_name,broker_1_key,broker_2_key,broker_3_key,memo FROM user_info_intact WHERE user_id = :user_id';
        $info = array();
        $param = [':user_id' => $user_id];
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取数据失败']);
            throw new \PDOException($e);
        }

        //取layer列表
        $layer_id = isset($info['layer_id']) ? $info['layer_id'] : '';
        $layer_type_sql = 'select layer_type from layer_info where layer_id = :layer_id';
        $layer_type = '';
        foreach ($mysql->query($layer_type_sql, [':layer_id' => $layer_id]) as $v) {
            $layer_type = $v['layer_type'];
        }

        $masterId = $context->getInfo('MasterId');
        $staffId = $context->getInfo('StaffId');
        if ($masterId == 0) {
            if ($layer_type > 100) {
                $layer_list_sql = 'select layer_id,layer_name from layer_info where layer_type > 100';
            } else {
                $layer_list_sql = 'select layer_id,layer_name from layer_info where layer_type < 100';
                if (empty($layer_type) || empty($layer_id)) {
                    $layer_list_sql = 'select layer_id,layer_name from layer_info';
                }
            }
            $layer_list = [];
            foreach ($mysql->query($layer_list_sql) as $layer) {
                $layer_list[] = $layer;
            }
        } else {
            $sql = 'select layer_id from staff_layer where staff_id=:staff_id';
            $layer_lists = [];
            foreach ($mysqlStaff->query($sql, [':staff_id' => $staffId]) as $row) {
                $layer_lists[] = $row['layer_id'];
            }
            if ($layer_type > 100) {
                $layer_list_sql = 'select layer_id,layer_name from layer_info where layer_type > 100 and layer_id in :layer_list';
            } else {
                $layer_list_sql = 'select layer_id,layer_name from layer_info where layer_type < 100 and layer_id in :layer_list';
                if (empty($layer_type) || empty($layer_id)) {
                    $layer_list_sql = 'select layer_id,layer_name from layer_info';
                }
            }
            $layer_list = [];
            foreach ($mysql->query($layer_list_sql, [':layer_list' => $layer_lists]) as $layer) {
                $layer_list[] = $layer;
            }
        }

        //获取最近的一次入款信息和出款信息
        $deal_key = $info['deal_key'];
        $dealMysql = $config->__get('data_'.$deal_key);
        $deposit_ql = "select vary_money,deal_type,deal_time,summary from deal where user_id = :user_id and (deal_type = 'deposit_finish' or deal_type = 'staff_deposit') order by deal_time desc limit 1";
        $withdraw_sql = "select vary_money,deal_type,deal_time from deal where user_id = :user_id and (deal_type = 'withdraw_finish' or deal_type = 'staff_withdraw') order by deal_time desc limit 1";
        $depositMoney = '';
        $depositType = '';
        $depositTime = '';
        $depositTypes = '';
        $summary = '';
        foreach ($dealMysql->query($deposit_ql, $param) as $val) {
            $depositMoney = $val['vary_money'];
            $depositTime = empty($val['deal_time']) ? '' : date('Y-m-d H:i:s', $val['deal_time']);
            $depositType = $val['deal_type'];
            $summary = json_decode($val['summary'], true);
        }

        if ($depositType == 'deposit_finish' && isset($summary['bank'])) {
            $depositTypes = '银行卡入款';
        }
        if ($depositType == 'deposit_finish' && isset($summary['gate_name'])) {
            $depositTypes = '三方入款';
        }
        if ($depositType == 'staff_deposit') {
            $depositTypes = '人工入款';
        }

        $withdrawType = '';
        $withdrawMoney = '';
        $withdrawTime = '';
        foreach ($dealMysql->query($withdraw_sql, $param) as $item) {
            $withdrawType = $item['deal_type'];
            $withdrawMoney = $item['vary_money'];
            $withdrawTime = $item['deal_time'];
        }

        if ($withdrawType == 'withdraw_finish') {
            $withdrawType = '会员提出';
        }
        if ($withdrawType == 'staff_withdraw') {
            $withdrawType = '人工提出';
        }

        //获取银行卡号
        $bank_info = 'SELECT bank_name,bank_branch,account_number FROM bank_info WHERE user_id=:user_id';
        foreach ($mysql->query($bank_info, $param) as $rows) {
            $bank_name = $rows['bank_name'];
            $bank_branch = $rows['bank_branch'];
            $account_number = $rows['account_number'];
        }
        //获取入款优惠信息
        $sql = 'select count(deposit_serial) as coupon_num,sum(vary_coupon_audit) as coupon_money from deposit_bank_intact where vary_coupon_audit>0 and user_id=:user_id';
        foreach ($dealMysql->query($sql, $param) as $row) {
            $coupon = $row;
        }

        //获取代理线的基本信息
        $user_info = array();
        if (!empty($info)) {
            $user_info['user_key'] = $info['user_key'];
            $user_info['account_name'] = !empty($info['account_name']) ? $info['account_name'] : '';
            $user_info['layer_name'] = $info['layer_name'];
            $user_info['broker_1_key'] = !empty($info['broker_1_key']) ? $info['broker_1_key'] : '';
            $user_info['broker_2_key'] = !empty($info['broker_2_key']) ? $info['broker_2_key'] : '';
            $user_info['broker_3_key'] = !empty($info['broker_3_key']) ? $info['broker_3_key'] : '';
            $user_info['deposit_info']['money'] = $depositMoney;
            $user_info['deposit_info']['time'] = $depositTime;
            $user_info['deposit_info']['type'] = $depositTypes;
            $user_info['withdraw_info']['money'] = $withdrawMoney;
            $user_info['withdraw_info']['time'] = $withdrawTime;
            $user_info['withdraw_info']['type'] = $withdrawType;
            $user_info['memo'] = !empty($info['memo']) ? $info['memo'] : '';
            $user_info['bank_name'] = !empty($bank_name) ? $bank_name : '';
            $user_info['bank_branch'] = !empty($bank_branch) ? $bank_branch : '';
            $user_info['account_number'] = !empty($account_number) ? $account_number : '';
            $user_info['coupon_num'] = !empty($coupon['coupon_num']) ? $coupon['coupon_num'] : '0';
            $user_info['coupon_money'] = !empty($coupon['coupon_money']) ? $coupon['coupon_money'] : '0';
            $user_info['vary_coupon_num'] = '0';
            $user_info['vary_coupon_audit'] = '0';
        }
        $context->reply([
            'status' => 200,
            'msg' => ' 获取成功',
            'layer_list' => $layer_list,
            'data' => $user_info,
        ]);
    }
}
