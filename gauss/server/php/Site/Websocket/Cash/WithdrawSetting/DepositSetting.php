<?php

namespace Site\Websocket\Cash\WithdrawSetting;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-出入款设定-入款设定信息
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/WithdrawSetting/DepositSave {"list":[{"setting_key":"withdraw_free","value":6},{"setting_key":"withdraw_max","value":6000},{"setting_key":"withdraw_min","value":100}]}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class DepositSetting extends CheckLogin
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
        if (!in_array('money_setting', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $mysql = $config->data_staff;
        $sql = 'SELECT * FROM site_setting';
        $list = iterator_to_array($mysql->query($sql));
        foreach ($list as $key => $val) {
            if ($val['setting_key'] == 'deposit_count_day') {  //每日入款次数
                $fee_max['deposit_key'] = $val['setting_key'];
                $fee_max['deposit_name'] = $val['description'];
                $fee_max['deposit_num'] = $val['int_value'];
            }
            if ($val['setting_key'] == 'deposit_interval') {  //每日入款间隔时间
                $fee_max['deposit_interval_key'] = $val['setting_key'];
                $fee_max['deposit_interval_name'] = $val['description'];
                $fee_max['deposit_interval_num'] = $val['int_value'];
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $fee_max]);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id,operate_key=:operate_key,detail=:detail,client_ip=:client_ip';
        $param = [':staff_id' => $staffId, ':operate_key' => 'money_setting', ':client_ip' => ip2long($context->getClientAddr()), ':detail' => '查看入款设定基本信息'];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $param);
    }
}
