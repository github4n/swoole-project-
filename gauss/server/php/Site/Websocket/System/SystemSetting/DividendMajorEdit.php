<?php
namespace Site\Websocket\System\SystemSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系分红设置-编辑大股东分红比例
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemSetting/DividendMajorEdit {"major_id":"107"}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param int   major_id: 大股东id
* @returnData: json;
*/

class DividendMajorEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 203,"当前账号没有修改权限"]);
            return;
        }
		//验证是否有操作权限
		$auth = json_decode($context->getInfo('StaffAuth'));
		if (!in_array("staff_dividend_update",$auth)) {
			$context->reply(["status" => 203,"msg" => "你还没有操作权限"]);
			return;
        }
        $cache = $config->cache_site;
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_staff;
        $staff_id = $data["major_id"];
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 202,"msg" => "新增的股东编号类型不正确"]);
            return;
        }
        $sql = "SELECT staff_name,staff_key FROM staff_info_intact WHERE staff_id=:staff_id AND staff_grade = :staff_grade AND master_id = :master_id" ;
        $param = [
            ":staff_id" => $staff_id,
            ":staff_grade" => 1,
            ":master_id" => 0
        ];
        $info = array();
        foreach ($mysql->query($sql,$param) as $row) {
            $info = $row;
        }
        if (empty($info)) {
            $context->reply(["status" => 204,"msg" => "当前提交的编号不是大股东"]);
            return;
        }
        $sql = "SELECT * FROM dividend_setting WHERE scope_staff_id=:scope_staff_id";
        $param = [":scope_staff_id" => $staff_id];
        $infos = array();
        $list = array();
        foreach ($mysql->query($sql,$param) as $rows) {
            $infos = $rows;
        }
        if (!empty($infos)) {
            //如果下级的比例未被设置则显示为全站的比例
            $fh_info = json_decode($cache->hget("SystemSetting",1),true);

            $list["staff_key"] = $info["staff_key"];
            $list["staff_name"] = $info["staff_name"];
            $list["major_bet"] = (!empty($infos["grade1_bet_rate"]) ? $infos["grade1_bet_rate"] : $fh_info['grade1_bet_rate'])."%";
            $list["major_profit"] = (!empty($infos["grade1_profit_rate"]) ? $infos["grade1_profit_rate"] : $fh_info['grade1_profit_rate'])."%";
            $list["major_fee"] = (!empty($infos["grade1_fee_rate"]) ? $infos["grade1_fee_rate"] : $fh_info['grade1_fee_rate'])."%";
            $list["major_tax"] = (!empty($infos["grade1_tax_rate"]) ? $infos["grade1_tax_rate"] : $fh_info['grade1_tax_rate'])."%";
            $list["minor_bet"] = (!empty($infos["grade2_bet_rate"]) ? $infos["grade2_bet_rate"] : $fh_info['grade2_bet_rate'])."%";
            $list["minor_profit"] = (!empty($infos["grade2_profit_rate"]) ? $infos["grade2_profit_rate"] : $fh_info['grade2_profit_rate'])."%";
            $list["minor_fee"] = (!empty($infos["grade2_fee_rate"]) ? $infos["grade2_fee_rate"] : $fh_info['grade2_fee_rate'])."%";
            $list["minor_tax"] = (!empty($infos["grade2_tax_rate"]) ? $infos["grade2_tax_rate"] : $fh_info['grade2_tax_rate'])."%";
            $list["agent_bet"] = (!empty($infos["grade3_bet_rate"]) ? $infos["grade3_bet_rate"] : $fh_info['grade3_bet_rate'])."%";
            $list["agent_profit"] = (!empty($infos["grade3_profit_rate"]) ? $infos["grade3_profit_rate"] : $fh_info['grade3_profit_rate'])."%";
            $list["agent_fee"] = (!empty($infos["grade3_fee_rate"]) ? $infos["grade3_fee_rate"] : $fh_info['grade3_fee_rate'])."%";
            $list["agent_tax"] = (!empty($infos["grade3_tax_rate"]) ? $infos["grade3_tax_rate"] : $fh_info['grade3_tax_rate'])."%";
        }
        $context->reply(["status" => 200, "msg" => "获取成功","info" => $list]);
    }
}
