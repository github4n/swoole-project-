<?php

namespace Site\Websocket\Account\BoundIp;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 子账号绑定ip - 员工新增绑定ip接口
 * @author： leo
 * @date：   2019-04-08   
 * @link：   Account/BoundIp/BoundAdd {"staff_id":"5","ip":"192.168.1.1"}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:  交接时间：暂无
 * @param int     staff_id: 账号id
 * @param string  ip: 绑定ip
 * @returnData: json;
 */

class BoundAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('slave_ip_insert', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        $staffId = $context->getInfo('StaffId');
        $mysql = $config->data_staff;
        $data = $context->getData();
        $StaffGrade = $context->getInfo('StaffGrade');
        $id = $data['staff_id'];
        $ip = ip2long($data['ip']);
        if ($ip == false) {
            $context->reply(['status' => 204, 'msg' => '请输入正确的IP']);
            return;
        }
        $ip = sprintf('%u', $ip); //避免为负数的情况
        if ($StaffGrade == 0) {
            $slave_list = 'SELECT * FROM staff_info_intact WHERE staff_id=:staff_id ';
            $param = [':staff_id' => $id];
        } else {
            $slave_list = 'SELECT * FROM staff_info_intact WHERE staff_id =:staff_id AND master_id=:staffId ';
            $param = [
                ':staff_id' => $id,
                ':staffId' => $staffId,
            ];
        }
        $slaveResult = iterator_to_array($mysql->query($slave_list, $param));
        if (empty($slaveResult)) {
            $context->reply(['status' => 202, 'msg' => '搜索不到该账号']);

            return;
        }
        $ip_select = 'SELECT * FROM staff_bind_ip WHERE staff_id=:staff_id AND bind_ip=:bind_ip';
        $param = [
            ':staff_id' => $id,
            ':bind_ip' => $ip,
        ];
        $ipResult = iterator_to_array($mysql->query($ip_select, $param));
        if (!empty($ipResult)) {
            $context->reply(['status' => 203, 'msg' => '该子账号已绑定该ip']);

            return;
        }
        $add_time = time();
        $sql = 'INSERT INTO staff_bind_ip SET staff_id=:staff_id, bind_ip=:bind_ip, add_time=:add_time ';
        $param = [
            ':staff_id' => $id,
            ':bind_ip' => $ip,
            ':add_time' => $add_time,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '新增失败']);
            throw new \PDOException($e);
        }

        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail, client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'slave_list_update',
            ':detail' => '新增子账号绑定ip' . json_encode($id),
        ];
        $mysql->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '新增成功',
        ]);
    }
}
