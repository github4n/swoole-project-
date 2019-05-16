<?php

namespace Site\Websocket\Member\MemberList;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MemberNameUpdate class.
 *
 * @description   会员管理-会员列表-修改会员真实姓名
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/MemberList/MemberNameUpdate {"account_name":"李四","user_id":1}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class MemberNameUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_user;
        $account_name = $data['account_name'];
        $user_id = $data['user_id'];
        if (strlen($account_name) > 20) {
            $context->reply(['status' => 204, 'msg' => '真实姓名长度太长']);

            return;
        }
        if (!is_numeric($user_id)) {
            $context->reply(['status' => 205, 'msg' => '会员参数错误']);

            return;
        }
        $sql = 'SELECT bank_name FROM bank_info WHERE user_id=:user_id';
        $param = [':user_id' => $user_id];
        $bank_info = iterator_to_array($mysql->query($sql, $param));
        if (empty($bank_info)) {
            $context->reply(['status' => 207, 'msg' => '该用户还未绑定银行卡']);

            return;
        }
        $sql = 'UPDATE bank_info SET account_name=:account_name WHERE user_id=:user_id';
        $param = [':user_id' => $user_id, ':account_name' => $account_name];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //更新用户累计数据
        $report_mysql = $config->data_report;
        $sql = 'UPDATE user_cumulate SET user_name=:user_name WHERE user_id=:user_id';
        $param = [':user_id' => $user_id, ':user_name' => $account_name];
        try {
            $report_mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 402, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }

        $context->reply(['status' => 200, 'msg' => '修改成功']);

        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'user_list_update',
            ':detail' => '修改会员'.$user_id.'的真实姓名为'.$account_name,
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
    }
}
