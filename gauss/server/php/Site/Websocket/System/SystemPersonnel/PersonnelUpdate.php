<?php
namespace Site\Websocket\System\SystemPersonnel;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系人员列表 - 提交修改体系人员信息接口
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemPersonnel/PersonnelUpdate {"staff_id":1,"staff_key":"125","staff_password":"sdferfre"}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param string staff_id 名称
* @param string staff_key： 登录账号
* @param string staff_password： 密码 
* @returnData: json;
*/

class PersonnelUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 204,"当前账号没有修改权限"]);
            return;
        }
        $data = $context->getData();
        $staff_key = $data["staff_key"];
        $staff_password = $data["staff_password"];
        $staff_id = $data["staff_id"];
        $staffId = $context->getInfo('StaffId');
        if (empty($staff_key)) {
            $context->reply(["status" => 203, "msg" => "登录名不能为空"]);
            return;
        }
        if (empty($staff_password)) {
            $context->reply(["status" => 204, "msg" => "登录密码不能为空"]);
            return;
        }
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 205, "msg" => "员工id类型不正确"]);
            return;
        }
        // 验证规则
        $preg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,40}$/';
        if (!preg_match($preg, $staff_password)) {
            $context->reply(['status' => 208, 'msg' => '密码为6-40位的数字和字母的组合']);
            return;
        }
        $mysql = $config->data_staff;
        //查找用户基本信息
        $sqls = "SELECT * FROM staff_info WHERE staff_id = :staff_id";
        $params = [":staff_id" => $staff_id];
        $info = array();
        try{
            foreach ($mysql->query($sqls, $params) as $row) {
                $info = $row;
            }
        }catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "修改失败"]) ;
            throw new \PDOException('select staff_info sql error'.$e);
        }
        if ($StaffGrade >= $info['staff_grade']) {
            $context->reply(["status" => 206, "msg" => "等级不够没有操作的权限"]);
            return;
        }
        $sql = "UPDATE staff_auth  SET password_hash = :password_hash  WHERE staff_id = :staff_id AND staff_key = :staff_key";
        $param = [
            ":password_hash" => $staff_password, 
            ":staff_id" => $staff_id,
            ":staff_key" => $staff_key
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "修改失败"]);
            throw new \PDOException('update staff_auth sql run error'.$e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':operate_key' => 'staff_list_agent_update',
            ':client_ip' => ip2long($context->getClientAddr()),
            ':detail'  => '修改编号为'.$staff_id."身份为".$info['staff_grade']."密码信息",
        ];
        $mysql->execute($sql, $params);
        $context->reply(["status" => 200, "msg" => "修改成功"]);
    }
}