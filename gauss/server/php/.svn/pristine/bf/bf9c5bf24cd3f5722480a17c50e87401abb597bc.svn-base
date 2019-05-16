<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-支付管理-快捷支付入款通道保存修改信息
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/PayManage/PaySimpleEditUpdate {"route_id":3,"passage_id":3,"acceptable":1,"level_id":["1","2"]}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class PaySimpleEditUpdate extends CheckLogin
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
        $route_id = $data['route_id'];
        $passage_id = $data['passage_id'];

        $acceptable = $data['acceptable'];
        $layer_id = $data['level_id'];

        $min_money = $data['min_money'];
        $max_money = $data['max_money'];

        if (!is_numeric($route_id)) {
            $context->reply(['status' => 203, 'msg' => '参数类型错误']);

            return;
        }
        if (!is_numeric($passage_id)) {
            $context->reply(['status' => 204, 'msg' => '通道参数类型错误']);

            return;
        }
        if (empty($layer_id)) {
            $context->reply(['status' => 206, 'msg' => '请选择层级']);

            return;
        }
        if (!is_array($layer_id)) {
            $context->reply(['status' => 207, 'msg' => '请选择层级']);

            return;
        }
        if ($acceptable == 1) {
            $acceptable = 1;
        } elseif ($acceptable == 2) {
            $acceptable = 0;
        } else {
            $acceptable = 1;
        }
        if (!is_numeric($min_money)) {
            $context->reply(['status' => 204, 'msg' => '最低入款参数类型错误']);

            return;
        }
        if (!is_numeric($max_money)) {
            $context->reply(['status' => 205, 'msg' => '最高入款参数类型错误']);

            return;
        }
        //修改支付线路
        $sql = 'UPDATE deposit_route SET passage_id=:passage_id,acceptable=:acceptable,min_money=:min_money, max_money=:max_money WHERE route_id=:route_id';
        $param = [
            ':passage_id' => $passage_id,
            ':acceptable' => $acceptable,
            ':route_id' => $route_id,
            ':min_money' => $min_money,
            ':max_money' => $max_money,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //修改会员层级
        //先删除之前的
        $sql = 'DELETE FROM deposit_route_layer WHERE route_id=:route_id';
        $param = [':route_id' => $route_id];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        foreach ($layer_id as $item) {
            if (!is_numeric($item)) {
                $context->reply(['status' => 209, 'msg' => '层级参数错误']);

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
            ':detail' => '修改快捷入款支付路线'.$route_id,
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
