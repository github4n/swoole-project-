<?php
namespace Site\Websocket\System\SystemSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系分红设置-搜索会员账号返回登录账号
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemSetting/StaffSearch {"staff_key":"name","type":"1"}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param int   staff_key: 登录账号
* @param int   type: 搜索类型（1-大股东、2-股东、3-总代理）
* @returnData: json;
*/

class StaffSearch extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 203, "msg" => "当前账号没有权限"]);
            return;
        }
        $data = $context->getData();
        $staff_key = isset($data["staff_key"]) ? $data["staff_key"] : '';
        if (empty($staff_key)) {
            $context->reply(["status" => 204, "msg" => "账号不能为空"]);
            return;
        }
        $mysql = $config->data_staff;
        $type = isset($data["type"]) ? $data["type"] : '';
        $sql = "SELECT staff_id,staff_name FROM staff_info_intact WHERE staff_key = :staff_key AND staff_grade = :staff_grade AND master_id = :master_id";
        $param = [
            ":staff_key" => $staff_key,
            ":master_id" => 0
        ];
        if ($type == 1) {
            //搜索大股东
            $param[':staff_grade'] = 1;
        } elseif ($type == 2) {
            //搜索股东
            $param[':staff_grade'] = 2;
        } elseif ($type == 3) {
            //搜索总代理
            $param[':staff_grade'] = 3;
        } else {
            $context->reply(["status" => 205,"搜索不到该账号"]);
            return;
        }
        $info = array();
        foreach ($mysql->query($sql, $param) as $row) {
            $info = $row;
        }
        if (empty($info)) {
            $context->reply(["status" => 204, "msg" => "搜索不到该账号"]);
            return;
        }
        $context->reply(["status" => 200, "msg" => "搜索成功","info" => $info]);
    }
}
