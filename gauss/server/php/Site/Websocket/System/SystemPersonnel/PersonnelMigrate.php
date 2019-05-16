<?php
namespace Site\Websocket\System\SystemPersonnel;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系人员列表 - 批量转移接口
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemPersonnel/PersonnelMigrate {"staff_id":303,"ids":["314","316","315"],"type":1}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param string staff_id 转移的人员id
* @param string ids  转移的人员id列表
* @param string type 类型
* @returnData: json;
*/

class PersonnelMigrate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //判断用户是否有这个操作权限
        /*$auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("staff_minor_select",$auth)) {
            $context->reply(["status" => 201, "msg" => "你还没有操作权限"]);
            return;
        } */
        $StaffGrade = $context->getInfo("StaffGrade");
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $ids = isset($data["ids"]) ? $data["ids"] : '';
        $idss = json_encode($ids);
        $staff_id = isset($data["staff_id"]) ? $data["staff_id"] : '';
        $type = isset($data["type"]) ? $data["type"] : '';
        $mysql = $config->data_staff;
		if (empty($ids)) {
			$context->reply(["status" => 203, "msg" => "请选择被转移的人员"]);
			return;
		}
		if (!is_array($ids)) {
			$context->reply(["status" => 220, "msg" => "转移人传递参数格式有误"]);
			return;
		}
        if (empty($staff_id)) {
            $context->reply(["status" => 202, "msg" => "要转移的人员不能为空"]);
            return;
        }
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 212, "msg" => "大股东参数类型错误"]);
            return;
        }
        if ($StaffGrade == 0) {
            //站长转移股东给大股东
             if ($type == 1) {
                 foreach ($ids as $item) {
                     if (!is_numeric($item)) {
                         $context->reply(["status" => 205, "msg" => "被转移的参数类型不正确"]);
                         return;
                     }
                     $sql = "UPDATE staff_info SET leader_id = :leader_id WHERE staff_id = :staff_id AND master_id = 0";
                     $param = [":leader_id" => $staff_id,":staff_id" => $item];
                     $sqls = "UPDATE staff_struct_minor SET major_id = :major_id WHERE minor_id = :minor_id";
                     $params = [":major_id" => $staff_id,":minor_id" => $item];
                     try{
                         $mysql->execute($sql,$param);
                         $mysql->execute($sqls,$params);
                     }catch (\PDOException $e) {
                         $context->reply(["status" => 401, "msg" => "转移失败"]);
                         throw new \PDOException($e);
                     }
                 }
                 //记录日志
                 $sql = 'INSERT INTO operate_log SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
                 $params = [
                     ':staff_id'  =>  $staffId,
                     ':client_ip'  =>  ip2long($context->getClientAddr()),
                     ':operate_key'  =>  'staff_minor_migrate',
                     ':detail'  => '站长将股东编号为（'.$idss.'）转移给了编号为'.$staff_id.'的大股东',
                 ];
                 $mysql->execute($sql, $params);
             } elseif ($type == 2) {
                 //站长转移总代理给股东
                 foreach ($ids as $item) {
                     if (!is_numeric($item)) {
                         $context->reply(["status" => 205, "msg" => "被转移的参数类型不正确"]);
                         return;
                     }
                     $sql = "UPDATE staff_info SET leader_id = :leader_id WHERE staff_id = :staff_id AND master_id=0";
                     $param = [":leader_id" => $staff_id,":staff_id" => $item];
                     $sqls= "UPDATE staff_struct_agent SET minor_id = :minor_id WHERE agent_id = :agent_id";
                     $params = [":minor_id" => $staff_id,":agent_id" => $item];
                     try{
                         $mysql->execute($sql, $param);
                         $mysql->execute($sqls, $params);
                     }catch (\PDOException $e) {
                         $context->reply(["status" => 402, "msg" => "转移失败"]);
                         throw new \PDOException($e);
                     }
                 }
                 //记录日志
                 $sql = 'INSERT INTO operate_log SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail,client_ip= :client_ip';
                 $params = [
                     ':staff_id'  =>  $staffId,
                     ':client_ip'  =>  ip2long($context->getClientAddr()),
                     ':operate_key'  =>  'staff_minor_migrate',
                     ':detail'  => '站长将代理编号为（'.$idss.'）转移给了编号为'.$staff_id.'的股东',
                 ];
                 $mysql->execute($sql, $params);
             } else {
                 $context->reply(["status" => 206, "msg" => "请选择转移股东还是总代理"]);
                 return;
             }
        } elseif ($StaffGrade == 1) {
            //大股东转移总代理
            foreach ($ids as $item) {
                if (!is_numeric($item)) {
                    $context->reply(["status" => 205, "msg" => "被转移的参数类型不正确"]);
                    return;
                }
                $sql = "UPDATE staff_info SET leader_id = :leader_id WHERE staff_id = :staff_id AND master_id = 0";
                $param = [":leader_id" => $staff_id,":staff_id" => $item];
                $sqls= "UPDATE staff_struct_agent SET minor_id = :minor_id WHERE agent_id = :agent_id";
                $params = [":minor_id" => $staff_id,":agent_id" => $item];
                try{
                    $mysql->execute($sql, $param);
                    $mysql->execute($sqls, $params);
                }catch(\PDOException $e) {
                    $context->reply(["status" => 400, "msg" => "转移失败"]);
                    throw new \PDOException($e);
                }
            }
            //记录日志
            $sql = 'INSERT INTO operate_log SET staff_id = :staff_id, operate_key = :operate_key, detail = :detail, client_ip = :client_ip';
            $params = [
                ':staff_id'  =>  $staffId,
                ':client_ip'  =>  ip2long($context->getClientAddr()),
                ':operate_key'  =>  'staff_minor_migrate',
                ':detail'  => '大股东将代理编号为（'.$idss.'）转移给了编号为'.$staff_id.'的股东',
            ];
            $mysql->execute($sql, $params);
        } else {
            $context->reply(["status" => 204, "msg" => "当前登录账号没有操作的权限"]);
            return;
        }
        $context->reply(["status" => 200, "msg" => "转移成功"]);
    }
}