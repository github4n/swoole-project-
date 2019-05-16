<?php
namespace Site\Websocket\System\SystemSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
* @description: 体系分红设置-删除大股东分红的比例
* @author： leo
* @date：   2019-04-08   
* @link：   System/SystemSetting/DividendMajorDelete {"major_id":"105"}
* @modifyAuthor: 交接负责人：暂无
* @modifyTime: 交接时间：暂无
* @param int   major_id: 大股东id
* @returnData: json;
*/

class DividendMajorDelete extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo("StaffGrade");
        if ($StaffGrade != 0) {
            $context->reply(["status" => 203,"当前账号没有修改权限"]);
            return;
        }
		//验证是否有操作权限
		$auth = json_decode($context->getInfo('StaffAuth'));
		if (!in_array("staff_dividend_delete",$auth)) {
			$context->reply(["status" => 203,"msg" => "你还没有操作权限"]);
			return;
		}
        $staffId = $context->getInfo('StaffId');
        $data = $context->getData();
        $mysql = $config->data_staff;
        $staff_id = $data["major_id"];
        if (!is_numeric($staff_id)) {
            $context->reply(["status" => 202,"msg" => "新增的股东编号类型不正确"]);
            return;
        }
        $sql = "SELECT staff_name FROM staff_info_intact WHERE staff_id=:staff_id AND staff_grade = :staff_grade AND master_id = :master_id" ;
        $param = [
            ":staff_id" => $staff_id,
            ":staff_grade" => 1,
            ":master_id" => 0
        ];
        $info = array();
        foreach ($mysql->query($sql,$param) as $row) {
            $info = $row;
        }
        if (empty($info)) {
            $context->reply(["status" => 204,"msg" => "当前提交的编号不是大股东"]);
            return;
        }
        $sql = "DELETE FROM dividend_setting WHERE scope_staff_id=:scope_staff_id";
        $param = [":scope_staff_id" => $staff_id];
        try {
            $mysql->execute($sql,$param);
        } catch (\PDOException $e) {
            $context->reply(["status" => 400,"msg" => "删除失败"]);
            throw new \PDOException($e);
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id'  =>  $staffId,
            ':client_ip'  =>  ip2long($context->getClientAddr()),
            ':operate_key'  =>  'staff_dividend_delete',
            ':detail'  => '删除大股东编号为'.$staff_id.'分红信息',
        ];
        $mysql->execute($sql, $params);
        $context->reply(["status" => 200,"msg" => "删除成功"]);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('System/Setting', [],time(),7);
    }
}