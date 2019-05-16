<?php

namespace App\Websocket\User\Recharge;

use App\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 支付宝充值
 * User/Recharge/AlipayPay {"lunch_money":100,"route_id":1,"pay_callbackurl":"www.baidu.com"}
 *
 * */

class AlipayPay extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $guest_id = $context->getInfo("GuestId");
        if (!empty($guest_id)) {
            $context->reply(["status" => 500, "msg" => "游客身份，没有访问权限"]);
            return;
        }

        $deal_key = $context->getInfo("DealKey");
        $mysql = $config->__get("data_" . $deal_key);
        $mysqlStaff = $config->data_staff;
        $data = $context->getData();
        $lunch_money = $data["lunch_money"];
        $route_id = $data["route_id"];
        $way_key = 'alipay';
        $pay_callbackurl = !empty($data["pay_callbackurl"]) ? $data["pay_callbackurl"] : '';
        //数据判断
        if (!is_numeric($lunch_money)) {
            $context->reply(["status" => 204, "msg" => "请输入充值金额"]);
            return;
        }
        if (!is_numeric($route_id)) {
            $context->reply(["status" => 205, "msg" => "请选择充值方式"]);
            return;
        }
        if (empty($pay_callbackurl)) {
            $context->reply(["status" => 206, "msg" => "请输入支付成功后的返回路径"]);
            return;
        }
        //获取用户的具体信息
        $sql = "select passage_id,passage_name,gate_key,gate_name,account_number,account_name,way_key,way_name from deposit_route_gateway_intact where route_id=:route_id and way_key='$way_key'";
        $passage_info = [];
        foreach ($mysqlStaff->query($sql, [":route_id" => $route_id]) as $row) {
            $passage_info = $row;
        }
        if (empty($passage_info)) {
            $context->reply(["status" => 201, "msg" => "请选择正确的充值方式"]);
            return;
        }
        //获取用户的真实姓名
        $account_name = $context->getInfo("AccountName");
        $sql = "INSERT INTO deposit_launch SET user_id=:user_id,user_key=:user_key,account_name=:account_name,passage_id=:passage_id,passage_name=:passage_name,layer_id=:layer_id,launch_money=:launch_money,launch_device=:launch_device";
        $params = [
            ":user_id" => $context->getInfo("UserId"),
            ":user_key" => $context->getInfo("UserKey"),
            ":account_name" => $account_name,
            ":passage_id" => $passage_info["passage_id"],
            ":passage_name" => $passage_info["passage_name"],
            ":layer_id" => $context->getInfo("LayerId"),
            ":launch_money" => $lunch_money,
            ":launch_device" => $context->getInfo("LoginDevice"),
        ];
        $deposit_serial = '';
        try {
            $mysql->execute($sql, $params);
            $sql = 'SELECT serial_last("deposit") as deposit_serial';
            foreach ($mysql->query($sql) as $row) {
                $deposit_serial = $row['deposit_serial'];
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "获取失败"]);
            throw new \PDOException($e);
        }
        $sqls = "INSERT INTO deposit_gateway SET deposit_serial=:deposit_serial, gate_key=:gate_key, gate_name=:gate_name, way_key=:way_key, way_name=:way_name, to_account_number=:to_account_number, to_account_name=:to_account_name";
        $param = [
            ":deposit_serial" => $deposit_serial,
            ":gate_key" => $passage_info["gate_key"],
            ":gate_name" => $passage_info["gate_name"],
            ":way_key" => $passage_info["way_key"],
            ":way_name" => $passage_info["way_name"],
            ":to_account_number" => $passage_info["account_number"],
            ":to_account_name" => $passage_info["account_name"],
        ];
        try {
            $mysql->execute($sqls, $param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "获取失败"]);
            throw new \PDOException($e);
        }
        $redirect="http://127.0.0.1:8080/1/Pay/Mall"."?"."deposit_serial"."=".$deposit_serial;
        $context->reply(["status" => 200, "msg" => "成功", 'redirect' => $redirect]);
        return;
    }

}
