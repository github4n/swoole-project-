<?php
namespace Site\Websocket\System\Logs;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 操作日志 - ？
 * @author： leo
 * @date：   2019-04-08   
 * @link：   System/Logs/LogsList {"staff_key":"","staff_level":"",start_time":"","end_time":"","page":"","num":"","go_num":""}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:  交接时间：暂无
 * @param string staff_key 用户名 （可不传）
 * @param string staff_level: 用户等级 （可不传）
 * @param string start_time： 开始时间 （可不传）
 * @param string end_time： 结束时间 （可不传）
 * @param string page：  当前页数
 * @param string num： 每页显示的数量
 * @param string go_num： 跳转的页数
 * @returnData: json;
 */

class LogsExport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo("StaffGrade");
        $data = $context->getData();
        $staff_name = $data["staff_name"];
        $staff_level = $data["staff_level"];
        $start_time = $data["start_time"];
        $end_time = $data["end_time"];
        //用户名模糊查询
        if (!empty($staff_name)) {
            $staff_names = " AND staff_key = '" . $staff_name . "' ";
        }
        //用户等级
        if (!empty($staff_level)) {
            $staff_levels = " AND staff_grade = " . $staff_level;
        }
        //只有开始时间的话那就查询那目前时间的数据，如果只有结束时间的话就只查询在从这个时间之前的数据，两个参数都有的话就查询之间的数据
        if (!empty($start_time)) {
            $start = $start_time . " 00:00:00";
            if (!empty($end_time)) {
                $end = $end_time . "23:59:59";
                $time = " AND log_time BETWEEN " . strtotime($start) . "  AND " . strtotime($end);
            } else {
                $time = " AND log_time BETWEEN " . strtotime($start) . "  AND " . time();
            }
        }
        if (!empty($end_time) && empty($start_time)) {
            $end = $end_time . "23:59:59";
            $time = " AND log_time <= " . strtotime($end);
        }
        $mysql = $config->data_staff;
        if ($StaffGrade == 0) {
            $sql = "SELECT log_id,staff_name,staff_grade,leader_name,detail,log_time FROM operate_log_intact WHERE master_id=0 AND staff_grade > 0" . $staff_names . $staff_levels . $time . $limit;
            try {
                $list = array();
                foreach ($mysql->query($sql) as $row) {
                    $list[] = $row;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取失败']);
                throw new \PDOException("sql run error" . $e);
            }
        } elseif ($StaffGrade == 1) {
            $sql = "SELECT log_id,staff_name,staff_grade,leader_name,detail,log_time FROM operate_log_intact WHERE leader_id = :leader_id OR staff_grade=3 AND master_id=0 " . $staff_names . $staff_levels . $time . $limit;
            $param = [":leader_id" => $staffId];
            try {
                $list = array();
                foreach ($mysql->query($sql, $param) as $row) {
                    $list[] = $row;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取失败']);
                throw new \PDOException("sql run error" . $e);
            }
        } elseif ($StaffGrade == 2) {
            $sql = "SELECT log_id,staff_name,staff_grade,leader_name,detail,log_time FROM operate_log_intact WHERE leader_id = :leader_id OR staff_grade=3 AND master_id=0 " . $staff_names . $staff_levels . $time . $limit;
            $param = [":leader_id" => $staffId];
            try {
                $list = array();
                foreach ($mysql->query($sql, $param) as $row) {
                    $list[] = $row;
                }
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '获取失败']);
                throw new \PDOException("sql run error" . $e);
            }
        } else {
            $context->reply(["status" => 220, "msg" => "当前登录是总代理还没有查看权限"]);
            return;
        }
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['log_time'] = date("Y-m-d H:i:s", $val['log_time']);
                if ($val["staff_grade"] == 0) {
                    $list[$key]['staff_grade'] = "站长";
                } elseif ($val["staff_grade"] == 1) {
                    $list[$key]['staff_grade'] = "大股东";
                } elseif ($val["staff_grade"] == 2) {
                    $list[$key]['staff_grade'] = "股东";
                } elseif ($val["staff_grade"] == 3) {
                    $list[$key]['staff_grade'] = "总代理";
                }
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'loglist' => $list]);
    }
}
