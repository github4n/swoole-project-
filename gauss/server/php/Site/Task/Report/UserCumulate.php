<?php
/**
 * UserCumulate.php
 *
 * @description   用户累计日报插入数据任务
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

class UserCumulate implements IHandler
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
        $sqls = "INSERT INTO user_cumulate SET user_id = :user_id, user_key = :user_key, user_name = :user_name, layer_id = :layer_id, layer_name = :layer_name, major_id = :major_id, major_name = :major_name, minor_id = :minor_id, minor_name = :minor_name, agent_id = :agent_id, agent_name = :agent_name, broker_1_id = :broker_1_id,  broker_1_key = :broker_1_key,  broker_1_name = :broker_1_name,  broker_2_id = :broker_2_id, broker_2_key = :broker_2_key, broker_2_name = :broker_2_name, broker_3_id = :broker_3_id, broker_3_key = :broker_3_key, broker_3_name = :broker_3_name, register_invite = :register_invite, register_time = :register_time, register_ip = :register_ip, register_device = :register_device, login_time = :login_time, login_ip = :login_ip, login_device = :login_device, phone_number = :phone_number, invite_code = :invite_code, money = :money, deposit_count = :deposit_count, deposit_amount = :deposit_amount, withdraw_count = :withdraw_count, withdraw_amount = :withdraw_amount, subsidy =:subsidy, brokerage = :brokerage, bet_all = :bet_all, bet_lottery = :bet_lottery, bet_video = :bet_video, bet_game = :bet_game, bet_sports = :bet_sports, bet_cards = :bet_cards, bonus_all = :bonus_all, bonus_lottery = :bonus_lottery, bonus_video = :bonus_video,  bonus_game = :bonus_game,  bonus_sports = :bonus_sports, bonus_cards = :bonus_cards, profit_all = :profit_all, profit_lottery = :profit_lottery,  profit_video = :profit_video,  profit_game = :profit_game, profit_sports = :profit_sports,  profit_cards = :profit_cards ";
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
            ":register_invite" => 0,
            ":register_time" => $list["register_time"],
            ":register_ip" => $list["register_ip"],
            ":register_device" => $list["register_device"],
            ":login_time" => 0,
            ":login_ip" => 0,
            ":login_device" => 0,
            ":phone_number" => 0,
            ":invite_code" => 0,
            ":money" => 0,
            ":deposit_count" => 0,
            ":deposit_amount" => 0,
            ":withdraw_count" => 0,
            ":withdraw_amount" => 0,
            ":subsidy" => 0,
            ":brokerage" => 0,
            ":bet_all" => 0,
            ":bet_lottery" => 0,
            ":bet_video" => 0,
            ":bet_game" => 0,
            ":bet_sports" => 0,
            ":bet_cards" => 0,
            ":bonus_all" => 0,
            ":bonus_lottery" => 0,
            ":bonus_video" => 0,
            ":bonus_game" => 0,
            ":bonus_sports" => 0,
            ":bonus_cards" => 0,
            ":profit_all" => 0,
            ":profit_lottery" => 0,
            ":profit_video" => 0,
            ":profit_game" => 0,
            ":profit_sports" => 0,
            ":profit_cards" => 0,
        ];
        try{
            $report_mysql->execute($sqls,$params);
        }catch (\PDOException $e){
            throw new \PDOException($e);
        }
    }
}
