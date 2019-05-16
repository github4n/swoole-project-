<?php

namespace Site\Websocket\Account\Staff;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 子账号管理 - 员工列表接口
 * @author： leo
 * @date：   2019-04-08   
 * @link：   Account/Staff/StaffList {"staff_name":"name","staff_ip":"110"}
 * @modifyTime:  交接时间：暂无
 * @param string staff_name： 用户名 （可不传）
 * @param string staff_ip： ip地址 （可不传）
 * @returnData: json;
 */

class StaffList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("slave_list_select", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }
        $data = $context->getData();
        $mysql = $config->data_staff;
        $mysqlPublic = $config->data_public;
        $cache = $config->cache_site;
        $staff_name = isset($data["staff_name"]) ? $data["staff_name"] : '';
        $staff_ip = isset($data["staff_ip"]) ? $data["staff_ip"] : '';
        $staffId = $context->getInfo('StaffId');
        $masterId = $context->getInfo('MasterId');
        $MasterId = $masterId == 0 ? $staffId : $masterId;
        $list = array();
        $params = [];
        if ($staff_name != '') {
            $params[':staff_key'] = $staff_name;
            $staff_name = " AND staff_key = :staff_key";
        }
        if ($staff_ip != '') {
            $staff_ip = ip2long($staff_ip);
            if ($staff_ip == false) {
                $context->reply([
                    'status' => 405,
                    'msg' => 'Ip地址格式不正确',
                    'list' => [],
                    'total' => 0,
                    'total_page' => 0
                ]);
                return;
            }
            $params[':login_ip'] = $staff_ip;
            $staff_ip = " AND login_ip = :login_ip";
        }
        $order = " ORDER BY staff_id DESC";
        $sql = "SELECT staff_id,staff_name,staff_key,add_time,login_ip,deposit_limit,withdraw_limit,login_time 
            FROM staff_info_intact 
            WHERE master_id = :master_id" . $staff_name . $staff_ip . $order;
        $params[":master_id"] = $MasterId;
        $staff_list = iterator_to_array($mysql->query($sql, $params));
        if (!empty($staff_list)) {
            foreach ($staff_list as $key => $val) {
                $list[$key]['staff_id'] = $val["staff_id"];
                $list[$key]['add_time'] = !empty($val["add_time"]) ? date("Y-m-d H:i:s", $val["add_time"]) : '';
                $list[$key]['login_time'] = !empty($val["login_time"]) ? date("Y-m-d H:i:s", $val["login_time"]) : '';
                $list[$key]['login_ip'] = !empty($val["login_ip"]) ? long2ip($val["login_ip"]) : '';
                $list[$key]['staff_name'] = $val['staff_name'];
                $list[$key]['staff_key'] = $val['staff_key'];
                $list[$key]['deposit_limit'] = $this->intercept_num($val['deposit_limit']);
                $list[$key]['withdraw_limit'] = $this->intercept_num($val['withdraw_limit']);
                $address = '';
                $ip = !empty($val["login_ip"]) ? $val["login_ip"] : '';
                if ($ip != '') {
                    $ipTranslation = substr($ip, 0, 8);
                    $ip = long2ip($ip);
                    $ipSaved = json_decode($cache->hget("ipList", $ipTranslation));
                    if (!empty($ipSaved)) {
                        $address = $ipSaved[0]->region . " " . $ipSaved[0]->city;
                    } else {
                        $ip_sql = "SELECT * FROM ip_address WHERE ip_net='$ipTranslation' ";
                        $ip_result = iterator_to_array($mysqlPublic->query($ip_sql));
                        if (!empty($ip_result)) {
                            $address = $ip_result[0]['region'] . " " . $ip_result[0]['city'];
                            $cache->hset("ipList", $ipTranslation, json_encode($ip_result));
                        }
                    }
                }
                $list[$key]['address'] = $address;
            }
        }
        $context->reply([
            'status' => 200,
            "msg" => "获取成功",
            "list" => $list
        ]);
    }
}
