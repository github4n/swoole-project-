<?php
namespace Site\Websocket\System\SystemPersonnel;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系人员列表 - 获取体系人员信息接口
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemPersonnel/PersonnelList {}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @returnData: json;
*/

class PersonnelList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        $staffId = $context->getInfo('StaffId');
        $mysql = $config->data_staff;
        $major_list = array();
        $minor_list = array();
        if ($StaffGrade == 0) {
            //搜索该站长的大股东
            $sql = "SELECT staff_id,staff_name FROM staff_info WHERE staff_grade = 1 AND master_id = 0";
            //股东
            $sqls = "SELECT staff_id,staff_name FROM staff_info WHERE staff_grade = 2 AND master_id = 0";
            try {
                foreach ($mysql->query($sql) as $row) {
                    $major_list[] = $row;
                }
            } catch (\PDOException $e) {
                $context->reply(["status" => 400,"msg" => "获取失败"]);
                throw new \PDOException($e);
            }
            try {
                foreach ($mysql->query($sqls) as $rows) {
                    $minor_list[] = $rows;
                }
            } catch (\PDOException $e) {
                $context->reply(["status" => 400,"msg" => "获取失败"]);
                throw new \PDOException($e);
            }
        } elseif ($StaffGrade == 1) {
            //查找自己名下的所有的股东
            $sql = "SELECT staff_id,staff_name FROM staff_info WHERE leader_id = :leader_id AND staff_grade = 2 AND master_id = 0";
            $param = [":leader_id" => $staffId];
            try {
                foreach ($mysql->query($sql,$param) as $rows) {
                    $minor_list[] = $rows;
                }
            } catch (\PDOException $e) {
                $context->reply(["status" => 400,"msg" => "获取失败"]);
                throw new \PDOException($e);
            }
        } else {
            $context->reply(["status" => 202,"msg" => "当前账号没有访问的权限"]);
            return;
        }
        $context->reply([
            "status" => 200,
            "msg" => "获取成功",
            "major_list" => $major_list,
            "minor_list" => $minor_list,
        ]);
    }
}