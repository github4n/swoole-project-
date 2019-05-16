<?php

namespace Site\Websocket\Account\BoundIp;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 子账号绑定ip - 员工绑定ip删除接口
* @author： leo
* @date：   2019-04-08   
* @link：   Account/BoundIp/BoundDelete  [{"staff_id":"1","ip":"192.168.1.1"},{"staff_id":"1","ip":"192.168.1.1"}]
* @modifyAuthor: 交接负责人：暂无
* @modifyTime:   交接时间：暂无
* @param int     staff_id: 账号id
* @param string  ip: 绑定ip
* @returnData: json;
*/

class BoundDelete extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('slave_ip_delete', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo('StaffGrade');
        $mysql = $config->data_staff;
        $data = $context->getData();
        if (empty($data)) {
            $context->reply(['status' => 204, 'msg' => '参数不可为空']);
            return;
        }
        if ($StaffGrade == 0) {
            $slave_list = 'SELECT staff_id FROM staff_info_intact WHERE 1=:master_id ';
            $param = [
                ':master_id' => 1,
            ];
        } else {
            $slave_list = 'SELECT staff_id FROM staff_info_intact WHERE master_id=:master_id ';
            $param = [
            ':master_id' => $staffId,
            ];
        }
        $slaveList = [];
        $slaveResult = iterator_to_array($mysql->query($slave_list, $param));
        if (!empty($slaveResult)) {
            foreach ($slaveResult as $rows) {
                $slaveList[] = $rows['staff_id'];
            }
        } else {
            $context->reply(['status' => 202, 'msg' => '子账号错误']);
            return;
        }
        foreach ($data as $value) {
            if (!$value['staff_id'] || !in_array($value['staff_id'], $slaveList)) {
                $context->reply(['status' => 202, 'msg' => '子账号错误']);
                return;
            }
            $id = $value['staff_id'];
            $ip = ip2long($value['ip']);
            $sql = 'DELETE FROM staff_bind_ip WHERE staff_id=:staff_id AND bind_ip=:bind_ip ';
            $param = [
                ':staff_id' => $id,
                ':bind_ip' => $ip
            ];
            try {
                $res = $mysql->execute($sql, $param);
                if ($res == 0) {
                    $context->reply(['status' => 400, 'msg' => '删除失败']);
                    return;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '删除失败']);
                throw new \PDOException($e);
            }
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail, client_ip=:client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':operate_key' => 'slave_list_delete',
            ':detail' => '批量删除子账号绑定ip'.json_encode($data),
            ':client_ip' => ip2long($context->getClientAddr())
        ];
        $mysql->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '删除成功',
        ]);
    }
}
