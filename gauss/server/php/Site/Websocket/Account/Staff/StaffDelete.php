<?php
namespace Site\Websocket\Account\Staff;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 子账号管理 - 员工删除接口
* @author： leo
* @date：   2019-04-08   
* @link：   Account/Staff/StaffDelete {"list":["302"]}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime:  交接时间：暂无
* @param array  list： 员工id列表 
* @returnData: json;
*/

class StaffDelete extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("slave_list_delete", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
            return;
        }
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_staff;
        $info = array();
        $ids = isset($data["list"]) ? $data["list"] : '';
        if (empty($ids) || gettype($ids) != "array") {
            $context->reply(["status" => 400, "msg" => "参数不正确"]);
            return;
        }
        foreach ($ids as $item) {
            $sql = "SELECT * FROM staff_session WHERE staff_id = :staff_id";
            $param = [":staff_id" => $item];
            try {
                foreach ($mysql->query($sql,$param) as $row) {
                    $info = $row;
                }
                if (!empty($info)) {
                    $context->reply([
                        "status" => 203,
                        "msg" => "该账号正在登录请勿删除"
                    ]);
                    return;
                }
            } catch (\PDOException $e) {
                $context->reply(["status" => 401,"msg" => "删除失败"]);
                throw new \PDOException($e);
            }
            $sql = "DELETE FROM staff_auth WHERE staff_id = :staff_id";
            $sql1 = "DELETE FROM staff_info WHERE staff_id = :staff_id ";
            $param = [":staff_id" => $item];
            try {
                $mysql->execute($sql, $param);
                $mysql->execute($sql1, $param);
            } catch (\PDOException $e) {
                $context->reply(["status" => 401, "msg" => "删除失败"]);
                throw new \PDOException($e);
            }
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'slave_list_delete',
            ':detail' => '删除子账号'.json_encode($ids),
        ];
        $mysql->execute($sql, $params);
        $context->reply([
            "status" => 200,
            "msg" => "删除成功"
        ]);
    }
}