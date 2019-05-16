<?php
namespace Site\Websocket\Staff;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/** 
 * @description: 修改密码接口
 * @author： leo
 * @date：   2019-04-08   
 * @link：   System/Staff/ModifyPassWord {"old_password":"old123","new_password":"new123","confirm_password":"new123"}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:  交接时间：暂无
 * @param string old_password: 旧密码
 * @param string new_password: 新密码
 * @param string confirm_password: 确认密码
 * @returnData: json;
 */

class ModifyPassWord extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $old_pw = $data["old_password"];
        $new_pw = $data["new_password"];
        $confirm_pw = $data["confirm_password"];
        if (empty($old_pw)) {
            $context->reply(["status" => 201, "msg" => "请输入旧密码"]);
            return;
        }
        if (empty($new_pw)) {
            $context->reply(["status" => 201, "msg" => "新密码不能为空"]);
            return;
        }
        if (empty($confirm_pw)) {
            $context->reply(["status" => 201, "msg" => "请再次确认密码"]);
            return;
        }
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/';
        if (!preg_match($preg, $new_pw)) {
            $context->reply(['status' => 206, 'msg' => '密码需6-20位字符,可包含英文和数字']);
            return;
        }
        if ($new_pw !== $confirm_pw) {
            $context->reply(["status" => 202, "msg" => "两次密码输入不一致,请重新输入"]);
            return;
        }
        //检测旧密码是否输入正确
        $staff_key  = $context->getInfo("StaffKey");
        $mysql = $config->data_staff;
        $sql = 'CALL staff_auth_verify(:staff_key, :password)';
        $params = [':staff_key' => $staff_key, ':password' => $old_pw];
        foreach ($mysql->query($sql, $params) as $row) {
            $old_password = $row;
        }
        if (empty($old_password)) {
            $context->reply(['status' => 202, 'msg' => '旧密码输入错误,请重新输入']);
            return;
        }
        //存入新密码
        try {
            $sql = "UPDATE staff_auth SET password_hash = :password_hash WHERE staff_id = :staff_id";
            $params = [
                ':password_hash' => $new_pw,
                ':staff_id' => $context->getInfo('StaffId')
            ];
            $res = $mysql->execute($sql, $params);
            if ($res == 0) {
                $context->reply(['status' => 400, 'msg' => '修改失败']);
            } else {
                //记录退出日志
                $sql = "INSERT INTO operate_log SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail,log_time = :log_time,client_ip = :client_ip";
                $params = [
                    ':staff_id' => $context->getInfo('StaffId'),
                    ':operate_key' => 'self_password',
                    ':client_ip' => ip2long($context->getClientAddr()),
                    ':detail'  => "修改密码",
                    ':log_time' => time(),
                ];
                $mysql->execute($sql, $params);
                $context->reply(['status' => 200, 'msg' => '修改成功']);
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            new \PDOException($e);
        }
    }
}
