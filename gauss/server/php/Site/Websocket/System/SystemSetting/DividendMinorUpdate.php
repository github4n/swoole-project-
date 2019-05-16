<?php
namespace Site\Websocket\System\SystemSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系分红设置-修改股东体系分红比例
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemSetting/DividendMinorUpdate {"minor_id":105,"minor_bet":"1","minor_profit":"1","minor_fee":"1","minor_tax":"1","agent_bet":"1","agent_profit":"1","agent_fee":"1","agent_tax":"1"}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param int    minor: 大股东id
* @param string minor_bet: 股东打码分红比例
* @param string minor_profit： 股东损益分红比例
* @param string minor_fee： 股东行政费比例
* @param string minor_tax： 股东平台费比例
* @param string agent_bet: 总代理打码分红比例
* @param string agent_profit： 总代理损益分红比例
* @param string agent_fee： 总代理行政费比例
* @param string agent_tax： 总代理平台费比例
* @returnData: json;
*/

class DividendMinorUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 203,"当前账号没有权限"]);
            return;
        }
		//验证是否有操作权限
		$auth = json_decode($context->getInfo('StaffAuth'));
		if (!in_array("staff_dividend_update",$auth)) {
			$context->reply(["status" => 203,"msg" => "你还没有操作权限"]);
			return;
		}
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_staff;
        $staff_id = $data["minor_id"];
        $minor_bet = !empty($data["minor_bet"]) ? $data["minor_bet"] : 0;
        $minor_profit = !empty($data["minor_profit"]) ? $data["minor_profit"] : 0;
        $minor_fee = !empty($data["minor_fee"]) ? $data["minor_fee"] : 0;;
        $minor_tax = !empty($data["minor_tax"]) ? $data["minor_tax"] : 0;;
        $agent_bet = !empty($data["agent_bet"]) ? $data["agent_bet"] : 0;;
        $agent_profit = !empty($data["agent_profit"]) ? $data["agent_profit"] : 0;;
        $agent_fee = !empty($data["agent_fee"]) ? $data["agent_fee"] : 0;;
        $agent_tax = !empty($data["agent_tax"]) ? $data["agent_tax"] : 0;;
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 202,"msg" => "请选择股东"]);
            return;
        }
        if (!is_numeric($minor_tax) &&!is_numeric($agent_tax) &&!is_numeric($minor_bet) && !is_numeric($minor_profit) && !is_numeric($minor_fee) && !is_numeric($agent_bet) && !is_numeric($agent_profit) && !is_numeric($agent_fee)) {
            $context->reply(["status" => 204,"msg" => "请填写比例"]);
            return;
        }
        if ($minor_bet<0 || $minor_profit<0 || $minor_fee<0 ||  $minor_tax<0 || $minor_tax>100 ||$agent_tax>100 || $minor_bet>100 || $minor_profit>100 || $minor_fee>100 || $agent_bet>100 || $agent_profit>100 || $agent_fee>100) {
            $context->reply(["status" => 205,"msg" => "请填写正确的比例"]);
            return;
        }
        $sql = "SELECT staff_name FROM staff_info_intact WHERE staff_id=:staff_id AND staff_grade = :staff_grade AND master_id = :master_id" ;
        $param = [
            ":staff_id" => $staff_id,
            ":staff_grade" => 2,
            ":master_id" => 0
        ];
        $info = array();
        foreach ($mysql->query($sql,$param) as $row) {
            $info = $row;
        }
        if (empty($info)) {
            $context->reply(["status" => 204,"msg" => "请选择股东"]);
            return;
        }
        $sql = "UPDATE dividend_setting SET grade2_bet_rate=:minor_bet,grade2_profit_rate=:minor_profit,grade2_fee_rate=:minor_fee,grade2_tax_rate=:minor_tax,grade3_bet_rate=:agent_bet,grade3_profit_rate=:agent_profit,grade3_fee_rate=:agent_fee,grade3_tax_rate=:agent_tax WHERE scope_staff_id=:scope_staff_id";
        $param = [
            ":scope_staff_id" => $staff_id,
            ":minor_bet" => $minor_bet,
            ":minor_profit" => $minor_profit,
            ":minor_fee" => $minor_fee,
            ":minor_tax" => $minor_tax,
            ":agent_bet" => $agent_bet,
            ":agent_profit" => $agent_profit,
            ":agent_fee" => $agent_fee,
            ":agent_tax" => $agent_tax,
        ];
        try {
            $mysql->execute($sql,$param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400,"msg" => "设置失败"]);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'staff_dividend_update',
            ':detail'  => '修改股东编号为'.$staff_id.'的分红信息',
        ];
        $mysql->execute($sql, $params);
        $context->reply(["status" => 200,"msg" => "设置成功"]);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('System/Setting', [],time(),9);
    }
}