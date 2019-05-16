<?php
namespace Site\Websocket\System\SystemReport;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/** 
 * @description: 体系分红报表-派发
 * @author： leo
 * @date：   2019-04-08   
 * @link：   System/SystemReport/Distribute {"staff_id":1,"type":1}
 * @modifyAuthor: 交接负责人：暂无
 * @modifyTime: 交接时间：暂无
 * @param string staff_id  派发id
 * @param string type 类型
 * @returnData: json;
 */
class Distribute extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo("StaffGrade");
        $data = $context->getData();
        $mysql = $config->data_staff;
        $staff_id = isset($data["staff_id"]) ? $data["staff_id"] : '';
        if (!is_array($staff_id)) {
            $context->reply(["status" => 205, "msg" => "参数类型错误"]);
            return;
        }
        foreach ($staff_id as $item) {
            if (!is_numeric($item)) {
                $context->reply(["status" => 204, "msg" => "被派发的员工的人员类型不正确"]);
                return;
            }
            $sql = "SELECT staff_grade FROM staff_info WHERE staff_id=:staff_id";
            $param = [":staff_id" => $item];
            foreach ($mysql->query($sql, $param) as $row) {
                $list = $row;
            }
            if ($StaffGrade >= $list["staff_grade"]) {
                $context->reply(["status" => 205, "msg" => "当前登录账号不能派发高级别的分红"]);
                return;
            }
            $sql = "UPDATE dividend_settle SET deliver_time=:deliver_time WHERE staff_id =:staff_id";
            $param = [":deliver_time" => time(), ":staff_id" => $item];
            try {
                $mysql->execute($sql, $param);
            } catch (\PDOException $e) {
                $context->reply(["status" => 400, "msg" => "派发失败"]);
                throw new \PDOException($e);
            }
        }
        //记录日志
        $context->reply([
            "status" => 200,
            "msg" => "派发成功",
        ]);
    }
}
