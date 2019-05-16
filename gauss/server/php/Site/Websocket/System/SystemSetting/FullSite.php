<?php
namespace Site\Websocket\System\SystemSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 体系分红设置-全站分红比例设置列表
 * @author： leo
 * @date：   2019-04-08   
 * @link：   System/SystemSetting/FullSite
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime: 交接时间：暂无
 * @returnData: json;
 */

class FullSite extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 203, "msg" => "当前账号没有查看权限"]);
            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("staff_dividend_select", $auth)) {
            $context->reply(["status" => 203, "msg" => "你还没有操作权限"]);
            return;
        }
        //判断权限
        //获取整站的分红比例
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo("MasterId");
        $scope_staff_id =  $MasterId == 0 ? $staffId : $MasterId;
        if ($StaffGrade == 0) {
            $sql = "SELECT * FROM dividend_setting WHERE scope_staff_id=:scope_staff_id";
            $param = [":scope_staff_id" => $scope_staff_id];
        } else {
            $context->reply(["status" => 202, "msg" => "你还没有查看的权限"]);
            return;
        }
        $list = array();
        $mysql = $config->data_staff;
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $list = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 400, "msg" => "查看失败"]);
            throw new \PDOException($e);
        }
        $rate_list = array();
        if (!empty($list)) {
            $rate_list['major_bet'] = floatval($list['grade1_bet_rate']);
            $rate_list['major_profit'] = floatval($list['grade1_profit_rate']);
            $rate_list['major_fee'] = floatval($list['grade1_fee_rate']);
            $rate_list['major_tax'] = floatval($list['grade1_tax_rate']);
            $rate_list['minor_bet'] = floatval($list['grade2_bet_rate']);
            $rate_list['minor_profit'] = floatval($list['grade2_profit_rate']);
            $rate_list['minor_fee'] = floatval($list['grade2_fee_rate']);
            $rate_list['minor_tax'] = floatval($list['grade2_tax_rate']);
            $rate_list['agent_bet'] = floatval($list['grade3_bet_rate']);
            $rate_list['agent_profit'] = floatval($list['grade3_profit_rate']);
            $rate_list['agent_fee'] = floatval($list['grade3_fee_rate']);
            $rate_list['agent_tax'] = floatval($list['grade3_tax_rate']);
        }
        $context->reply([
            "status" => 200,
            "msg" => "获取成功",
            "data" => $rate_list
        ]);
    }
}
