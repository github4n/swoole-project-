<?php
namespace Site\Websocket\AgentRebate\AgentLayer;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * ManualList.php
 *
 * @description   代理层级设置--代理层级列表
 * @Author  rose
 * @date  2019-04-07
 * @links 参数：AgentRebate/AgentLayer/ManualList {}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */

class ManualList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array("broker_layer_select",$auth)){
            $context->reply(["status"=>202,"msg"=>"你还没有操作权限"]);
            return;
        }
        $staffId = $context->getInfo('StaffId');
        if($StaffGrade != 0){
            $context->reply(["status"=>203,"msg"=>"当前账号没有操作权限权限"]);
            return;
        }
        $mysql = $config->data_user;
        $sql = "SELECT * FROM layer_info WHERE layer_type = 101";
        $total_sql = "SELECT layer_id FROM layer_info WHERE layer_type = 101";

        $list = array();
        try{
            foreach ($mysql->query($sql) as $rows){
                $list[] = $rows;
            }
            $total = $mysql->execute($total_sql);

        }catch (\PDOException $e){
            $context->reply(["status"=>204,"msg"=>"获取失败"]);
            throw new \PDOException($e);
        }

        $listss = array();
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $lists = [];
                $listss[$key]["layer_id"] = $val["layer_id"];
                $listss[$key]["layer_name"] = $val["layer_name"];
                $sqls = "SELECT operate_key FROM layer_permit WHERE layer_id=:layer_id";
                $params = [":layer_id"=>$val["layer_id"]];
                foreach ($mysql->query($sqls,$params) as $rows){
                    $lists[] = $rows["operate_key"];
                }
                $sql = "SELECT user_id FROM user_info_intact WHERE layer_id=:layer_id";
                $param = [":layer_id"=>$val["layer_id"]];
                $user_total = $mysql->execute($sql,$param);
                $listss[$key]["users"] = $user_total;
                $listss[$key]["auth"] = $lists;
            }
        }
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':operate_key' => 'broker_layer_select',
            ':client_ip' => ip2long($context->getClientAddr()),
            ':detail' => '查看代理层级列表',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply([
            "status"=>200,
            "msg"=>"获取成功",
            "total"=>$total,
            "list"=>$listss
        ]);
    }
}