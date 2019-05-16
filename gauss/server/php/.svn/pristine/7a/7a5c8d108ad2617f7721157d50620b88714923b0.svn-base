<?php

namespace App\Websocket\User\DealRecord;

use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

/*
 * 我的--交易记录
 * User/DealRecord/DealRecord type 1 充值　2 提现
 * */

class DealRecord extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }
                //获取缓存数据
        $userId = $context->getInfo('UserId');
        $deal_key = $context->getInfo('DealKey');
        $mysql = $config->__get("data_".$deal_key);
        $param = $context->getData();
        $type = isset($param['type']) ? $param['type'] : 1;
        if (!is_numeric($type) || $type > 2 || $type < 1) {
            $context->reply(['status'=>403,'msg'=> '获取失败']);
            return;
        }
        if ($type == 1) {
            $depositBank = [];
            //银行充值所有订单
            $sql = "SELECT launch_money,launch_time,finish_money,finish_time,deal_time,cancel_time,cancel_reason FROM deposit_bank_intact WHERE user_id='$userId' order by launch_time desc ";

            foreach($mysql->query($sql) as $row ) {
                $status = 0;
                $time = date('Y-m-d H:i:s',$row['launch_time']);
                $money = $row['launch_money'];
                if (!empty($row['finish_time'])) {
                    //成功
                    $status = 1;
                    $time = date('Y-m-d H:i:s',$row['finish_time']);
                    $money = $row['launch_money'];
                }
                if (!empty($row['cancel_time'])) {
                    //失败
                    $status = 0;
                    $time = date('Y-m-d H:i:s',$row['cancel_time']);
                    $money = $row['launch_money'];
                }
                if (empty($row['finish_time']) && empty($row['cancel_time'])) {
                    //充值审核中
                    $status = 2;
                    $time = date('Y-m-d H:i:s',$row['launch_time']);
                    $money = $row['launch_money'];
                }

                $deposit = [
                    'money'      => $money,
                    'launch_time'=> $time,
                    'gate_key'   => "",
                    'way_key'    => "",
                    'status'     => $status,
                    'reason'     => !empty($row['cancel_reason']) ? $row['cancel_reason'] : '',
                ];
                $depositBank[] = $deposit;
            }
            //手工充值
            $sql = "select money,deposit_time,deal_time from staff_deposit_intact where user_id='$userId' order by deposit_time desc";
            foreach ($mysql->query($sql) as $row){
                $deposit = [
                    'money'      => $row["money"],
                    'launch_time'=> !empty($row['deposit_time']) ? date('Y-m-d H:i:s',$row['deposit_time']) : '',
                    'gate_key'   => "",
                    'way_key'    => "",
                    'status'     => !empty($row["deposit_time"]) ? 1 : 0,
                    'reason'     => "",
                ];
                $depositBank[] = $deposit;
            }

            //便捷入款
            $sql = "select finish_money,finish_time from deposit_simple_intact where user_id='$userId' order by finish_time desc";
            foreach ($mysql->query($sql) as $row){
                $deposit = [
                    'money'      => $row["finish_money"],
                    'launch_time'=> !empty($row['finish_time']) ? date('Y-m-d H:i:s',$row['finish_time']) : '',
                    'gate_key'   => "",
                    'way_key'    => "",
                    'status'     => !empty($row["finish_time"]) ? 1 : 0,
                    'reason'     => "",
                ];
                $depositBank[] = $deposit;
            }

            //第三方充值的所有订单
            $sql = "SELECT launch_money,launch_time,gate_key,way_key,finish_money,finish_time,deal_time,cancel_time,cancel_reason FROM deposit_gateway_intact WHERE user_id='$userId' order by launch_time desc ";

            foreach($mysql->query($sql) as $value ) {
                $launch_money = $value['launch_money'];
                $launch_time = !empty($value['launch_time']) ? date('Y-m-d H:i:s',$value['launch_time']) : '';
                $gate_key = $value['gate_key'];
                $way_key = $value['way_key'];
                $finish_money = $value['finish_money'];
                $finish_time = !empty($value['finish_time']) ? date('Y-m-d H:i:s',$value['finish_time']) : '';
                $cancel_time = !empty($value['cancel_time']) ? date('Y-m-d H:i:s',$value['cancel_time']) : '';
                $cancel_reason =!empty($value['cancel_reason']) ? $value['cancel_reason'] : '';
                $status = 0;
                $time = $launch_time;
                $money = $launch_money;
                if ($finish_time) {
                    //成功
                    $status = 1;
                    $time = $finish_time;
                    $money = $finish_money;
                }
                if ($cancel_time) {
                    //失败
                    $status = 0;
                    $time = $cancel_time;
                    $money = $launch_money;
                }
                if (!$finish_time && !$cancel_time) {
                    //充值审核中
                    $status = 2;
                    $time = $launch_time;
                    $money = $launch_money;
                }
                $deposit = [
                    'money'    => $money,
                    'time'     => $time,
                    'gate_key' => $gate_key,
                    'way_key'  => $way_key,
                    'status'   => $status,
                    'reason'   => $cancel_reason
                ];
                $depositBank[] = $deposit;
            }

            $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => $depositBank]);
        }elseif($type == 2){
            $withdrawList = [];
            //提现的订单
            $sql = "SELECT launch_money,withdraw_money,launch_time,accept_time,reject_time,reject_reason,finish_time,cancel_time,cancel_reason FROM withdraw_intact WHERE user_id='$userId' order by launch_time desc ";
            foreach($mysql->query($sql,$param) as $val ) {
                $launch_money = $val['launch_money'];
                $withdraw_money = $val['withdraw_money'];
                $launch_time = !empty($val['launch_time']) ? date('Y-m-d H:i:s',$val['launch_time']) : '';
                $accept_time = !empty($val['accept_time']) ? date('Y-m-d H:i:s',$val['accept_time']) :'';
                $reject_time = !empty($val['reject_time']) ? date('Y-m-d H:i:s',$val['reject_time']) : '';
                $reject_reason = !empty($val['reject_reason']) ? $val['reject_reason'] : '';
                $finish_time = !empty($val['finish_time']) ? date('Y-m-d H:i:s',$val['finish_time']) : '';
                $cancel_time = !empty($val['cancel_time']) ? date('Y-m-d H:i:s',$val['cancel_time']) : '';;
                $cancel_reason = !empty($val['cancel_reason']) ? $val['cancel_reason'] : '';
                $status = 0;
                $money = $launch_money;
                $time = $launch_time;
                $reason = '';
                if ($finish_time) {
                    $status = 1;
                    $time = $finish_time;
                    $money = $launch_money;
                }
                //出款失败
                if ($cancel_time) {
                    $status = 0;
                    $time = $cancel_time;
                    $reason = $cancel_reason;
                }
                //拒绝出款
                if ($reject_time) {
                    $status = 3;
                    $time = $reject_time;
                    $reason = $reject_reason;
                }
                //等待出款
                if (!$reject_time && !$cancel_time && !$finish_time) {
                    $status = 2;
                    $time = $launch_time or $accept_time;
                }

                $withdraw = [
                    'money'   => $money,
                    'time'    => $time,
                    'reason'  => $reason,
                    'status'  => $status,
                ];
                $withdrawList[] = $withdraw;
            }

            //手工提出
            $sql = "select money,withdraw_type,withdraw_time,deal_time,memo from staff_withdraw_intact where user_id='$userId' order by withdraw_time desc  ";
            foreach ($mysql->query($sql) as $row){
                $withdraw = [
                    'money'   => $row["money"],
                    'time'    => !empty($row['withdraw_time']) ? date('Y-m-d H:i:s',$val['withdraw_time']) : date('Y-m-d H:i:s',$val['deal_time']),
                    'reason'  => !empty($row["memo"]) ? $row["memo"] : "",
                    'status'  => !empty($row["withdraw_time"]) ? 1 : 0,
                ];
                $withdrawList[] = $withdraw;
            }
            $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => $withdrawList]);
        }else{
            $context->reply(["status"=>300,"msg"=>"请选择正确的交易方式"]);
        }

    }
}