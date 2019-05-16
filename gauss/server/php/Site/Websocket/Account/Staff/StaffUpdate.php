<?php

namespace Site\Websocket\Account\Staff;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 子账号管理 - 修改的子账号的信息接口
 * @author： leo
 * @date：   2019-04-08   
 * @link：   Account/Staff/StaffUpdate {
 *               "staff_id": "110",      
 *               "deposit_limit": "110",
 *               "level_list": ["11","1"],
 *               "operate_list":["home_topmajor","slave_list_select","slave_ip_select"],
 *               "password": "stan666",
 *               "staff_key": "stan666",
 *               "staff_name": "stan666", 
 *               "withdraw_limit": "10",
 *               "notify_status":"1"
 *           }
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:  交接时间：暂无
 * @param int    staff_id: 账号id
 * @param string staff_key： 登录名 
 * @param string staff_name： 用户名 
 * @param string password： 登录密码 
 * @param array  level_list： 会员层级 
 * @param string notify_status： 接收超过授权金额的提示 1-接收 0-不接受
 * @param int    withdraw_limit： 出款每笔最大限额
 * @param int    deposit_limit： 入款每笔最大限额 
 * @param array  operate_list： 用员工权限设置 
 * @returnData: json;
 */

class StaffUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("slave_list_update", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo("StaffGrade");
        $data = $context->getData();
        $staff_id = isset($data['staff_id']) ? $data['staff_id'] : '';
        $staff_name = isset($data["staff_name"]) ? $data["staff_name"] : '';
        $staff_key = isset($data["staff_key"]) ? $data["staff_key"] : '';
        $staff_password = isset($data["staff_password"]) ? $data["staff_password"] : '';
        $level_list = isset($data["level_list"]) ? $data["level_list"] : '';
        $notify_status = isset($data["notify_status"]) ? $data["notify_status"] : '';
        $deposit_limit = isset($data["deposit_limit"]) ? $data["deposit_limit"] : 0;
        $withdraw_limit = isset($data["withdraw_limit"]) ? $data["withdraw_limit"] : 0;
        $operate_keys = isset($data["operate_list"]) ? $data["operate_list"] : '';
        $info = array();
        $operate_list = array();
        //基本判断
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 202, "msg" => "账号参数类型错误"]);
            return;
        }
        if (empty($staff_name)) {
            $context->reply(["status" => 203, "msg" => "员工名称不能为空"]);
            return;
        }
        if (empty($staff_key)) {
            $context->reply(["status" => 204, "msg" => "登录名称不能为空"]);
            return;
        }
        if (!empty($staff_password)) {
            //判断密码规则
            $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,40}$/';
            if (!preg_match($preg, $staff_password)) {
                $context->reply(['status' => 206, 'msg' => '密码只能由数字字母组成，长度有效区间：[6-40]']);
                return;
            }
        }
        if (!empty($deposit_limit)) {
            if (!is_numeric($deposit_limit)) {
                $context->reply(["status" => 208, "msg" => "入款金额类型不正确"]);
                return;
            }
        }
        if (!empty($withdraw_limit)) {
            if (!is_numeric($withdraw_limit)) {
                $context->reply(["status" => 209, "msg" => "出款金额类型不正确"]);
                return;
            }
        }
        if (empty($level_list)) {
            $context->reply(["status" => 210, "msg" => "会员等级列表不能为空"]);
            return;
        }
        if (empty($operate_keys)) {
            $context->reply(["status" => 211, "msg" => "子账号操作权限不能为空"]);
            return;
        }
        if ($notify_status == 1) {
            $notify_status = 1;
        } else {
            $notify_status = 0;
        }
        //判断修改的登录名称是否已经存在
        $sql = "SELECT staff_key FROM staff_auth WHERE staff_key = :staff_key AND staff_id != :staff_id";
        $param = [":staff_key" => $staff_key, ":staff_id" => $staff_id];
        $mysql = $config->data_staff;
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "添加失败"]);
            throw new \PDOException($e);
        }
        if (!empty($info)) {
            $context->reply(["status" => 207, "msg" => "登录名称已经存在"]);
            return;
        }
        //记录日志sql
        $log_sql = 'INSERT INTO operate_log SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
        $log_params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'slave_list_update'
        ];
        //修改员工的基本信息
        $sql = "UPDATE staff_info SET staff_name = :staff_name WHERE staff_id = :staff_id";
        $param = [":staff_id" => $staff_id, ":staff_name" => $staff_name];
        try {
            $affectedRow = $mysql->execute($sql, $param);
            if ($affectedRow > 0) {
                //记录日志
                $log_params[':detail'] = '修改子账号编号为' . $staff_id . '的基本信息';
                $mysql->execute($log_sql, $log_params);
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "修改失败"]);
            throw new \PDOException($e);
        }
        //修改员工的登录信息
        if (empty($staff_password)) {
            $sql = "UPDATE staff_auth SET staff_key = :staff_key WHERE staff_id = :staff_id";
            $param = [":staff_key" => $staff_key, "staff_id" => $staff_id];
        }
        if (!empty($staff_password)) {
            $sql = "UPDATE staff_auth SET staff_key = :staff_key,password_hash = :password_hash WHERE staff_id = :staff_id";
            $param = [":staff_key" => $staff_key, "staff_id" => $staff_id, ":password_hash" => $staff_password];
        }
        try {
            $affectedRow = $mysql->execute($sql, $param);
            if ($affectedRow > 0) {
                //记录日志
                $log_params[':detail'] = '修改子账号编号为' . $staff_id . '的登录信息';
                $mysql->execute($log_sql, $log_params);
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 401, "msg" => "修改失败"]);
            throw new \PDOException($e);
        }
        //修改会员层级的(删除之前的层级)
        $sql = "DELETE FROM staff_layer WHERE staff_id = :staff_id";
        $param = [":staff_id" => $staff_id];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 402, "msg" => "修改失败"]);
            throw new \PDOException($e);
        }
        //添加新的层级
        if (!empty($level_list)) {
            foreach ($level_list as $k => $v) {
                $level_lists[$k]['layer_id'] = $v;
            }
            $mysql->staff_layer->load($level_lists, ['staff_id' => $staff_id], '');
            //记录日志
            $log_params[':detail'] = '添加子账号编号为' . $staff_id . '添加会员层级id为' . json_encode($level_list);
            $mysql->execute($log_sql, $log_params);
        }
        //修改员工授信额度
        $sqls = "UPDATE staff_credit SET deposit_limit = :deposit_limit, withdraw_limit = :withdraw_limit,notify_status = :notify_status  WHERE staff_id = :staff_id";
        $params = [
            ":staff_id" => $staff_id,
            ":deposit_limit" => $deposit_limit,
            ":withdraw_limit" => $withdraw_limit,
            ":notify_status" => $notify_status,
        ];
        try {
            $mysql->execute($sqls, $params);
            //记录日志
            $log_params[':detail'] = '添加子账号编号为' . $staff_id . "出款额度为" . $withdraw_limit . "入款额度为" . $deposit_limit;
            $mysql->execute($log_sql, $log_params);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "添加失败"]);
            throw new \PDOException($e);
        }
        //删除之前的员工操作的信息
        $sqls = "DELETE FROM staff_permit WHERE staff_id = :staff_id";
        $param = [":staff_id" => $staff_id];
        try {
            $mysql->execute($sqls, $param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "修改失败"]);
            throw new \PDOException($e);
        }
        //添加员工的操作授权信息
        $param = [];
        switch ($StaffGrade) {
                //站长权限
            case 0:
                $param[':owner_permit'] = 0;
                $where = " WHERE owner_permit = :owner_permit";
                break;
                //大股东权限
            case 1:
                $param[':major_permit'] = 0;
                $where = " WHERE major_permit = :major_permit";
                break;
                //股东权限
            case 2:
                $param[':minor_permit'] = 0;
                $where = " WHERE minor_permit = :minor_permit";
                break;
                //总代理权限
            case 3:
                $param[':agent_permit'] = 0;
                $where = " WHERE agent_permit = :agent_permit";
                break;
        }
        $sql = 'SELECT operate_key FROM operate' . $where;
        $operate_list = [];
        foreach ($mysql->query($sql, $param) as $row) {
            $operate_list[] = $row['operate_key'];
        }
        $operate_lists = array_merge($operate_keys, $operate_list);
        if (!empty($operate_lists)) {
            foreach ($operate_lists as $k => $v) {
                $operate_listss[$k]['operate_key'] = $v;
            }
            $mysql->staff_permit->load($operate_listss, ['staff_id' => $staff_id], '');
        }
        //记录日志
        $log_params[':detail'] = '添加子账号编号为' . $staff_id . '操作授权信息';
        $mysql->execute($log_sql, $log_params);
        $context->reply([
            "status" => 200,
            "msg" => "修改成功",
        ]);
    }
}
