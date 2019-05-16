<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-三方账户停用或者启用
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/DepositAccount/PayStop {"passage_id":2}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class PayStop extends CheckLogin
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
        $acceptable = $data['acceptable'];
        if (!is_numeric($route_id)) {
            $context->reply(['status' => 203, 'msg' => '参数id缺失']);

            return;
        }
        if (empty($acceptable)) {
            $context->reply(['status' => 204, 'msg' => '请提交修改数据']);

            return;
        }
        $passage_id = 0;
        $sql = 'select passage_id from deposit_route where route_id=:route_id';
        foreach ($mysql->query($sql, [':route_id' => $route_id]) as $row) {
            $passage_id = $row['passage_id'];
        }
        if (empty($passage_id)) {
            $context->reply(['status' => 206, 'msg' => '参数错误']);

            return;
        }
        $sql = 'select risk_control,cumulate,acceptable from deposit_passage where passage_id=:passage_id';
        $passage_info = [];
        foreach ($mysql->query($sql, [':passage_id' => $passage_id]) as $rows) {
            $passage_info = $rows;
        }
        if (empty($passage_info)) {
            $context->reply(['status' => 207, 'msg' => '参数错误']);

            return;
        }
        if ($acceptable == 1) {
            $acceptable = 1;
            if ($passage_info['acceptable'] == 0) {
                $context->reply(['status' => 300, 'msg' => '通道启用失败，请开启入款账户']);

                return;
            }
        } elseif ($acceptable == 2) {
            $acceptable = 0;
        } else {
            $context->reply(['status' => 205, 'msg' => '提交的参数错误']);

            return;
        }
        //查找入款账户是
        $sql = 'UPDATE deposit_route SET  acceptable = :acceptable WHERE route_id=:route_id';
        $param = [':route_id' => $route_id, ':acceptable' => $acceptable];
        try {
            $mysql->execute($sql, $param);
            //记录日志
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '操作失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '操作成功']);
    }
}
