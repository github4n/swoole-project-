<?php

namespace App\Websocket\User\Recharge\AsynchronousCallback;

use Lib\Websocket\Context;
use Lib\Config;
use Lib\Http\Handler;

/*
 * 便利付异步
 * http://127.0.0.1:8080/2/AsynchronousCallback/BianliPayCallback
 *
 * */

class BianliPayCallback extends Handler {

    public function onRequest(Context $context, Config $config) {
//wss://1.ws.gauss.xblan.cc/User/Recharge/AsynchronousCallback/BianliPayCallback
        $ReturnArray = array(// 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" => $_REQUEST["orderid"], // 订单号
            "amount" => $_REQUEST["amount"], // 交易金额
            "datetime" => $_REQUEST["datetime"], // 交易时间
            "returncode" => $_REQUEST["returncode"] //订单状态
        );

        $staff_mysql = $config->data_staff;
        $deposit_serial = $_REQUEST["memberid"]; // 商户ID
//        $deposit_serial=10030;
        $deposit_passage_gate_sql = "select signature_key from deposit_passage_gate_intact where gate_name=" . "'" . 便利付 . "'" . " and account_number='$deposit_serial' limit 1";
        $deposit_passage_gate_list = iterator_to_array($staff_mysql->query($deposit_passage_gate_sql));
        $Md5key = !empty($deposit_passage_gate_list[0]['signature_key']) ? $deposit_passage_gate_list[0]['signature_key'] : '';
        ///////////////////////////////////////////////////////
        ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        ///////////////////////////////////////////////////////
//$context->reply(["status"=>206,"msg"=>$Md5key]);
//            return;
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["returncode"] == "00") {
                $finish_money = 0;
                $coupon_money = 0;
                $coupon_audit_rate = 0;
                $user_id = 0;
                $deal_key = '';
                foreach ($config->deal_list as $dealChoise) {

                    if (empty($user_id)) {
                        $mysql = $config->__get("data_" . $dealChoise);
                        $deal_key = $dealChoise;
                        $deposit_launch_sql = "SELECT user_id,launch_money,coupon_money,coupon_audit_rate,passage_id FROM deposit_launch WHERE deposit_serial='$deposit_serial'";
                        foreach ($mysql->query($deposit_launch_sql) as $row) {
                            $finish_money = $row["launch_money"];
                            $coupon_money = $row["coupon_money"];
                            $coupon_audit_rate = $row["coupon_audit_rate"];
                            $passage_id = $row["passage_id"];
                            $user_id = $row["user_id"];
                        }
                    }
                }

                if (empty($user_id)) {
                    $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
                    $params = [
                        ':staff_id' => 0,
                        ':client_ip' => ip2long($context->getClientAddr()),
                        ':operate_key' => 'money_deposit_deal',
                        ':detail' => "收到BianLiPay的错误的异步入款单号入款",
                    ];
                    $staff_mysql->execute($sql, $params);
                    return;
                }

                $sqls = "INSERT INTO deposit_finish SET deposit_serial=:deposit_serial, finish_money=:finish_money,coupon_audit=:coupon_audit, finish_staff_id=:finish_staff_id,finish_staff_name=:finish_staff_name";
                $params = [
                    ":deposit_serial" => $deposit_serial,
                    ":finish_money" => $finish_money + $coupon_money,
                    ":coupon_audit" => $coupon_audit_rate * $coupon_money,
                    ":finish_staff_id" => 0,
                    ":finish_staff_name" => 0,
                ];

                try {
                    $mysql->execute($sqls, $params);
                } catch (\PDOException $e) {
                    $context->reply(["status" => 401, "msg" => "操作失败"]);
                    throw new \PDOException($e);
                }
                //记录日志
                $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
                $params = [
                    ':staff_id' => 0,
                    ':client_ip' => ip2long($context->getClientAddr()),
                    ':operate_key' => 'money_deposit_deal',
                    ':detail' => 'BianLiPay异步收到入款单号为' . $deposit_serial . "的入款",
                ];
                $staff_mysql = $config->data_staff;
                $staff_mysql->execute($sql, $params);
                //更新用户累计数据
                $sql = "UPDATE user_cumulate SET money = money+:money,deposit_count = deposit_count+1,deposit_amount =  deposit_amount+:deposit_amount WHERE user_id=:user_id";
                $param = [
                    ":money" => $finish_money + $coupon_money,
                    ":deposit_amount" => $finish_money + $coupon_money,
                    ":user_id" => $user_id
                ];
                $data_report = $config->data_report;
                try {
                    $data_report->execute($sql, $param);
                } catch (\PDOException $e) {
                    $context->reply(["status" => 402, "msg" => "操作失败"]);
                    throw new \PDOException($e);
                }
                //更新事件数据
                $sql = "UPDATE user_event SET last_deposit_time=unix_timestamp() WHERE user_id=:user_id";
                $param = [":user_id" => $user_id];
                try {
                    $data_report->execute($sql, $param);
                } catch (\PDOException $e) {
                    $context->reply(["status" => 403, "msg" => "操作失败"]);
                    throw new \PDOException($e);
                }

                $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
                $user_mysql = $config->data_user;
                $sql = "SELECT client_id FROM user_session WHERE user_id=:user_id";
                $param = ['user_id' => $user_id];
                foreach ($user_mysql->query($sql, $param) as $row) {
                    $id = $row['client_id'];
                    $taskAdapter->plan('NotifyApp', ['path' => 'User/Balance', 'data' => ['user_id' => $user_id, "id" => $id, "deal_key" => $deal_key]]);
                }
                //更新账户的目前存款
                $sql = "update deposit_passage set cumulate = cumulate+:cumulate where passage_id=:passage_id";
                $staff_mysql->execute($sql, [":cumulate" => $finish_money, ":passage_id" => $passage_id]);
                //检测入款通道是否已经达到风控金额
                $taskAdapter->plan('Cash/Passage', [], time(), 9);
                return "OK";
            }
        }
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => 0,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_deal',
            ':detail' => "BianLiPay的异步入款失败，单号为:" . $deposit_serial,
        ];
        $staff_mysql->execute($sql, $params);
        return "OK";
    }

}
