<?php

namespace Site\Websocket\System\SystemPersonnel;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * @description: 体系人员列表 - 新增大股东接口
 * @author： leo
 * @date：   2019-04-08
 * @link：   System/SystemPersonnel/MajorAdd {"staff_name":"tomato6","staff_key":"tomato6","staff_password":"tomato6"}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime: 交接时间：暂无
 *
 * @param string staff_nam： 总代理名称
 * @param string staff_key： 总代理登录账号
 * @param string staff_password： 总代理密码
 * @returnData: json;
 */
class MajorAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //判断当前登录账号的权限
        $staffGrade = $context->getInfo('StaffGrade');
        if ($staffGrade >= 1) {
            $context->reply(['status' => 204, 'msg' => '没有操作的权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('staff_list_major_insert', $auth)) {
            $context->reply(['status' => 204, 'msg' => '你还没有操作权限']);

            return;
        }
        $allRow = [];
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');
        $data = $context->getData();
        $staff_name = $data['staff_name'];
        $staff_key = $data['staff_key'];
        $staff_password = $data['staff_password'];
        if (empty($staff_name)) {
            $context->reply(['status' => 202, 'msg' => '员工名不能为空']);
            return;
        }
        // 大股东名称校验
        $preg = '/^[\w\x{4e00}-\x{9fa5}]{4,20}$/u';
        if (!preg_match($preg, $staff_name)) {
            $context->reply(['status' => 204, 'msg' => '员工名称需4-20位字符,可包含中文、英文和数字']);
            return;
        }
        if (empty($staff_key)) {
            $context->reply(['status' => 203, 'msg' => '登录账号不能为空']);
            return;
        }
        // 验证规则
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/';
        if (!preg_match($preg, $staff_key)) {
            $context->reply(['status' => 207, 'msg' => '登录账号需6-20位字符,可包含英文和数字']);
            return;
        }
        if (empty($staff_password)) {
            $context->reply(['status' => 204, 'msg' => '登录密码不能为空']);
            return;
        }
        if (!preg_match($preg, $staff_password)) {
            $context->reply(['status' => 208, 'msg' => '登录密码需6-20位字符,可包含英文和数字']);
            return;
        }
        //判断登录账号是否存在
        $sql = 'SELECT staff_key FROM staff_auth WHERE staff_key = :staff_key';
        $param = [':staff_key' => $staff_key];
        $mysql = $config->data_staff;
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 401, 'msg' => '新增大股东失败']);
            throw new \PDOException($e);
        }
        if (!empty($info)) {
            $context->reply(['status' => 207, 'msg' => '该登录账号已被注册,请重新输入']);
            return;
        }
        //判断员工名称是否存在  (新增修改 04-08)
        $sql = 'SELECT staff_name FROM staff_info WHERE staff_name = :staff_name';
        $param = [':staff_name' => $staff_name];
        $staff_name_info = '';
        foreach ($mysql->query($sql, $param) as $row) {
            $staff_name_info = $row['staff_name'];
        }
        if (!empty($staff_name_info)) {
            $context->reply(['status' => 210, 'msg' => '该员工名称已被注册,请重新输入']);
            return;
        }
        //添加大股东的基本信息
        $sql = 'INSERT INTO staff_info SET staff_name = :staff_name, staff_grade = :staff_grade, master_id = :master_id, leader_id = :leader_id, add_time = :add_time, add_ip = :add_ip';
        $params = [
            ':staff_name' => $staff_name,
            ':staff_grade' => 1,
            ':master_id' => 0,
            ':leader_id' => $MasterId == 0 ? $staffId : $MasterId,
            ':add_time' => time(),
            ':add_ip' => ip2long($context->getClientAddr()),
        ];
        try {
            $mysql->execute($sql, $params);
            $sql = 'SELECT last_insert_id() as staff_id';
            foreach ($mysql->query($sql) as $row) {
                $staff_id = $row['staff_id'];
            }
            //记录日志
            $sql = 'INSERT INTO operate_log  SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
            $params = [
                ':staff_id' => $staffId,
                ':client_ip' => ip2long($context->getClientAddr()),
                ':operate_key' => 'staff_list_major_insert',
                ':detail' => '添加大股东编号' . $staff_id . '的基本信息',
            ];
            $mysql->execute($sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 402, 'msg' => '新增大股东失败']);
            throw new \PDOException($e);
        }
        //添加大股东的登录信息
        $sql = 'INSERT INTO staff_auth SET staff_id = :staff_id, staff_key = :staff_key, password_hash = :password_hash';
        $param = [
            ':staff_id' => $staff_id,
            ':staff_key' => $staff_key,
            ':password_hash' => $staff_password,
        ];
        try {
            $mysql->execute($sql, $param);
            //记录日志
            $sql = 'INSERT INTO operate_log SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
            $params = [
                ':staff_id' => $staffId,
                ':client_ip' => ip2long($context->getClientAddr()),
                ':operate_key' => 'staff_list_major_insert',
                ':detail' => '添加大股东编号为' . $staff_id . '的登录信息',
            ];
            $mysql->execute($sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 403, 'msg' => '新增大股东失败']);
            throw new \PDOException($e);
        }
        //添加大股东权限
        $sql = 'SELECT operate_key FROM operate WHERE major_permit = 0 or major_permit = 1';
        foreach ($mysql->query($sql) as $row) {
            $allRow[] = ['staff_id' => $staff_id, 'operate_key' => $row['operate_key']];
        }
        $mysql->staff_permit->load($allRow, [], 'ignore');
        $context->reply(['status' => 200, 'msg' => '新增大股东成功']);
    }
}
