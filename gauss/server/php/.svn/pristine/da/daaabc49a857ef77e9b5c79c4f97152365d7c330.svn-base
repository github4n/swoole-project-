<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-支付管理-删除第三方
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/PayManage/PayGateDelete  {"route_id":1}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class PayGateDelete extends CheckLogin
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
        if (!is_numeric($route_id)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);

            return;
        }
        if ($this->checkRoute($route_id, $config)) {
            $context->reply(['status' => 300, 'msg' => '还有未完成的入款订单，禁止删除']);

            return;
        }
        $sql = 'DELETE FROM deposit_route WHERE route_id=:route_id';
        $sqls = 'DELETE FROM deposit_route_layer WHERE route_id=:route_id';
        $sqlm = 'DELETE FROM deposit_route_way WHERE route_id=:route_id';
        $param = [':route_id' => $route_id];
        try {
            $mysql->execute($sql, $param);
            $mysql->execute($sqls, $param);
            $mysql->execute($sqlm, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'money_deposit_route',
            ':detail' => '删除三方入款支付线路'.$route_id,
        ];
        $mysql->execute($sql, $params);
        $context->reply(['status' => 200, 'msg' => '删除成功']);
    }
}
