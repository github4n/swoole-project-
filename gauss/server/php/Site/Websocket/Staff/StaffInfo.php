<?php

namespace Site\Websocket\Staff;

use Lib\Websocket\IHandler;
use Lib\Websocket\Context;
use Lib\Config;

/** 
* @description: 获取账户资料接口
* @author： leo
* @date：   2019-04-08   
* @link：   Staff/StaffInfo {}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime:  交接时间：暂无
* @returnData: json;
*/

class StaffInfo implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $status = 0;
        $mysqlStaff = $config->data_staff;
        $sql = "SELECT int_value FROM site_setting WHERE setting_key = 'site_status'";
        foreach ($mysqlStaff->query($sql) as $row) {
            $status = $row['int_value'];
        }
        if ($status == 3) {
            $context->reply(['status' => 500, 'msg' => '维护中']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $staff_sql = 'SELECT staff_key,staff_id,staff_name,staff_grade,master_id,add_time,leader_id FROM staff_info_intact WHERE staff_id=:staff_id';
        $param = [':staff_id' => $staffId];
        $staffInfo = [];
        foreach ($mysqlStaff->query($staff_sql, $param) as $rows) {
            $staffInfo = $rows;
        }
        $staff_info = [];
        if (!empty($staffInfo)) {
            $staff_info['staff_key'] = isset($staffInfo['staff_key']) ? $staffInfo['staff_key'] : '';
            $staff_info['staff_name'] = isset($staffInfo['staff_name']) ? $staffInfo['staff_name'] : '';
            $staff_info['staff_grade'] = isset($staffInfo['staff_grade']) ? $staffInfo['staff_grade'] : '';
            $staff_info['add_time'] = isset($staffInfo['add_time']) ? date('Y-m-d', $staffInfo['add_time']) : '';
            if ($staffInfo['staff_grade'] == 0) {
                $staff_info['grade_name'] = '站长';
            }
            if ($staffInfo['staff_grade'] == 1) {
                $staff_info['grade_name'] = '大股东';
            }
            if ($staffInfo['staff_grade'] == 2) {
                $staff_info['grade_name'] = '股东';
            }
            if ($staffInfo['staff_grade'] == 3) {
                $staff_info['grade_name'] = '总代理';
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => $staff_info]);
    }
}
