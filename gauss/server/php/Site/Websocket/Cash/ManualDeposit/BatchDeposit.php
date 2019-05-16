<?php

namespace Site\Websocket\Cash\ManualDeposit;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description   现金系统-手工存提款-手工存入
 * @Author  Rose
 * @date  2019-04-26
 * @links Cash/ManualDeposit/BatchDeposit {"type":1,"memo":"测试数据","deposit":1,"coupon":2,"account":[{"user_id":1,"money":20},{"user_id":2,"money":30}]}
 * @param status 1等待入款 2入款成功 3入款失败
 * @modifyAuthor
 * @modifyDate
 * */

class BatchDeposit extends CheckLogin
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
        $data = $context->getData();
        $mysql = $config->data_staff;
        $deposit_type = $data['type'];
        $memo = $data['memo'];
        $deposit = $data['deposit'];
        $coupon = $data['coupon'];
        $account = $data['account'];
        if (!is_array($account)) {
            $context->reply(['status' => 205, 'msg' => '信息有误,请重新导入']);

            return;
        }
        if (empty($deposit_type)) {
            $context->reply(['status' => 206, 'msg' => '请选择存入项目']);

            return;
        }
        if (empty($deposit) && empty($coupon)) {
            $context->reply(['status' => 207, 'msg' => '请选择稽核类型']);

            return;
        }
        if (mb_strlen($memo) > 100) {
            $context->reply(['status' => 208, 'msg' => '请不要输入超过30个字']);

            return;
        }
        if ($deposit == 1) {
            $deposit = 0;
        } elseif ($deposit == 2) {
            $deposit = 1;
        } else {
            $deposit = 0;
        }
        $sql = 'INSERT INTO staff_deposit SET staff_id=:staff_id, deposit_type=:deposit_type,deposit_audit_multiple=:deposit_audit_multiple,coupon_audit_multiple=:coupon_audit_multiple,memo=:memo,user_money_map=:user_money_map,finish_count=:finish_count,finish_money=:finish_money,finish_time=:finish_time';
        $param = [
            ':staff_id' => $staffId,
            ':deposit_type' => $deposit_type,
            ':deposit_audit_multiple' => $deposit,
            ':coupon_audit_multiple' => $coupon,
            ':memo' => $memo,
            ':user_money_map' => json_encode($account),
            ':finish_count' => 0,
            ':finish_money' => 0,
            ':finish_time' => 0,
        ];
        try {
            $mysql->execute($sql, $param);
            $sql = 'SELECT last_insert_id() as staff_deposit_id';
            foreach ($mysql->query($sql) as $row) {
                $staff_deposit_id = $row['staff_deposit_id'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '存入失败']);
            throw new \PDOException($e);
        }
        $context->reply(['status' => 200, 'msg' => '操作成功']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_site);
        $taskAdapter->plan('Cash/Deposit', ['staff_deposit_id' => $staff_deposit_id, 'staff_name' => $context->getInfo('StaffKey')]);
    }
}
