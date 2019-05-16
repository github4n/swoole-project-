<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 19-2-22
 * Time: 下午6:04
 */

namespace Site\Websocket\AgentRebate\Brokerage;
use Lib\Config;
use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;

/**
 * DistributeBrokerage.php
 *
 * @description   手动派发佣金触发佣金派发任务
 * @Author  nathan
 * @date  2019-04-07
 * @links  参数：AgentRebate/Brokerage/BrokerageTwo {"broker_id":1,"time":"20190124"}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */

class DistributeBrokerage extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffGrade = $context->getInfo('StaffGrade');
        if ($staffGrade != 0) {
            $context->reply(['status' => 202,'msg' => '无权限操作']);
            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("broker_deliver", $auth)) {
            $context->reply(["status" => 203, "msg" => "你还没有操作权限"]);
            return;
        }
        $staff_id = $context->getInfo('StaffId');
        $param = $context->getData();
        $staffMysql = $config->data_staff;
        $mysqlReport = $config->data_report;
        $daily = isset($param['daily']) ? $param['daily'] : '';
        $layer_id = isset($param['layer_id']) ? $param['layer_id'] : '';

        if (empty($layer_id)) {
            $context->reply(['status' => 203,'msg' => '选择的派发层级不能为空']);
            return;
        }

        if (empty($daily)) {
            $context->reply(['status' => 203,'msg' => '选择派发的日期不能为空']);
            return;
        }
        $daily = date("Ymd",strtotime($daily));
        $sql = "select daily from daily_status where daily=:daily and frozen = 1";
        $statusInfo = [];
        foreach ($mysqlReport->query($sql,[":daily"=>$daily]) as $row){
            $statusInfo = $row;
        }
        if(empty($statusInfo)){
            $context->reply(["status"=>210,"msg"=>"还未完成结算，暂时不能派发"]);
            return;
        }

        $sql = "select staff_name from staff_info_intact where staff_id = :staff_id";
        $staff_name = '';
        foreach ($staffMysql->query($sql,[":staff_id" => $staff_id]) as $val) {
            $staff_name = $val['staff_name'];
        }
        $adapter = $adapter = new \Lib\Task\Adapter($config->cache_daemon);
        $adapter->plan('Cash/Brokerage', ['staff_id' => $staff_id,'staff_name' => $staff_name,'daily' => $daily,'layer_id' => $layer_id,'start_time' => time(),'auto_deliver'=>1]);

        $context->reply(['status' => 200,'msg' => '反佣派发中……']);
    }
}