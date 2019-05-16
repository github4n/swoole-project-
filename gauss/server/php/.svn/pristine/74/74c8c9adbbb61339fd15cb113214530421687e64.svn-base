<?php

/**
 * Class DistributeSubsidy
 * @description 返水派发类，点击派发按钮后传递参数去触发反水任务
 * @author Nathan
 * @date 2019-02-22
 * @link Websocket: Rebate/RebateCount/DistributeSubsidy {"layer_id": "1","daily":"20190425"}
 * @param string $layer_id 层级Id
 * @param string $daily 日期
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-10
 */

namespace Site\Websocket\Rebate\RebateCount;

use Lib\Config;
use Lib\Websocket\Context;
use Site\Websocket\CheckLogin;

class DistributeSubsidy extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $staffMysql = $config->data_staff;
        $mysqlReport = $config->data_report;
        $staffGrade = $context->getInfo('StaffGrade');
        if ($staffGrade != 0) {
            $context->reply(['status' => 202, 'msg' => '无权限操作']);

            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('subsidy_deliver', $auth)) {
            $context->reply(['status' => 203, 'msg' => '你还没有操作权限']);

            return;
        }
        $staff_id = $context->getInfo('StaffId');
        $param = $context->getData();
        $daily = isset($param['daily']) ? $param['daily'] : '';
        $layer_id = isset($param['layer_id']) ? $param['layer_id'] : '';

        if (empty($layer_id)) {
            $context->reply(['status' => 204, 'msg' => '派发层级不能为空']);

            return;
        }

        if (empty($daily)) {
            $context->reply(['status' => 205, 'msg' => '选择派发的日期不能为空']);

            return;
        }

        $sql = 'select daily from daily_status where daily=:daily and frozen = 1';
        $statusInfo = [];
        foreach ($mysqlReport->query($sql, [':daily' => $daily]) as $row) {
            $statusInfo = $row;
        }
        if (empty($statusInfo)) {
            $context->reply(['status' => 210, 'msg' => '还未完成结算，暂时不能派发']);

            return;
        }

        $sql = 'select staff_name from staff_info_intact where staff_id = :staffId';
        $param = [':staffId' => $staff_id];
        $staff_name = '未知';
        foreach ($staffMysql->query($sql, $param) as $val) {
            $staff_name = $val['staff_name'];
        }

        $adapter = new \Lib\Task\Adapter($config->cache_daemon);
        $adapter->plan('Cash/Subsidy', ['staff_id' => $staff_id, 'staff_name' => $staff_name, 'daily' => $daily, 'layer_id' => $layer_id, 'start_time' => time(), 'auto_deliver' => 1]);

        $context->reply(['status' => 200, 'msg' => '反水派发中……', 'staff' => $staff_id]);
    }
}
