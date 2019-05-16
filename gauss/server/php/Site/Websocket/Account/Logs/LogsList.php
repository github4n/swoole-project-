<?php

namespace Site\Websocket\Account\Logs;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 操作日志 - 日志列表接口
 * @author： leo
 * @date：   2019-04-08   
 * @link：   Account/Logs/LogsList {"staff_name":"","start_time":"","end_time":""}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:  交接时间：暂无
 * @param string staff_name： 用户名 （可不传）
 * @param string start_time： 开始时间 （可不传）
 * @param string end_time： 结束时间 （可不传）
 * @returnData: json;
 */

class LogsList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("slave_log", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }
        $masterId = $context->getInfo('MasterId');
        $cache = $config->cache_site;
        $public_mysql = $config->data_public;
        $staff_id = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo("StaffGrade");
        $master_id = $masterId == 0 ? $staff_id : $masterId;
        $data = $context->getData();
        $staff_name = isset($data["staff_name"]) ? $data["staff_name"] : '';
        $start_time = isset($data["start_time"]) ? $data["start_time"] : '';
        $end_time = isset($data["end_time"]) ? $data["end_time"] : '';
        $staff_names = '';
        $time = '';
        $param = [];
        //前端时间传参格式错误
        if ($start_time == "undefined" || $end_time == "undefined") {
            $start_time = '';
            $end_time = '';
        }
        if (!empty($staff_name)) {
            $param[':staff_name'] = $staff_name;
            $staff_names = " AND staff_name = :staff_name ";
        }
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
        $order = " ORDER BY log_id DESC";
        $limit = " LIMIT 1000";
        $mysql = $config->data_staff;
        if ($StaffGrade == 0) {
            //修改子账号日志不查看管理下级的体系管理人员的操作日志及体系管理人员的子账号的操作日志
            $sql = "SELECT staff_id,log_id,staff_name,leader_name,detail,log_time,client_ip,operate_key 
                FROM operate_log_intact 
                WHERE staff_grade = 0" . $staff_names . $time . $order . $limit;
            //如果是站长子账号则不可查看站长操作日志
            if ($masterId != 0) {
                $sql = "SELECT staff_id,log_id,staff_name,leader_name,detail,log_time,client_ip,operate_key 
                    FROM operate_log_intact 
                    WHERE staff_grade = 0 AND master_id = 1" . $staff_names . $time . $order . $limit;
            }
        } else {
            $sql = "SELECT staff_id,log_id,staff_name,leader_name,detail,log_time,client_ip,operate_key 
                FROM operate_log_intact 
                WHERE master_id = :master_id" . $staff_names . $time . $order . $limit;
            $param[':master_id'] = $master_id;
        }
        try {
            $list = array();
            foreach ($mysql->query($sql, $param) as $row) {
                $list[] = $row;
            }
            if (!empty($list)) {
                $sql = "SELECT operate_key,operate_name FROM operate ";
                $operateList = [];
                foreach ($mysql->query($sql) as $value) {
                    $operateList += [$value['operate_key'] => $value['operate_name']];
                }
                foreach ($list as $key => $val) {
                    $staff_id = $val['staff_id'];
                    $list[$key]['log_time'] = date("Y-m-d H:i:s", $val['log_time']);
                    $operateKey = $val['operate_key'];
                    //代理层级被修改：broker_layer被去除
                    //会员管理-修改会员密码：update 被去除
                    if ($operateKey != 'broker_layer' || $operateKey != 'update') {
                        $list[$key]['features'] = $operateList[$operateKey];
                        $ip = $val['client_ip'];
                        if ($ip != 0) {
                            $ipTranslation = substr($ip, 0, 8);
                            $ip = long2ip($ip);
                            $ipSaved = json_decode($cache->hget("ipList", $ipTranslation));
                            if (!empty($ipSaved)) {
                                $ip .= " " . "(" . $ipSaved[0]->region . " " . $ipSaved[0]->city . ")";
                            } else {
                                $ip_sql = "SELECT * FROM ip_address WHERE ip_net=:ip_net ";
                                $param = [':ip_net' => $ipTranslation];
                                $ip_result = iterator_to_array($public_mysql->query($ip_sql, $param));
                                if (!empty($ip_result)) {
                                    $ip .= " " . "(" . $ip_result[0]['region'] . " " . $ip_result[0]['city'] . ")";
                                    $cache->hset("ipList", $ipTranslation, json_encode($ip_result));
                                }
                            }
                        }
                        $list[$key]['ip'] = $ip;
                    }
                }
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'loglist' => $list
        ]);
    }
}
