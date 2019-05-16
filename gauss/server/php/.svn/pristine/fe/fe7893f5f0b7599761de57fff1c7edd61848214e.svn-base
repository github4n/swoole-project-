<?php

namespace Site\Websocket\System\SystemSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;
/** 
* @description: 体系分红设置-修改全站设置分红比例
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemSetting/FullSiteUpdate {"major_bet":"1","major_profit":"1","major_fee":"1","major_tax":"1","minor_bet":"1","minor_profit":"1","minor_fee":"1","minor_tax":"1","agent_bet":"1","agent_profit":"1","agent_fee":"1","agent_tax":"1"}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param string major_bet: 大股东打码分红比例
* @param string major_profit： 大股东损益分红比例
* @param string major_fee： 大股东行政费比例
* @param string major_tax： 大股东平台费比例
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

class FullSiteUpdate extends CheckLogin 
{
    public function onReceiveLogined(Context $context, Config $config) 
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 203, "当前账号没有修改权限"]);
            return;
        }
		//验证是否有操作权限
		$auth = json_decode($context->getInfo('StaffAuth'));
		if (!in_array("staff_dividend_update",$auth)) {
			$context->reply(["status"=>203,"msg"=>"你还没有操作权限"]);
			return;
		}
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo("MasterId");
        $scope_staff_id = $MasterId == 0 ? $staffId : $MasterId;

        $data = $context->getData();
        $major_bet = isset($data["major_bet"]) ? $data["major_bet"] : '';
        $major_profit = isset($data["major_profit"]) ? $data["major_profit"] : '';
        $major_fee = isset($data["major_fee"]) ? $data["major_fee"] : '';
        $major_tax = isset($data["major_tax"]) ? $data["major_tax"] : '';
        $minor_bet = isset($data["minor_bet"]) ? $data["minor_bet"] : '';
        $minor_profit = isset($data["minor_profit"]) ? $data["minor_profit"] : '';
        $minor_fee = isset($data["minor_fee"]) ? $data["minor_fee"] : '';
        $minor_tax = isset($data["minor_tax"]) ? $data["minor_tax"] : '';
        $agent_bet = isset($data["agent_bet"]) ? $data["agent_bet"] : '';
        $agent_profit = isset($data["agent_profit"]) ? $data["agent_profit"] : '';
        $agent_fee = isset($data["agent_fee"]) ? $data["agent_fee"] : '';
        $agent_tax = isset($data["agent_tax"]) ? $data["agent_tax"] : '';
        if (empty($major_tax) || empty($minor_tax) || empty($agent_tax) || empty($major_bet) || empty($major_profit) || empty($major_fee) || empty($minor_bet) || empty($minor_profit) || empty($minor_fee) || empty($agent_bet) || empty($agent_profit) || empty($agent_fee)) {
            $context->reply(["status" => 204, "msg" => "请输入完整参数"]);
            return;
        }

        if (!is_numeric($major_tax) || !is_numeric($minor_tax) || !is_numeric($agent_tax) || !is_numeric($major_bet) || !is_numeric($major_profit) || !is_numeric($major_fee) || !is_numeric($minor_bet) || !is_numeric($minor_profit) || !is_numeric($minor_fee) || !is_numeric($agent_bet) || !is_numeric($agent_profit) || !is_numeric($agent_fee)) {
            $context->reply(["status" => 204, "msg" => "请填写比例"]);
            return;
        }
        $mysql = $config->data_staff;
        $sql = "UPDATE dividend_setting SET grade1_bet_rate=:major_bet,grade1_profit_rate=:major_profit,grade1_fee_rate=:major_fee,grade1_tax_rate=:major_tax,grade2_bet_rate=:minor_bet,grade2_profit_rate=:minor_profit,grade2_fee_rate=:minor_fee,grade2_tax_rate=:minor_tax,grade3_bet_rate=:agent_bet,grade3_profit_rate=:agent_profit,grade3_fee_rate=:agent_fee,grade3_tax_rate=:agent_tax WHERE scope_staff_id=:scope_staff_id";
        $param = [
            ":scope_staff_id" => $scope_staff_id,
            ":major_bet" => $major_bet,
            ":major_profit" => $major_profit,
            ":major_fee" => $major_fee,
            ":major_tax" => $major_tax,
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
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "设置失败"]);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'staff_dividend_update',
            ':detail' => '修改全站分红设置信息',
        ];
        $mysql->execute($sql, $params);
        $context->reply(["status" => 200, "msg" => "设置成功"]);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('System/Setting', [],time(),9);
    }
}
