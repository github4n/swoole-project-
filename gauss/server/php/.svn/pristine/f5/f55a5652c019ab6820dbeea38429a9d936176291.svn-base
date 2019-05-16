<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-支付管理-快捷支付入款通道添加
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/PayManage/PaySimpleAddUpdate {"passage_id":3,"acceptable":1,"level_id":["1","11"]}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class PaySimpleAddUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

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
        $passage_id = $data['passage_id'];
        $min_money = $data['min_money'];
        $max_money = $data['max_money'];
        $acceptable = $data['acceptable'];
        $layer_id = $data['level_id'];
        if (!is_numeric($passage_id)) {
            $context->reply(['status' => 203, 'msg' => '通道参数类型错误']);

            return;
        }
        if (empty($layer_id)) {
            $context->reply(['status' => 206, 'msg' => '请选择层级']);

            return;
        }
        if (!is_array($layer_id)) {
            $context->reply(['status' => 206, 'msg' => '请选择层级']);

            return;
        }
        if ($acceptable == 1) {
            $acceptable = 1;
        } else {
            $acceptable = 0;
        }
        if (!is_numeric($min_money)) {
            $context->reply(['status' => 204, 'msg' => '最低入款参数类型错误']);

            return;
        }
        if (!is_numeric($max_money)) {
            $context->reply(['status' => 205, 'msg' => '最高入款参数类型错误']);

            return;
        }
        //插入入款通道信息
        $sql = 'INSERT INTO deposit_route SET passage_id=:passage_id, min_money=:min_money, max_money=:max_money, acceptable=:acceptable, coupon_rate=:coupon_rate, coupon_max=:coupon_max, coupon_times=:coupon_times, coupon_audit_rate=:coupon_audit_rate';
        $param = [
            ':passage_id' => $passage_id,
            ':min_money' => $min_money,
            ':max_money' => $max_money,
            ':acceptable' => $acceptable,
            ':coupon_rate' => 0,
            ':coupon_max' => 0,
            ':coupon_times' => 0,
            ':coupon_audit_rate' => 0,
        ];
        try {
            $mysql->execute($sql, $param);
            $sql = 'SELECT last_insert_id() as route_id';
            foreach ($mysql->query($sql) as $row) {
                $route_id = $row['route_id'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '添加失败']);
            throw new \PDOException($e);
        }
        //插入会员层级信息
        foreach ($layer_id as $item) {
            if (!is_numeric($item)) {
                $context->reply(['status' => 208, 'msg' => '层级参数错误']);

                return;
            }
            $layer_info = ['route_id' => $route_id, 'layer_id' => $item];
            $layers[] = $layer_info;
        }
        $mysql->deposit_route_layer->load($layers, [], 'replace');
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_route',
            ':detail' => '新增便捷支付线路'.$route_id,
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '添加成功']);
    }
}
