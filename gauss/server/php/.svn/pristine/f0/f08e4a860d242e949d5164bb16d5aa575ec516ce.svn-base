<?php
/**
 * LayerReport.php
 *
 * @description   层级报表
 * @Author  Luis
 * @date  2019-04-07
 * @links  ReportQuery/LayerReport
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
namespace Site\Websocket\ReportQuery;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;



class LayerReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $mysql = $config->data_report;
        $mysqlStaff = $config->data_staff;
        // 检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('report_lottery', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }

        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $cache = $config->cache_site;
        $MasterId = $context->getInfo('MasterId');

//        $start_time = isset($data["start_time"]) ? $data["start_time"] : '';
//        $end_time = isset($data["end_time"]) ? $data["end_time"] : '';
//        $time = '';
//        if (!empty($start_time) && !empty($end_time)) {
//            $start_time = intval(date("Ymd", strtotime($data["start_time"])));
//            $end_time = intval(date("Ymd", strtotime($data["end_time"])));
//            $time = " and daily between :start_time and :end_time";
//        }
//        if (!empty($start_time) && empty($end_time)) {
//            $start_time = intval(date("Ymd", strtotime($data["start_time"])));
//            $time = " and daily > :start_time ";
//        }
//        if (empty($start_time) && !empty($end_time)) {
//            $end_time = intval(date("Ymd", strtotime($data["end_time"])));
//            $time = " and daily <:end_time ";
//        }
        $listTranslation = [];
        $layers = json_decode($cache->hget('LayerList', 'allLayer'));
        foreach ($layers as $value) {
            $listTranslation[$value->layer_id]['layer_id'] = $value->layer_id;
            $listTranslation[$value->layer_id]['layer_name'] = $value->layer_name;
            $listTranslation[$value->layer_id]['user_all'] = 0;
            $listTranslation[$value->layer_id]['deposit_amount'] = 0;
            $listTranslation[$value->layer_id]['deposit_count'] = 0;
            $listTranslation[$value->layer_id]['withdraw_amount'] = 0;
            $listTranslation[$value->layer_id]['bet_amount'] = 0;
            $listTranslation[$value->layer_id]['bet_count'] = 0;
            $listTranslation[$value->layer_id]['bonus_amount'] = 0;
            $listTranslation[$value->layer_id]['coupon_amount'] = 0;
            $listTranslation[$value->layer_id]['rebate_amount'] = 0;
            $listTranslation[$value->layer_id]['profit_amount'] = 0;
        }
        $layer_info = [];
        if ($MasterId != 0) {
            $sql = 'select layer_id_list from staff_info_intact where staff_id=:staff_id';
            foreach ($mysqlStaff->query($sql, [':staff_id' => $staffId]) as $row) {
                $layer_info = $row;
            }
            $layerLists = json_decode($layer_info['layer_id_list'], true);
        }
        if ($StaffGrade == 0) { //站长
            if($MasterId == 0){
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id, sum(deposit_amount) as deposit_amount,'.
                    'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                    'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                    'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                    'where 1=1  group by layer_id';
                $param = [];
            } else {
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id, sum(deposit_amount) as deposit_amount,'.
                    'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                    'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                    'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                    'where layer_id in :layer_list  group by layer_id';
                $param = [':layer_list' => $layerLists];
            }
        }
        //充值人数及投注人数
        if ($StaffGrade == 1) {
            if($MasterId == 0 ){
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id,sum(deposit_amount) as deposit_amount,'.
                    'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                    'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                    'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                    ' where major_id = :staff_id  group by layer_id';
                $param = [':staff_id' => $staffId];
            } else {
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id,sum(deposit_amount) as deposit_amount,'.
                    'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                    'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                    'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                    ' where major_id = :staff_id  and layer_id in :layer_list group by layer_id';
                $param = [':staff_id' => $MasterId,':layer_list'=>$layerLists];
            }
        }
        if ($StaffGrade == 2) {
            if($MasterId == 0){
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id, sum(deposit_amount) as deposit_amount,'.
                    'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                    'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                    'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                    ' where minor_id = :staff_id  group by layer_id';
                $param = [':staff_id' => $staffId];
            } else {
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id, sum(deposit_amount) as deposit_amount,'.
                    'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                    'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                    'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                    ' where minor_id = :staff_id and layer_id in :layer_list group by layer_id';
                $param = [':staff_id' => $MasterId,':layer_list'=>$layerLists];
            }
        }
        if ($StaffGrade == 3) {
            if($MasterId == 0){
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id,sum(deposit_amount) as deposit_amount,'.
                'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                ' where agent_id = :staff_id  group by layer_id';
                $param = [':staff_id' => $staffId];
            } else {
                $sql = 'select layer_id,count(DISTINCT(IF (wager_amount > 0,user_id,null))) as user_id,sum(deposit_amount) as deposit_amount,'.
                    'count(DISTINCT(IF (deposit_amount-staff_deposit_amount > 0,user_id,null))) as deposit_count,sum(withdraw_amount) as withdraw_amount,'.
                    'sum(wager_amount) as bet_amount,sum(bet_count) as bet_count,sum(bonus_amount) as bonus_amount,'.
                    'sum(coupon_amount) as coupon_amount,sum(rebate_amount) as rebate_amount,sum(wager_amount - bonus_amount -rebate_amount) as profit_amount from daily_user '.
                    ' where agent_id = :staff_id and layer_id in :layer_list group by layer_id';
                $param = [':staff_id' => $MasterId,':layer_list'=>$layerLists];
            }
        }
        $list = iterator_to_array($mysql->query($sql, $param));
        $layer_list = [];
        if (!empty($list)) {
            foreach ($list as  $val) {
                $layer_name = $context->getInfo($val['layer_id']);
                if (empty($layer_name)) {
                    $layer_name = '层级已删除';
                }
                $listTranslation[$val['layer_id']]['layer_id'] = $val['layer_id'];
                $listTranslation[$val['layer_id']]['layer_name'] = $layer_name;
                $listTranslation[$val['layer_id']]['user_all'] = $val['user_id'];
                $listTranslation[$val['layer_id']]['deposit_amount'] = $this->intercept_num($val['deposit_amount']);
                $listTranslation[$val['layer_id']]['deposit_count'] = $val['deposit_count'];
                $listTranslation[$val['layer_id']]['withdraw_amount'] = $this->intercept_num($val['withdraw_amount']);
                $listTranslation[$val['layer_id']]['bet_amount'] = $this->intercept_num($val['bet_amount']);
                $listTranslation[$val['layer_id']]['bet_count'] = $val['bet_count'];
                $listTranslation[$val['layer_id']]['bonus_amount'] = $this->intercept_num($val['bonus_amount']);
                $listTranslation[$val['layer_id']]['coupon_amount'] = $this->intercept_num($val['coupon_amount']);
                $listTranslation[$val['layer_id']]['rebate_amount'] = $this->intercept_num($val['rebate_amount']);
                $listTranslation[$val['layer_id']]['profit_amount'] = $this->intercept_num($val['profit_amount']);
            }
        }

        foreach ($listTranslation as $value) {
            $layer_list[] = $value;
        }//调整层级
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $layer_list,
        ]);
    }
}
