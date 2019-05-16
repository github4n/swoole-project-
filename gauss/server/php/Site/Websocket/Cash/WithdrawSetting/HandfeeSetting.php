<?php

namespace Site\Websocket\Cash\WithdrawSetting;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-出款管理-出款审核设置
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/WithdrawSetting/HandfeeSetting {}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class HandfeeSetting extends CheckLogin
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
        $lists = [];
        foreach ($list as $key => $val) {
            if ($val['setting_key'] == 'withdraw_fee_max') {
                $fee_max['key'] = $val['setting_key'];
                $fee_max['name'] = $val['description'];
                $fee_max['num'] = $this->intercept_num($val['dbl_value']);
                $lists[] = $fee_max;
            }
            if ($val['setting_key'] == 'withdraw_fee_rate') {
                $fee_rate['key'] = $val['setting_key'];
                $fee_rate['name'] = $val['description'];
                $fee_rate['num'] = $this->intercept_num($val['dbl_value']);
                $lists[] = $fee_rate;
            }
            if ($val['setting_key'] == 'withdraw_interval') {
                $interval['key'] = $val['setting_key'];
                $interval['name'] = $val['description'];
                $interval['num'] = $val['int_value'];
                $lists[] = $interval;
            }
            if ($val['setting_key'] == 'withdraw_max') {
                $max['key'] = $val['setting_key'];
                $max['name'] = $val['description'];
                $max['num'] = $this->intercept_num($val['dbl_value']);
                $lists[] = $max;
            }
            if ($val['setting_key'] == 'withdraw_min') {
                $min['key'] = $val['setting_key'];
                $min['name'] = $val['description'];
                $min['num'] = $this->intercept_num($val['dbl_value']);
                $lists[] = $min;
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $lists]);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id,operate_key=:operate_key,detail=:detail,client_ip=:client_ip';
        $param = [':staff_id' => $staffId, ':operate_key' => 'money_setting', ':client_ip' => ip2long($context->getClientAddr()), ':detail' => '查看出款管理出款手续费设置信息'];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $param);
    }
}
