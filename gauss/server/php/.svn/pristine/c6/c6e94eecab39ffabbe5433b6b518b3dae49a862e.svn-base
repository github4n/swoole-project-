<?php

namespace Site\Websocket\Cash\DepositAccount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description    现金系统-三方账户停用或者启用
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/DepositAccount/DepositStop {"passage_id":2}
 * @modifyAuthor
 * @modifyDate
 *
 * */

class DepositStop extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_staff;
        $passage_id = $data['passage_id'];
        $acceptable = $data['acceptable'];
        if (!is_numeric($passage_id)) {
            $context->reply(['status' => 203, 'msg' => '参数id缺失']);

            return;
        }
        if (empty($acceptable)) {
            $context->reply(['status' => 204, 'msg' => '请提交修改数据']);

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
        if ($passage_info['cumulate'] >= $passage_info['risk_control']) {
            $context->reply(['status' => 300, 'msg' => '目前存款大于风控金额,不能开启该通道']);

            return;
        }

        if ($acceptable == 1) {
            $acceptable = 1;
        } elseif ($acceptable == 2) {
            $acceptable = 0;
        } else {
            $context->reply(['status' => 205, 'msg' => '提交的参数错误']);

            return;
        }
        $sql = 'UPDATE deposit_passage SET  acceptable = :acceptable WHERE passage_id=:passage_id';
        $param = [':passage_id' => $passage_id, ':acceptable' => $acceptable];
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
