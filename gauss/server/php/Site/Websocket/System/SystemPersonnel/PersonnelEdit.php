<?php
namespace Site\Websocket\System\SystemPersonnel;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系人员列表 - 获取修改体系人员信息接口
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemPersonnel/PersonnelEdit {"staff_id":3}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param string staff_id： 会员id
* @returnData: json;
*/

class PersonnelEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 204, "msg" => "当前账号没有修改权限"]);
            return;
        }
        $data = $context->getData();
        $staff_id = $data["staff_id"];
        $staff_info = array();
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 202, "msg" => "要删除人员的唯一值正确"]);
            return;
        }
        $mysql = $config->data_staff;
        //查找用户的基本信息
        $sql = "SELECT staff_name,staff_id,staff_key,staff_grade FROM staff_info_intact WHERE staff_id = :staff_id";
        $param= [":staff_id" => $staff_id];
        try {
            foreach ($mysql->query($sql,$param) as $row) {
                $staff_info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "查询失败"]);
            throw new \PDOException('select staff_info  sql run error'.$e);
        }
        if (empty($staff_info)) {
            $context->reply(["status" => 203, "msg" => "提交的账户的有误"]);
            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        //修改大股东操作权限
        if ($staff_info["staff_grade"] == 1) {
            if (!in_array("staff_list_major_update",$auth)) {
                $context->reply(["status" => 204, "msg" => "你还没有修改权限"]);
                return;
            }
        } elseif ($staff_info["staff_grade"] == 2) {
            //修改股东操作权限
            if (!in_array("staff_list_minor_update",$auth)) {
                $context->reply(["status" => 204, "msg" => "你还没有修改权限"]);
                return;
            }
        } elseif ($staff_info["staff_grade"] == 3) {
            //修改总代理操作权限
            if (!in_array("staff_list_agent_update",$auth)) {
                $context->reply(["status" => 204, "msg" => "你还没有修改权限"]);
                return;
            }
        }
        if ($StaffGrade >= $staff_info["staff_grade"]) {
            $context->reply(["status" => 204, "msg" => "当前登录账号等级不够"]);
            return;
        }
        $context->reply(["status" => 200, "msg" => "修改成功", "info" => $staff_info]);
    }
}