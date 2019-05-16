<?php

namespace Site\Websocket\Cash\ManualDeposit;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * @description   现金系统-手工存提款-手工提出
 * @Author  Rose
 * @date  2019-05-08
 * @links  Cash/ManualDeposit/ManualWithdraw {"user_id":1,"type":1,"money":100,"memo":"测试数据","deposit":"0","coupon":"0"}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class ManualWithdraw extends CheckLogin
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
        if (!in_array('money_manual', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $masterId = $context->getInfo('MasterId');
        $data = $context->getData();
        $mysql = $config->data_staff;
        $user_mysql = $config->data_user;
        $mysqlReport = $config->data_report;
        $user_id = $data['user_id'];
        $deposit_type = $data['type'];
        $finish_money = $data['money'];
        $memo = $data['memo'];
        $deposit = $data['deposit'];
        $coupon = $data['coupon'];
        if (!is_numeric($user_id)) {
            $context->reply(['status' => 204, 'msg' => '请输入会员账号']);

            return;
        }
        if (!is_numeric($finish_money) && empty($deposit) && empty($coupon)) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if ($finish_money==0 && $deposit==0 && $coupon==0) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if ($finish_money > 9999999.99) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if ($deposit > 9999999.99) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if ($coupon > 9999999.99) {
            $context->reply(['status' => 205, 'msg' => '请输入金额']);

            return;
        }
        if (!is_numeric($deposit_type)) {
            $context->reply(['status' => 206, 'msg' => '请选择提出项目']);

            return;
        }
        if (mb_strlen($memo) > 100) {
            $context->reply(['status' => 208, 'msg' => '请不要输入超过30个字']);

            return;
        }
        //查询用户余额
        $sql = 'select deal_key from user_info where user_id=:user_id';
        foreach ($user_mysql->query($sql, [':user_id' => $user_id]) as $row) {
            $deal_info = $row;
        }
        $deal_key = $row['deal_key'];
        $sql = 'select money from account where user_id=:user_id';
        $dealMysql = $config->__get('data_'.$deal_key);
        foreach ($dealMysql->query($sql, [':user_id' => $user_id]) as $row) {
            $money = $row;
        }
        if ($masterId != 0) {
            $sql = 'select withdraw_limit from staff_credit where staff_id=:staff_id';
            $withdraw_limit = 0;
            foreach ($mysql->query($sql, [':staff_id' => $staffId]) as $rows) {
                $withdraw_limit = $rows['withdraw_limit'];
            }
            if ($finish_money > $withdraw_limit) {
                $context->reply(['status' => 300, 'msg' => '超出账号限额']);

                return;
            }
        }
        if ($finish_money > $money['money']) {
            $context->reply(['status' => 206, 'msg' => '余额不足']);

            return;
        }
        $money_map = [$user_id => ['money' => $finish_money, 'deposit_audit' => $deposit, 'coupon_audit' => $coupon]];
        $sql = 'INSERT INTO staff_withdraw SET staff_id=:staff_id, withdraw_type=:withdraw_type,deposit_audit_multiple=:deposit_audit_multiple,coupon_audit_multiple=:coupon_audit_multiple,memo=:memo,user_money_map=:user_money_map,finish_count=:finish_count,finish_money=:finish_money,finish_time=:finish_time';
        $param = [
            ':staff_id' => $staffId,
            ':withdraw_type' => $deposit_type,
            ':deposit_audit_multiple' => 0,
            ':coupon_audit_multiple' => 0,
            ':memo' => $memo,
            ':user_money_map' => json_encode($money_map),
            ':finish_count' => 0,
            ':finish_money' => 0,
            ':finish_time' => 0,
        ];
        try {
            $mysql->execute($sql, $param);
            $sql = 'SELECT last_insert_id() as staff_withdraw_id';
            foreach ($mysql->query($sql) as $row) {
                $staff_withdraw_id = $row['staff_withdraw_id'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '提出失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '操作成功']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_site);
        $taskAdapter->plan('Cash/Withdraw', ['staff_withdraw_id' => $staff_withdraw_id, 'staff_name' => $context->getInfo('StaffKey')]);

        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);

        $sql = 'SELECT client_id FROM user_session WHERE user_id=:user_id';

        $param = [':user_id' => $user_id];
        foreach ($user_mysql->query($sql, $param) as $row) {
            $id = $row['client_id'];
            $taskAdapter->plan('NotifyApp', ['path' => 'User/Balance', 'data' => ['user_id' => $user_id, 'id' => $id, 'deal_key' => $deal_key]]);
        }
    }
}
