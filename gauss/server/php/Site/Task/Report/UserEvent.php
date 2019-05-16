<?php
/**
 * UserEvent.php
 *
 * @description   用户事件日报插入数据任务
 * @Author  nathan
 * @date  2019-04-07
 * @links  Initialize.php
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
namespace Site\Task\Report;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class UserEvent implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['user_id' => $user_id] = $context->getData();
        $user_mysql = $config->data_user;
        $staff_mysql = $config->data_staff;
        $report_mysql = $config->data_report;
        $sql = "SELECT * FROM user_info_intact WHERE user_id = :user_id";
        $param = [":user_id"=>$user_id];
        foreach ($user_mysql->query($sql,$param) as $row){
            $list = $row;
        }
        $sql = "SELECT * FROM staff_struct_agent WHERE agent_id=:agent_id";
        $param = [":agent_id"=>$list["agent_id"]];
        foreach ($staff_mysql->query($sql,$param) as $row){
            $agent = $row;
        }
        $sqls = "INSERT INTO user_event SET user_id = :user_id, user_key = :user_key, user_name = :user_name, layer_id = :layer_id, layer_name = :layer_name, major_id = :major_id, major_name = :major_name, minor_id = :minor_id, minor_name = :minor_name, agent_id = :agent_id, agent_name = :agent_name, broker_1_id = :broker_1_id,  broker_1_key = :broker_1_key,  broker_1_name = :broker_1_name,  broker_2_id = :broker_2_id, broker_2_key = :broker_2_key, broker_2_name = :broker_2_name, broker_3_id = :broker_3_id, broker_3_key = :broker_3_key, broker_3_name = :broker_3_name, register_time = :register_time";
        $params = [
            ":user_id" => intval($user_id),
            ":user_key" => $list["user_key"],
            ":user_name" => 0,
            ":layer_id" => intval($list["layer_id"]),
            ":layer_name" => $list["layer_name"],
            ":major_id" => intval($agent["major_id"]),
            ":major_name" => $agent["major_name"],
            ":minor_id" => intval($agent["minor_id"]),
            ":minor_name" => $agent["minor_name"],
            ":agent_id" => intval($list["agent_id"]),
            ":agent_name" => $agent["agent_name"],
            ":broker_1_id" => 0,
            ":broker_1_key" => 0,
            ":broker_1_name" => 0,
            ":broker_2_id" => 0,
            ":broker_2_key" => 0,
            ":broker_2_name" => 0,
            ":broker_3_id" => 0,
            ":broker_3_key" => 0,
            ":broker_3_name" => 0,
            ":register_time" => $list["register_time"],
        ];
        try{
            $report_mysql->execute($sqls,$params);
        }catch (\PDOException $e){
            throw new \PDOException($e);
        }
    }
}
