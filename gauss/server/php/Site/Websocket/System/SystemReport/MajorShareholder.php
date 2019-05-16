<?php

namespace Site\Websocket\System\SystemReport;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 体系分红报表 - 大股东列表接口 
 * @author： leo
 * @date：   2019-04-08   
 * @link：   System/SystemReport/MajorShareholder {"staff_name":"","start_time":"","end_time":"","is_settle":1}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime: 交接时间：暂无
 * @param string staff_nam： 用户名 （可不传）
 * @param int    is_settle: 派发(1为已结算，0为待结算)
 * @param string start_time： 开始时间 （可不传）
 * @param string end_time： 结束时间 （可不传）
 * @returnData: json;
 */

class MajorShareholder extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade > 0) {
            $context->reply(["status" => 208, "msg" => "当前登录账号没有访问的权限"]);
            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("staff_report_major", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }
        $data = $context->getData();
        $mysql = $config->data_staff;
        $mysqlReport = $config->data_report;
        $cache = $config->cache_site;
        $staff_name = isset($data["staff_name"]) ? $data["staff_name"] : '';
        $start_time = isset($data["start_time"]) ? $data["start_time"] : '';
        $end_time = isset($data["end_time"]) ? $data["end_time"] : '';
        $isSettle = isset($data["is_settle"]) ? $data["is_settle"] : '';
        $time = "";
        $param = [];
        //如果搜索数据为时间+未结算则返回空数组
        if (!empty($start_time) && !empty($end_time) && empty($isSettle)) {
            $context->reply([
                "status" => 200,
                "msg" => "获取成功",
                "total" => 0,
                "total_page" => 0,
                "list" => []
            ]);
            return;
        }
        if (empty($isSettle)) {
            $isSettle = 0; //未结算
        } else {
            if (!is_numeric($isSettle)) {
                $context->reply(["status" => 300, "msg" => "请选择结算状态"]);
                return;
            } else {
                $isSettle = 1;
            }
        }
        if (!empty($staff_name)) {
            $param[':major_name'] = $staff_name;
            $staff_name = " AND major_name = :major_name";
        }
        $major_list = [];
        if ($isSettle == 0) { //未结算
            //去除已结算的数据
            $where = " AND major_id NOT IN (SELECT major_id FROM dividend_settle_major WHERE 1=1) ";
            $order = " ORDER BY major_id DESC ";
            $sql = "select * from staff_struct_major where 1=1" . $where . $staff_name . $order;
            $majorList = iterator_to_array($mysql->query($sql, $param));
            foreach ($majorList as $key => $val) {
                //获取大股东的分红设置
                //$rate = json_decode($cache->hget("SystemSetting", $val["major_id"]), true);
                //获取不到缓存中已删除的分红设置
                $sql = "SELECT scope_staff_id,grade1_bet_rate,grade1_profit_rate,grade1_fee_rate,grade1_tax_rate,grade2_bet_rate,grade2_profit_rate,grade2_fee_rate,grade2_tax_rate,grade3_bet_rate,grade3_profit_rate,grade3_fee_rate,grade3_tax_rate 
                    FROM dividend_setting WHERE scope_staff_id = :scope_staff_id";
                $param = [
                    ":scope_staff_id" => $val["major_id"]
                ];
                $rate = [];
                foreach ($mysql->query($sql, $param) as $row) {
                    $rate = $row;
                }
                if (empty($rate)) {
                    //如果为空则获取站长的分红设置
                    $rate = json_decode($cache->hget("SystemSetting", 1), true);
                }
                //计算投注额
                $sql = "SELECT sum(wager_amount) as bet_all FROM monthly_staff 
                    WHERE major_id = :major_id AND monthly = :monthly";
                $param = [
                    ":major_id" => $val["major_id"],
                    ":monthly" => intval(date("Ym", strtotime("today")))
                ];
                foreach ($mysqlReport->query($sql, $param) as $row) {
                    $bet_all = $row["bet_all"];
                }

                if (empty($bet_all)) {
                    $bet_all = "0.00";
                }
                $major = [
                    "staff_name" => $val["major_name"],
                    "staff_id" => $val["major_id"],
                    "major_grade" => "大股东",
                    "bet_amount" => $this->intercept_num($bet_all),
                    "bet_rate" => floatval($rate["grade1_bet_rate"]),
                    "profit_amount" => "0.00",
                    "profit_rate" => floatval($rate["grade1_profit_rate"]),
                    "fee_rate" => floatval($rate["grade1_fee_rate"]),
                    "tax_rate" => floatval($rate["grade1_tax_rate"]),
                    "dividend" => "0.00",
                    "settle_time" => "",
                    "is_settle" => 0,
                ];
                $major_list[] = $major;
            }
        }
        if ($isSettle == 1) { //已结算
            $time = '';
            //只有开始时间的话那就查询那目前时间的数据，如果只有结束时间的话就只查询在从这个时间之前的数据，两个参数都有的话就查询之间的数据
            if (!empty($start_time)) {
                $start = date("Y-m-d", strtotime($start_time)) . " 00:00:00";
                $param[':starts'] = strtotime($start);
                if (!empty($end_time)) {
                    $end = date("Y-m-d", strtotime($end_time)) . " 23:59:59";
                    $param[':ends'] = strtotime($end);
                } else {
                    $param[':ends'] = time();
                }
                $time = " AND settle_time BETWEEN :starts  AND :ends";
            }
            if (!empty($end_time) && empty($start_time)) {
                $end = date("Y-m-d", strtotime($end_time)) . " 00:00:00";
                $param[':settle_time'] = strtotime($end);
                $time = " AND settle_time <= :settle_time ";
            }
            $order = ' ORDER BY settle_time DESC ';
            $limit = ' LIMIT 1000';
            $sql = "SELECT * FROM dividend_settle_major WHERE 1=1" . $staff_name . $time . $order . $limit;
            $lists = array();
            try {
                foreach ($mysql->query($sql, $param) as $rows) {
                    $lists[] = $rows;
                }
            } catch (\PDOException $e) {
                $context->reply(["status" => 400, "msg" => "获取失败"]);
                throw new \PDOException($e);
            }
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    $major_list[$key]["staff_name"] = $val['major_name'];
                    $major_list[$key]["staff_id"] = $val['major_id'];
                    $major_list[$key]["major_grade"] = "大股东";
                    $major_list[$key]["bet_amount"] = $this->intercept_num($val["bet_amount"]);
                    $major_list[$key]["bet_rate"] = floatval($val["bet_rate"]);
                    $major_list[$key]["profit_amount"] = $this->intercept_num($val["profit_amount"]);
                    $major_list[$key]["profit_rate"] = floatval($val["profit_rate"]);
                    $major_list[$key]["fee_rate"] = floatval($val["fee_rate"]);
                    $major_list[$key]["tax_rate"] = floatval($val["tax_rate"]);
                    $major_list[$key]["dividend"] = $this->intercept_num($val["dividend_result"]);
                    $major_list[$key]["settle_time"] = date("Y-m-d H:i:s", $val["settle_time"]);
                    $major_list[$key]["is_settle"] = 1;
                }
            }
        }
        //记录日志
        $sql = 'INSERT INTO operate_log 
            SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'staff_report_major',
            ':detail' => '查看大股东分红',
        ];
        $mysql->execute($sql, $params);
        $context->reply([
            "status" => 200,
            "msg" => "获取成功",
            "list" => $major_list
        ]);
    }
}
