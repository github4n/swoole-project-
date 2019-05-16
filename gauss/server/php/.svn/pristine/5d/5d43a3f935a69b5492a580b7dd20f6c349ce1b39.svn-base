<?php

namespace Site\Websocket\Cash\ManualDeposit;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * @description   现金系统-根据会员账号查找会员余额和真实姓名
 * @Author  Rose
 * @date  2019-05-08
 * @links  Cash/ManualDeposit/MemberSearch
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class MemberSearch extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $user_key = $data['user_key'];
        if (empty($user_key)) {
            $context->reply(['status' => 203, 'msg' => '请输入会员账户']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_manual', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $info = [];
        $masterId = $context->getInfo('MasterId');
        $staffId = $context->getInfo('StaffId');
        $mysqlStaff = $config->data_staff;
        if ($masterId != 0) {
            $layer_sql = 'select group_concat(layer_id) as layer_list from staff_layer where staff_id=:staff_id';
            foreach ($mysqlStaff->query($layer_sql, [':staff_id' => $staffId]) as $rows) {
                $layer_list = $rows['layer_list'];
            }
            if (empty($layer_list)) {
                $layer_list = 0;
            }
            $userSql = "select user_id,account_name,deal_key from user_info_intact WHERE user_key=:user_key and layer_id in ($layer_list) ";
        } else {
            $userSql = 'select user_id,account_name,deal_key from user_info_intact WHERE user_key=:user_key  ';
        }
        $mysqlUser = $config->data_user;

        $param = [':user_key' => $user_key];
        try {
            foreach ($mysqlUser->query($userSql, $param) as $row) {
                $info['user_id'] = $row['user_id'];
                $info['user_name'] = empty($row['account_name']) ? '' : $row['account_name'];
                $mysqlDeal = $config->__get('data_'.$row['deal_key']);
                $dealSql = 'select money from account where user_id=:user_id';
                $money = [];
                foreach ($mysqlDeal->query($dealSql, [':user_id' => $row['user_id']]) as $rows) {
                    $money = $rows;
                }
                $info['money'] = empty($money) ? '0' : $money['money'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '搜索不到该账号']);
            throw new \PDOException($e);
        }
        if (empty($info)) {
            $context->reply(['status' => 204, 'msg' => '搜索不到该账号']);

            return;
        }
        $context->reply(['status' => 200, 'msg' => '查找成功', 'info' => $info]);
    }
}
