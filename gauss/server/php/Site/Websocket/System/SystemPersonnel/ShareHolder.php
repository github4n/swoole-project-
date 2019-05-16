<?php
namespace Site\Websocket\System\SystemPersonnel;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 体系人员列表-股东列表接口 
 * @author： leo
 * @date：   2019-04-08   
 * @link：   System/SystemPersonnel/ShareHolder {"staff_name":"","major_name":""}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime: 交接时间：暂无
 * @param string staff_nam： 用户名 （可不传）
 * @param string major_name： 所属大股东姓名（可不传）
 * @returnData: json;
 */

class ShareHolder extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        $cache = $config->cache_site;
        //判断当前登录账号的权限
        if ($StaffGrade > 1) {
            $context->reply(["status" => 204, "msg" => "没有查看权限"]);
            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("staff_list_minor_select", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }
        $mysql = $config->data_staff;
        //$mysqls = $config->data_user;
        //修改，会员管理-会员列表查询的是report库里面的数据
        $mysqls = $config->data_report;
        $masterId = $context->getInfo("MasterId");
        $staffId = $context->getInfo('StaffId');
        $owner_id = $masterId == 0 ? $staffId : $masterId;
        $data = $context->getData();
        $staff_name = isset($data["staff_name"]) ? $data['staff_name'] : '';
        $major_name = isset($data["major_name"]) ? $data['major_name'] : '';
        $names = '';
        $list = array();
        $param = [];
        if (!empty($staff_name)) {
            $param[':minor_name'] = $staff_name;
            $names = " AND minor_name = :minor_name";
        }
        if (!empty($major_name)) {
            $param[':major_name'] = $major_name;
            $major_name = " AND major_name = :major_name";
        }
        $order = " ORDER BY minor_id DESC";
        //当前登录用户是站长查看所有的股东
        if ($StaffGrade == 0) {
            $sql = "SELECT * FROM staff_struct_minor WHERE 1=1 " . $names . $major_name . $order;
        } elseif ($StaffGrade == 1) {
            //当前用户是大股东查看该大股东的所有股东
            $sql = "SELECT * FROM staff_struct_minor WHERE major_id = :major_id " . $names . $major_name . $order;
            $param[':major_id'] = $owner_id;
        } else {
            $context->reply(["status" => 204, "msg" => "还没有查看的权限"]);
        }
        $list = iterator_to_array($mysql->query($sql, $param));
        unset($param[':limit_start']);
        unset($param[':limit_end']);
        $minor_list = array();
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $staff_info = array();
                $minor_list[$key]['id'] = $val["minor_id"];
                $minor_list[$key]['name'] = $val["minor_name"];
                //登录账号
                $sql = "SELECT staff_key FROM staff_auth WHERE staff_id = :staff_id";
                $param = [":staff_id" => $val["minor_id"]];
                try {
                    foreach ($mysql->query($sql, $param) as $row) {
                        $staff_info = $row;
                    }
                } catch (\PDOException $e) {
                    $context->reply(["status" => 400, "msg" => "获取失败"]);
                    throw new \PDOException($e);
                }
                $minor_list[$key]["staff_key"] = $staff_info["staff_key"];
                $minor_list[$key]["major_name"] = $val["major_name"];
                $minor_list[$key]['level'] = 2;
                $minor_list[$key]['level_name'] = "股东";
                //缓存获取不到已删除的分红设置
                //$info = json_decode($cache->hget("SystemSetting",$val["minor_id"]),true);
                $sql = "SELECT scope_staff_id,grade2_bet_rate,grade2_profit_rate,grade2_fee_rate,grade2_tax_rate
                    FROM dividend_setting WHERE scope_staff_id = :scope_staff_id";
                $param = [
                    ":scope_staff_id" => $val["minor_id"]
                ];
                $info = [];
                foreach ($mysql->query($sql, $param) as $row) {
                    $info = $row;
                }
                if (empty($info)) {
                    //$info = json_decode($cache->hget("SystemSetting",$val["major_id"]),true);
                    //如果没有则获取上级大股东的分红设置
                    $param = [
                        ":scope_staff_id" => $val["major_id"]
                    ];
                    foreach ($mysql->query($sql, $param) as $row) {
                        $info = $row;
                    }
                    //如果上级大股东的股东分红没有设置则获取上级站长的分红设置
                    if (empty($info['grade2_bet_rate']) && empty($info['grade2_profit_rate']) && empty($info['grade2_fee_rate']) && empty($info['grade2_tax_rate'])) {
                        $info = json_decode($cache->hget("SystemSetting", 1), true);
                    }
                }
                $minor_list[$key]["bet_rate"] = floatval($info["grade2_bet_rate"]);
                $minor_list[$key]["profit_rate"] = floatval($info["grade2_profit_rate"]);
                $minor_list[$key]["fee_rate"] = floatval($info["grade2_fee_rate"]);
                $minor_list[$key]["tax_rate"] = floatval($info["grade2_tax_rate"]);
                //查找该股东下的所有会员
                //① 查找该股东下的所有的总代理
                $sql = "SELECT agent_id FROM staff_struct_agent WHERE minor_id = :minor_id";
                $param = [":minor_id" => $val["minor_id"]];
                $agent_row = array();
                foreach ($mysql->query($sql, $param) as $rows) {
                    $agent_row[] = $rows;
                }
                $user_num = 0;
                if (!empty($agent_row)) {
                    foreach ($agent_row as $k => $v) {
                        //$sql = "SELECT user_id FROM user_info WHERE agent_id = :agent_id";
                        //修改，会员管理-会员列表查询的是report库里面的数据
                        //子账号的权限信息
                        $sql = "SELECT user_id FROM user_cumulate WHERE agent_id = :agent_id";
                        $param = [":agent_id" => $v['agent_id']];
                        $user_num += $mysqls->execute($sql, $param);
                    }
                }
                $minor_list[$key]['agent_count'] = $val["agent_count"];
                $minor_list[$key]["user_count"] = $user_num;
            }
        }
        //记录日志
        $sql = 'INSERT INTO operate_log 
            SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'staff_list_minor_select',
            ':detail'  => '查看股东列表',
        ];
        $mysql->execute($sql, $params);
        $context->reply([
            "status" => 200,
            "msg" => "获取成功",
            "list" => $minor_list
        ]);
    }
}
