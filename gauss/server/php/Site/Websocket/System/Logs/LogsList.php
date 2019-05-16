<?php

namespace Site\Websocket\System\Logs;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @edit:  4-29修改sql优化  暂未提交 ---------
 * @description: 操作日志 - 日志列表接口
 * @author： leo
 * @date：   2019-04-08   
 * @link：   System/Logs/LogsList {"staff_key":"","staff_level":"",start_time":"","end_time":""}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:  交接时间：暂无
 * @param string staff_key 用户名 （可不传）
 * @param string staff_level: 用户等级 （可不传）
 * @param string start_time： 开始时间 （可不传）
 * @param string end_time： 结束时间 （可不传）
 * @returnData: json;
 */

class LogsList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo("StaffGrade");
        $auth = json_decode($context->getInfo('StaffAuth'));
        $data = $context->getData();
        $mysqlPublic = $config->data_public;
        $cache = $config->cache_site;
        $staff_name = isset($data["staff_key"]) ? $data["staff_key"] : '';
        $staff_level = isset($data["staff_level"]) ? $data["staff_level"] : '';
        $start_time = isset($data["start_time"]) ? $data["start_time"] : '';
        $end_time = isset($data["end_time"]) ? $data["end_time"] : '';
        $param = [];
        if ($StaffGrade >= 2 && $staff_level < $StaffGrade && $staff_level != 0) {
            $context->reply(["status" => 203, "msg" => "没有查看权限"]);
            return;
        }
        if ($staff_name) {
            $param[':staff_name'] = $staff_name;
            $staff_name = " AND staff_name = :staff_name";
        }
        //用户等级
        $staff_levels = $staff_level;
        if (!empty($staff_level)) {
            $param[':staff_grade'] = $staff_level;
            $staff_level = " AND staff_grade = :staff_grade";
        }
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
            $time = " AND log_time BETWEEN :starts  AND :ends";
        }
        if (!empty($end_time) && empty($start_time)) {
            $end = date("Y-m-d", strtotime($end_time)) . " 00:00:00";
            $param[':log_time'] = strtotime($end);
            $time = " AND log_time <= :log_time ";
        }
        $order = " ORDER BY log_time DESC";
        $limit = " LIMIT 1000";
        $mysql = $config->data_staff;
        if ($StaffGrade == 0) {
            $staff_level_major = '';
            $staff_level_minor = '';
            $staff_level_agent = '';
            //修改不查看站长的操作日志
            $owner_grade = " AND staff_grade != :owner_grade";
            $param[":owner_grade"] = 0;
            //站长
            switch ($staff_levels) {
                    //查询大股东
                case 1:
                    if (!in_array("staff_log_major", $auth)) {
                        $context->reply(["status" => 204, "msg" => "你还没有操作权限"]);
                        return;
                    }
                    break;
                    //查询股东
                case 2:
                    if (!in_array("staff_log_minor", $auth)) {
                        $context->reply(["status" => 204, "msg" => "你还没有操作权限"]);
                        return;
                    }
                    break;
                    //查询总代理
                case 3:
                    if (!in_array("staff_log_agent", $auth)) {
                        $context->reply(["status" => 204, "msg" => "你还没有操作权限"]);
                        return;
                    }
                    break;
                default:
                    //如果查询全部
                    //如果没有查询大股东操作日志的权限
                    if (!in_array("staff_log_major", $auth)) {
                        $param[':staff_grade1'] = 1;
                        $staff_level_major = " AND staff_grade != :staff_grade1";
                    }
                    //如果没有查询股东操作日志的权限
                    if (!in_array("staff_log_minor", $auth)) {
                        $param[':staff_grade2'] = 2;
                        $staff_level_minor = " AND staff_grade != :staff_grade2";
                    }
                    //如果没有查询总代理操作日志的权限
                    if (!in_array("staff_log_agent", $auth)) {
                        $param[':staff_grade3'] = 3;
                        $staff_level_agent = " AND staff_grade != :staff_grade3";
                    }
                    break;
            }
            $where = " WHERE master_id = 0" . $owner_grade . $staff_level_major . $staff_level_minor . $staff_level_agent;
        } elseif ($StaffGrade == 1) {
            //大股东
            switch ($staff_levels) {
                case 1:
                    //查询大股东操作日志
                    $where = " WHERE staff_id = :staff_id";
                    $param[':staff_id'] = $staffId;
                    break;
                case 2:
                    //查询股东操作日志
                    $where = " WHERE leader_id = :leader_id";
                    $param[':leader_id'] = $staffId;
                    break;
                case 3:
                    //搜索总代理操作日志
                    $where = " WHERE leader_id IN (SELECT minor_id FROM staff_struct_minor WHERE major_id = :major_id)";
                    $param[':major_id'] = $staffId;
                    break;
                default:
                    //查看全部
                    $where = " WHERE (leader_id = :leader_id OR leader_id IN (SELECT minor_id FROM staff_struct_minor WHERE major_id = :major_id) OR staff_id = :staff_id)";
                    $param[':leader_id'] = $staffId;
                    $param[':staff_id'] = $staffId;
                    $param[':major_id'] = $staffId;
            }
        } elseif ($StaffGrade == 2) {
            //股东
            switch ($staff_levels) {
                case 2:
                    //搜索股东操作日志
                    $where = " WHERE staff_id = :staff_id";
                    $param[':staff_id'] = $staffId;
                    break;
                case 3:
                    //搜索总代理操作日志
                    $where = " WHERE leader_id = :leader_id";
                    $param[':leader_id'] = $staffId;
                    break;
                default:
                    //搜索全部
                    $where = " WHERE (leader_id = :leader_id OR staff_id = :staff_id)";
                    $param[':leader_id'] = $staffId;
                    $param[':staff_id'] = $staffId;
            }
        } elseif ($StaffGrade == 3) {
            //总代理
            $where = " WHERE staff_id = :staff_id";
            $param[':staff_id'] = $staffId;
        }
        $sql = "SELECT staff_id,client_ip,log_id,staff_name,staff_grade,operate_key,leader_name,detail,log_time 
            FROM operate_log_intact " . $where . $staff_level . $staff_name . $time . $order . $limit;
        $list = iterator_to_array($mysql->query($sql, $param));
        unset($param[':limit_start']);
        unset($param[':limit_end']);
        if (!empty($list)) {
            $sql = "SELECT operate_key,operate_name FROM operate ";
            $operateList = [];
            foreach ($mysql->query($sql) as $value) {
                $operateList += [$value['operate_key'] => $value['operate_name']];
            }
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
                $staff_id = $val['staff_id'];
                $staff_info_sql = "SELECT * FROM staff_info_intact WHERE staff_id = :staff_id";
                $staff_info_sql_param = [":staff_id" => $staff_id];
                $staff_info = iterator_to_array($mysql->query($staff_info_sql, $staff_info_sql_param));
                $list[$key]['staff_key'] = !empty($staff_info) ? $staff_info[0]['staff_key'] : '';
                $operateKey = $val['operate_key'];
                $list[$key]['features'] = $operateList[$operateKey] ? $operateList[$operateKey] : '';
                $address = '';
                $ip = !empty($val["client_ip"]) ? $val["client_ip"] : '';
                if ($ip != '') {
                    $ipTranslation = substr($ip, 0, 8);
                    $ip = long2ip($ip);
                    $ipSaved = json_decode($cache->hget("ipList", $ipTranslation));
                    if (!empty($ipSaved)) {
                        $address = " " . "(" . $ipSaved[0]->region . " " . $ipSaved[0]->city . ")";
                    } else {
                        $ip_sql = "SELECT * FROM ip_address WHERE ip_net = :ip_net";
                        $ip_sql_param = [":ip_net" => $ipTranslation];
                        $ip_result = iterator_to_array($mysqlPublic->query($ip_sql, $ip_sql_param));
                        if (!empty($ip_result)) {
                            $address = " " . "(" . $ip_result[0]['region'] . " " . $ip_result[0]['city'] . ")";
                            $cache->hset("ipList", $ipTranslation, json_encode($ip_result));
                        }
                    }
                }
                $list[$key]['ip'] = $ip . $address;
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'loglist' => $list
        ]);
    }
}
