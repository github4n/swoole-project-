<?php

namespace Site\Websocket\Cash\ManualDeposit;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * @description   现金系统-手工存提款-手工存入
 * @Author  Rose
 * @date  2019-05-08
 * @links  Cash/ManualDeposit/Import {"deposit_list":[{"user_key":"user","money":12,"user_name":"张三"},{"user_key":"user123","money":12,"user_name":"张三"}]}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class Import extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, '当前账号没有操作权限权限']);

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
        $mysql = $config->data_user;
        $mysqlStaff = $config->data_staff;
        $deposit_list = $data['deposit_list'];
        $userlist = array();
        $total_user = 0;
        $total_money = 0;
        foreach ($deposit_list as $item) {
            $msg = '正常';
            $user_info = array();
            $user_key = isset($item['user_key']) ? $item['user_key'] : '';
            $money = isset($item['money']) ? $item['money'] : '';
            if (!is_numeric($money)) {
                $context->reply(['status' => 400, 'msg' => '导入失败存入钱数参数错误']);

                return;
            }
            if ($money < 0) {
                $msg = '金额错误';
            }
            if ($masterId != 0) {
                $sql = 'select deposit_limit from staff_credit where staff_id=:staff_id';
                $deposit_limit = 0;
                foreach ($mysqlStaff->query($sql, [':staff_id' => $staffId]) as $rows) {
                    $deposit_limit = $rows['deposit_limit'];
                }
                if ($money > $deposit_limit) {
                    $msg = '超出账号限额';
                }
                $layer_sql = 'select group_concat(layer_id) as layer_list from staff_layer where staff_id=:staff_id';
                foreach ($mysqlStaff->query($layer_sql, [':staff_id' => $staffId]) as $rows) {
                    $layer_list = $rows['layer_list'];
                }
                if (empty($layer_list)) {
                    $layer_list = 0;
                }
                $sql = "select user_key,layer_name,user_id from user_info_intact WHERE user_key=:user_key and layer_id in ($layer_list) ";
            } else {
                $sql = 'SELECT user_key,layer_name,user_id FROM user_info_intact WHERE user_key=:user_key';
            }

            $user_name = isset($item['user_name']) ? $item['user_name'] : '';
//            $sql = "SELECT user_key,layer_name,user_id FROM user_info_intact WHERE user_key=:user_key";
            $param = [':user_key' => $user_key];
            $user_infos = [];
            foreach ($mysql->query($sql, $param) as $row) {
                $user_infos = $row;
            }
            if (empty($user_infos)) {
                $msg .= '超出管理层级';
            }
            if (!empty($user_infos['layer_name'])) {
                ++$total_user;
                $total_money += $money;
            }
            $user_info['user_id'] = $user_infos['user_id'];
            $user_info['user_key'] = $user_key;
            $user_info['user_name'] = $user_name;
            $user_info['money'] = $money;
            $user_info['msg'] = $msg;

            $userlist[] = $user_info;
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'total_user' => $total_user, 'total_money' => $total_money, 'list' => $userlist]);
    }
}
