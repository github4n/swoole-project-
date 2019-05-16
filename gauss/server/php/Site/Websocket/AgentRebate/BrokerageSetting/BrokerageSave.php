<?php

namespace Site\Websocket\AgentRebate\BrokerageSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * BrokerageSave.php.
 *
 * @description   代理返佣--佣金设定
 * @Author  Luis
 * @date  2019-04-07
 * @links  参数：AgentRebate/BrokerageSetting/BrokerageSave {"layer_id":11,"auto_deliver":1,"deliver_time":"13:15","min_bet_amount":"50","min_deposit":"1000","brokerage_list":[{"vigor_count":1,"broker_1_rate":"0.01","broker_2_rate":"0.003","broker_3_rate":"0.001"}]}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
class BrokerageSave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_setting', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $layer_id = $data['layer_id'];
        $auto_deliver = $data['auto_deliver'];
        $deliver_time = $data['deliver_time'];
        $min_bet_amount = $data['min_bet_amount'];
        $min_deposit = $data['min_deposit'];
        $brokerage_list = $data['brokerage_list'];
        if (!is_numeric($layer_id)) {
            $context->reply(['status' => 204, 'msg' => '会员层级参数错误']);

            return;
        }
        if (!is_numeric($min_bet_amount)) {
            $context->reply(['status' => 205, 'msg' => '最低投注额参数类型错误']);

            return;
        }
        if (!is_numeric($min_deposit)) {
            $context->reply(['status' => 206, 'msg' => '最低充值金额参数类型错误']);

            return;
        }
        if ($data['auto_deliver'] == 0) {//自动派发为0
            if (empty($data['deliver_time']) || date('H:i', strtotime($data['deliver_time'])) != $data['deliver_time']) {
                $context->reply(['status' => 211, 'msg' => '自动发放时间错误']);

                return;
            }
            $deliver_time = str_replace(':', '', $deliver_time);
        } else {
            $deliver_time = 0;
        }

        if (!is_array($brokerage_list)) {
            $context->reply(['status' => 207, 'msg' => '参数类型错误']);

            return;
        }
        $brokerage_rate = [];
        foreach ($brokerage_list as $item) {
            $vigor_count = $item['vigor_count'];
            $broker_1_rate = $item['broker_1_rate'];
            $broker_2_rate = $item['broker_2_rate'];
            $broker_3_rate = $item['broker_3_rate'];
            if (!is_numeric($vigor_count)) {
                $context->reply(['status' => 208, 'msg' => '会员活跃参数类型错误']);

                return;
            }
            if (!is_numeric($broker_1_rate)) {
                $context->reply(['status' => 209, 'msg' => '一级下线佣金比例错误']);

                return;
            }
            if (!is_numeric($broker_2_rate)) {
                $context->reply(['status' => 209, 'msg' => '二级下线佣金比例错误']);

                return;
            }
            if (!is_numeric($broker_3_rate)) {
                $context->reply(['status' => 209, 'msg' => '三级下线佣金比例错误']);

                return;
            }

            $brokerage_rate[] = [
                'layer_id' => $layer_id,
                'vigor_count' => $vigor_count,
                'broker_1_rate' => $broker_1_rate,
                'broker_2_rate' => $broker_2_rate,
                'broker_3_rate' => $broker_3_rate,
            ];
        }
        //删除之前的代理佣金比例
        $sql = 'DELETE FROM brokerage_rate WHERE layer_id=:layer_id';
        $param = [':layer_id' => $layer_id];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }

        $mysql->brokerage_rate->load($brokerage_rate, [], 'replace');

        //修改佣金设置
        $sql = 'UPDATE brokerage_setting SET min_bet_amount=:min_bet_amount,min_deposit=:min_deposit,auto_deliver=:auto_deliver,deliver_time=:deliver_time WHERE layer_id=:layer_id ';
        if ($auto_deliver == 0) {
            $layer_sql = 'select * from brokerage_setting where layer_id =:layer_id ';
            if (!$mysql->execute($layer_sql, ['layer_id' => $layer_id])) {
                $sql = 'insert into brokerage_setting (layer_id,min_bet_amount,min_deposit,auto_deliver,deliver_time) values (:layer_id,:min_bet_amount,:min_deposit,0,:deliver_time)';
            }
        }
        if ($auto_deliver == 1) {
            $deliver_time = 0;
            $layer_sql = 'select * from brokerage_setting where layer_id =:layer_id ';
            if (!$mysql->execute($layer_sql, ['layer_id' => $layer_id])) {
                $sql = 'insert into brokerage_setting (layer_id,min_bet_amount,min_deposit,auto_deliver,deliver_time) values (:layer_id,:min_bet_amount,:min_deposit,0,:deliver_time)';
            }
        }
        try {
            $mysql->execute($sql, [':min_bet_amount' => $min_bet_amount, ':min_deposit' => $min_deposit, ':auto_deliver' => $auto_deliver, ':deliver_time' => $deliver_time, ':layer_id' => $layer_id]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }

        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'broker_setting',
            ':detail' => '修改佣金比例',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
