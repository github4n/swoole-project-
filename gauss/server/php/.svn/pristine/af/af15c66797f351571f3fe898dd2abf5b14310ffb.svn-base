<?php
namespace Site\Websocket\Account\Logs;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 操作日志 - ？
 * @author： leo
 * @date：   2019-04-08   
 * @link：   Account/Logs/LogsExport {"page":"1","num":"10","go_num":"","staff_name":"","start_time":"","end_time":""}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime:  交接时间：暂无
 * @param string staff_name： 用户名 （可不传）
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
        $masterId = $context->getInfo('MasterId');
        $staff_id = $context->getInfo('StaffId');
        $master_id = $masterId == 0 ? $staff_id : $masterId;
        $data = $context->getData();
        $staff_name = $data["staff_name"];
        $start_time = $data["start_time"];
        $end_time = $data["end_time"];
        $staff_names = '';
        $time = '';
        //用户名模糊查询
        if (!empty($staff_name)) {
            $staff_names = " AND staff_name like '%" . $staff_name . "%' ";
        }
        //只有开始时间的话那就查询那目前时间的数据，如果只有结束时间的话就只查询在从这个时间之前的数据，两个参数都有的话就查询之间的数据
        if (!empty($start_time)) {
            $start = $start_time . " 00:00:00";
            if (!empty($end_time)) {
                $end = $end_time . "23:59:59";
                $time = "AND log_time BETWEEN " . strtotime($start) . "  AND " . strtotime($end);
            } else {
                $time = "AND log_time BETWEEN " . strtotime($start) . "  AND " . time();
            }
        }
        if (!empty($end_time) && empty($start_time)) {
            $end = $end_time . "23:59:59";
            $time = "AND log_time <= " . strtotime($end);
        }
        $mysql = $config->data_staff;
        try {
            $list = array();
            $sql = "SELECT log_id, staff_name, leader_name, detail, log_time 
                FROM operate_log_intact 
                WHERE master_id = :master_id" . $staff_names . $time;
            $param = [":master_id" => $master_id];
            foreach ($mysql->query($sql, $param) as $row) {
                $list[] = $row;
            }
            if (!empty($list)) {
                foreach ($list as $key => $val) {
                    $list[$key]['log_time'] = date("Y-m-d H:i:s", $val['log_time']);
                }
            }
            $context->reply(['status' => 200, 'msg' => '获取成功', 'loglist' => $list]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException("sql run error" . $e);
        }
    }
}
