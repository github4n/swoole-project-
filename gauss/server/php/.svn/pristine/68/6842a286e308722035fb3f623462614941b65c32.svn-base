<?php

namespace Site\Websocket\Cash\WithdrawSetting;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-出入款设定-保存入款设定信息
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/WithdrawSetting/DepositSave {"list":[{"setting_key":"withdraw_free","value":6},{"setting_key":"withdraw_max","value":6000},{"setting_key":"withdraw_min","value":100}]}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class DepositSave extends CheckLogin
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
        $data = $context->getData();
        $list = $data['list'];
        if (!is_array($list)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);

            return;
        }
        foreach ($list as $item) {
            $setting_key = $item['setting_key'];
            $value = $item['value'];
            if (empty($setting_key)) {
                $context->reply(['status' => 205, 'msg' => '设置的关键字不能为空']);

                return;
            }
            if (!is_numeric($value)) {
                $context->reply(['status' => 206, 'msg' => '提交的数值了类型不正确']);

                return;
            }
            if ($value > 999 || $value < 1) {
                $context->reply(['status' => 207, 'msg' => '请输入正确数值']);

                return;
            }
        }
        foreach ($list as $item) {
            $setting_key = $item['setting_key'];
            $value = $item['value'];
            $sql = 'UPDATE site_setting SET int_value=:int_value WHERE setting_key=:setting_key';
            $param = [':int_value' => $value, ':setting_key' => $setting_key];
            try {
                $mysql->execute($sql, $param);
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '修改失败']);
                throw new \PDOException($e);
            }
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id,operate_key=:operate_key,detail=:detail,client_ip=:client_ip';
        $param = [':staff_id' => $staffId, ':operate_key' => 'money_setting', ':client_ip' => ip2long($context->getClientAddr()), ':detail' => '修改入款设定信息成功'];
        $mysql->execute($sql, $param);

        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('NotifyApp', ['path' => 'User/SiteSetting', 'data' => []]);
    }
}
