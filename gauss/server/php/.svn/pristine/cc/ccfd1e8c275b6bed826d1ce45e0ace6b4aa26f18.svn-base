<?php
namespace Site\Websocket\Account\Staff;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 子账号管理 - 获取员工修改信息页接口
* @author： leo
* @date：   2019-04-08   
* @link：   Account/Staff/StaffEdit {"staff_id":302}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime:  交接时间：暂无
* @param int  staff_id 员工id 
* @returnData: json;
*/

class StaffEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("slave_list_update",$auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }
        $data = $context->getData();
        if (empty($data)) {
            $context->reply(["status" => 211, "msg" => "参数错误"]);
            return;
        }
        $staff_id = isset($data["staff_id"]) ? $data["staff_id"] : '';
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 203, "msg" => "参数错误"]);
            return;
        }
        $info = array();
        $operate_info = array();
        $mysql = $config->data_staff;
        $user_mysql = $config->data_user;
        $cache = $config->cache_site;
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo("MasterId");
        $StaffGrade = $context->getInfo('StaffGrade');
        //获取当前修改的账号的基本信息
        $sql = "SELECT staff_name,staff_key,deposit_limit,withdraw_limit,notify_status,layer_id_list  FROM staff_info_intact  WHERE staff_id=:staff_id";
        $param = [":staff_id" => $staff_id];
        $info = [];
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 402, "msg" => "获取失败"]);
            throw new \PDOException($e);
        }
        //添加修改04-09
        if (!empty($info["layer_id_list"])) {
            $info["layer_id_list"] = json_decode($info["layer_id_list"], true);
        }
        //需要修改的用户的操作权限
        $sql = "SELECT operate_key FROM staff_permit WHERE staff_id = :staff_id";
        try {
            foreach ($mysql->query($sql, $param) as $row) {
                $operate_info[] = $row['operate_key'];
            }
        } catch (\PDOException $e) {
            $context->reply(["status" => 401, "msg" => "获取失败"]);
            throw new \PDOException($e);
        }
        if (!empty($MasterId)) {
            //当前账号管理会员的信息
            $sql = "SELECT layer_id FROM staff_layer WHERE staff_id=:staff_id";
            $param = [":staff_id" => $staffId];
            foreach ($mysql->query($sql, $param) as $rows) {
                $layer_list[] = $rows;
            }
            $user_layer = array();
            $agent_layer = array();
            if (!empty($layer_list)) {
                foreach ($layer_list as $key => $val) {
                    $sql = "SELECT layer_id,layer_name,layer_type FROM layer_info WHERE layer_id=:layer_id";
                    $param = [":layer_id" => $val["layer_id"]];
                    foreach ($user_mysql->query($sql, $param) as $row) {
                        if ($row["layer_type"] < 3) {
                            $user_layer[$key]["layer_name"] = $row["layer_name"];
                            $user_layer[$key]["layer_id"] = $row["layer_id"];
                        }
                        if ($row["layer_type"] > 100) {
                            $agent_layer[$key]["layer_name"] = $row["layer_name"];
                            $agent_layer[$key]["layer_id"] = $row["layer_id"];
                        }
                    }
                }
            }
            sort($agent_layer,1);
            sort($user_layer,1);
        } else{
            //会员层级
            $user_layer = json_decode($cache->hget("LayerList","userLayer"));
            //代理层级
            $agent_layer = json_decode($cache->hget("LayerList","agentLayer"));
        }
        //权限分配
        $sql = "SELECT * FROM staff_permit WHERE staff_id = :staff_id";
        $param = [":staff_id" => $staffId];
        foreach ($mysql->query($sql, $param) as $row) {
            $operate[] = $row;
        }
        //sql优化
        $params = [];
        $where = '';
        //如果不是站长登录，则不传体系管理体系人员列表的修改删除key和会员列表的修改key
        $del_key = [
            'staff_list_major_update', 
            'staff_list_major_delete', 
            'staff_list_minor_update', 
            'staff_list_minor_delete', 
            'staff_list_agent_update', 
            'staff_list_agent_delete', 
            'user_list_update'
        ];
        switch ($StaffGrade) {
            //站长权限
            case 0:
                $params[':owner_permit'] = 1;
                $where = " AND operate_key IN (SELECT operate_key FROM operate WHERE owner_permit = :owner_permit)";
                break;
            //大股东权限
            case 1:
                $params[':major_permit'] = 1;
                //大股东不能添加总代理
                $del_key[] = 'staff_list_agent_insert';
                $params[':del_key'] = $del_key;
                $where = " AND operate_key IN (SELECT operate_key FROM operate WHERE major_permit = :major_permit AND operate_key NOT IN :del_key)";
                break;
            //股东权限
            case 2:
                $params[':minor_permit'] = 1;
                $params[':del_key'] = $del_key;
                $where = " AND operate_key IN (SELECT operate_key FROM operate WHERE minor_permit = :minor_permit AND operate_key NOT IN :del_key)";
                break;
            //总代理权限
            case 3:
                $params[':agent_permit'] = 1;
                //总代理不能查看经营报表
                $del_key[] = 'report_money';
                $params[':del_key'] = $del_key;
                $where = " AND operate_key IN (SELECT operate_key FROM operate WHERE agent_permit = :agent_permit AND operate_key NOT IN :del_key)";
                break;
        }
        //权限分配
        $sql = "SELECT * FROM staff_permit WHERE staff_id = :staff_id" . $where;
        $params[":staff_id"] = $staffId;
        $operate = iterator_to_array($mysql->query($sql, $params));
        if (!empty($operate)) {
            foreach ($operate as $k => $v) {
                $sql = "SELECT operate_name,operate_key FROM operate WHERE operate_key=:operate_key";
                $param = [":operate_key" => $v["operate_key"]];
                $operates =[];
                foreach ($mysql->query($sql, $param) as $rows) {
                    $operates = $rows;
                }
                if(!empty($operates)){
                    $operate_list[$k]["operate_key"] = $operates["operate_key"];
                    $operate_list[$k]["operate_name"] = $operates["operate_name"];
                }
            }
        }
        $info["operate_list"] = $operate_info;
        sort($operate_list,1) ;
        $context->reply([
            "status" => 200,
            "msg" => "获取成功",
            "info" => $info,
            "user_layer"  =>  $user_layer,
            "agent_layer"  =>  $agent_layer,
            "operate_list"  =>  $operate_list
        ]);
    }
}