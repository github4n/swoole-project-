<?php
namespace Site\Websocket\System\SystemSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系分红设置-修改大股东体系分红比例
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemSetting/DividendMajorUpdate {"major_id":105,"major_bet":"1","major_profit":"1","major_fee":"1","major_tax":"1","minor_bet":"1","minor_profit":"1","minor_fee":"1","minor_tax":"1","agent_bet":"1","agent_profit":"1","agent_fee":"1","agent_tax":"1"}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param int    major_id: 大股东id
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

class DividendMajorUpdate extends CheckLogin
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
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_staff;
        $staff_id = $data["major_id"];
        $major_bet = $data["major_bet"];
        $major_bet = !empty($data["major_bet"]) ? $data["major_bet"] : 0;
        $major_profit = !empty($data["major_profit"]) ? $data["major_profit"] : 0;
        $major_fee = !empty($data["major_fee"]) ? $data["major_fee"] : 0;
        $major_tax = !empty($data["major_tax"]) ? $data["major_tax"] : 0;
        $minor_bet = !empty($data["minor_bet"]) ? $data["minor_bet"] : 0;
        $minor_profit = !empty($data["minor_profit"]) ? $data["minor_profit"] : 0;
        $minor_fee = !empty($data["minor_fee"]) ? $data["minor_fee"] : 0;
        $minor_tax = !empty($data["minor_tax"]) ? $data["minor_tax"] : 0;
        $agent_bet = !empty($data["agent_bet"]) ? $data["agent_bet"] : 0;
        $agent_profit = !empty($data["agent_profit"]) ? $data["agent_profit"] : 0;
        $agent_fee = !empty($data["agent_fee"]) ? $data["agent_fee"] : 0;
        $agent_tax = !empty($data["agent_tax"]) ? $data["agent_tax"] : 0;
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 202,"msg" => "新增的股东编号类型不正确"]);
            return;
        }
        if (!is_numeric($major_bet) && !is_numeric($major_profit) && !is_numeric($major_fee) && !is_numeric($minor_bet) && !is_numeric($minor_profit) && !is_numeric($minor_fee) && !is_numeric($agent_bet) && !is_numeric($agent_profit) && !is_numeric($agent_fee) && !is_numeric($major_tax) && !is_numeric($minor_tax) && !is_numeric($agent_tax)) {
            $context->reply(["status" => 204,"msg" => "请填写正确的比例"]);
            return;
        }
        if ($major_tax<0 || $minor_tax<0 || $agent_tax<0 || $major_bet<0 || $major_profit<0 || $major_fee<0 || $minor_bet<0 || $minor_profit<0 || $minor_fee<0 || $agent_bet<0 || $agent_profit<0 || $agent_fee<0 || $major_tax>100 || $minor_tax>100 || $agent_tax>100 || $major_bet>100 || $major_profit>100 || $major_fee>100 || $minor_bet>100 || $minor_profit>100 || $minor_fee>100 || $agent_bet>100 || $agent_profit>100 || $agent_fee>100){
            $context->reply(["status" => 205,"msg" => "请填写正确的比例"]);
            return;
        }
        $sql = "SELECT staff_name FROM staff_info_intact WHERE staff_id=:staff_id AND staff_grade = :staff_grade AND master_id = :master_id";
        $param = [":staff_id" => $staff_id];
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
            $context->reply(["status" => 204,"msg" => "请选择大股东"]);
            return;
        }
        $sql = "UPDATE dividend_setting SET grade1_bet_rate=:major_bet,grade1_profit_rate=:major_profit,grade1_fee_rate=:major_fee,grade1_tax_rate=:major_tax,grade2_bet_rate=:minor_bet,grade2_profit_rate=:minor_profit,grade2_fee_rate=:minor_fee,grade2_tax_rate=:minor_tax,grade3_bet_rate=:agent_bet,grade3_profit_rate=:agent_profit,grade3_fee_rate=:agent_fee,grade3_tax_rate=:agent_tax WHERE scope_staff_id=:scope_staff_id";
        $param = [
            ":scope_staff_id"  => $staff_id,
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
            $mysql->execute($sql,$param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400,"msg" => "设置失败"]);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id'  =>  $staffId,
            ':client_ip'  =>  ip2long($context->getClientAddr()),
            ':operate_key'  =>  'staff_dividend_update',
            ':detail'  => '修改大股东编号为'.$staff_id.'的分红信息',
        ];
        $mysql->execute($sql, $params);
        $context->reply(["status" => 200,"msg" => "设置成功"]);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('System/Setting', [],time(),9);
    }
}