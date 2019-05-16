<?php

namespace Site\Websocket\Account\BoundIp;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 子账号绑定ip - 获取员工信息
 * @author： leo
 * @date：   2019-04-08   
 * @link：   Account/BoundIp/SlaveSearch {"staff_key":"2"}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:   交接时间：暂无
 * @param string  staff_key: 账号名称
 * @returnData: json;
 */

class SlaveSearch extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('slave_ip_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        $staffId = $context->getInfo('StaffId');
        $mysql = $config->data_staff;
        $data = $context->getData();
        $staff_key = $data['staff_key'];
        if (empty($staff_key)) {
            $context->reply(['status' => 202, 'msg' => '子账号的登陆账号不能为空']);
            return;
        }
        $StaffGrade = $context->getInfo('StaffGrade');
        $MasterId = $context->getInfo('MasterId');
        if ($MasterId != 0) {
            $staff_mysql = $config->data_staff;
            $sql = 'SELECT master_id FROM staff_info WHERE staff_id = :staff_id ';
            $param = [':staff_id' => $staffId];
            $betTranslation = iterator_to_array($staff_mysql->query($sql, $param));
            $staffId = $betTranslation[0]['master_id'];
        }
        //同级以及子账号可以进行绑定
        if ($StaffGrade == 0) {
            $slave_list = 'SELECT * FROM staff_info_intact WHERE staff_key = :staff_key ';
            $param = [':staff_key' => $staff_key];
        } else {
            $slave_list = 'SELECT * FROM staff_info_intact WHERE staff_key = :staff_key AND master_id = :master_id ';
            $param = [
                ':staff_key' => $staff_key,
                ':master_id' => $staffId
            ];
        }
        $slaveResult = iterator_to_array($mysql->query($slave_list, $param));
        $dataResult = !empty($slaveResult) ? $slaveResult[0] : '';
        if (empty($dataResult)) {
            $context->reply([
                'status' => 300,
                'msg' => '搜索不到该账号'
            ]);
        } else {
            $context->reply([
                'status' => 200,
                'msg' => '获取成功',
                'data' => $dataResult
            ]);
        }
    }
}
