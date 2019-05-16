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
 * @links  Cash/WithdrawSetting/ReviewSetting {}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class ReviewSetting extends CheckLogin
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
        $masterId = $context->getInfo('MasterId');
        $mysql = $config->data_user;
        $staff_mysql = $config->data_staff;
        if ($masterId == 0) {
            $sql = 'SELECT layer_id,layer_name,withdraw_audit_amount,withdraw_audit_first FROM layer_info';
            $param = [];
        } else {
            $layer_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
            $layer_lists = [];
            foreach ($staff_mysql->query($layer_sql, [':staff_id' => $staffId]) as $row) {
                $layer_lists[] = $row['layer_id'];
            }
            $sql = 'SELECT layer_id,layer_name,withdraw_audit_amount,withdraw_audit_first FROM layer_info where layer_id in :layer_list';
            $param = [':layer_list' => $layer_lists];
        }

        $list = iterator_to_array($mysql->query($sql, $param));
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $withdraw_list[$key]['layer_id'] = $val['layer_id'];
                $withdraw_list[$key]['layer_name'] = $val['layer_name'];
                $withdraw_list[$key]['withdraw_audit_amount'] = $val['withdraw_audit_amount']; //出款上限
                $withdraw_list[$key]['withdraw_audit_first'] = $val['withdraw_audit_first'];  //首次出款是否需要审核
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $withdraw_list]);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id,operate_key=:operate_key,detail=:detail,client_ip=:client_ip';
        $param = [':staff_id' => $staffId, ':operate_key' => 'money_setting', ':client_ip' => ip2long($context->getClientAddr()), ':detail' => '查看出款管理出款审核设置信息'];
        $staff_mysql->execute($sql, $param);
    }
}
