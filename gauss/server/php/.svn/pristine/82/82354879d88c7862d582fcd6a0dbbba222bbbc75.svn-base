<?php
/**
 * FirstChargeReport.php
 *
 * @description   首充报表
 * @Author  Luis
 * @date  2019-04-07
 * @links  ReportQuery/FirstChargeReport
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
namespace Site\Websocket\ReportQuery;
use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class FirstChargeReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $mysql = $config->data_report;
        $mysqlStaff = $config->data_staff;
        $StaffGrade = $context->getInfo("StaffGrade");
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo("MasterId");
        // 检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array('report_lottery', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        $master_id =  $MasterId==0 ? $staffId : $MasterId ;
        $start_time = isset($data["start_time"]) ?  strtotime($data["start_time"]) : '';
        $end_time = isset($data["end_time"]) ? strtotime($data["end_time"]) : '';
        $time = '';
        $param = [];
        if(!empty($start_time) && !empty($end_time)){
            $time = " and first_deposit_time between :start_time and :end_time";
            $param[":start_time"] =strtotime(date("Ymd",$start_time)."00:00:00");
            $param[":end_time"] =strtotime(date("Ymd",$end_time)."23:59:59");;
        }
        if(!empty($start_time) && empty($end_time)){
            $time = " and first_deposit_time >= :start_time ";
            $param[":start_time"] =$start_time;
        }
        if(empty($start_time) && !empty($end_time)){
            $time = " and first_deposit_time <= :end_time ";
            $param[":end_time"] =$end_time;
        }
        if ($MasterId != 0) {
            $sql = 'select layer_id_list from staff_info_intact where staff_id=:staff_id';
            foreach ($mysqlStaff->query($sql, [':staff_id' => $staffId]) as $row) {
                $layer_info = $row;
            }
            $layerLists = json_decode($layer_info['layer_id_list'], true);
        }

        switch ($StaffGrade){
            case 0:
                if($MasterId == 0){
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0".$time;
                } else {
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0 and layer_id in :layer_list ".$time;
                    $param[':layer_list'] = $layerLists;
                }
                
                break;
            case 1:
                if($MasterId == 0){
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0 and major_id = :master_id".$time;
                    $param[":master_id"] =$master_id;
                } else {
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0 and major_id = :master_id and layer_id in :layer_list ".$time;
                    $param[":master_id"] =$master_id;
                    $param[':layer_list'] = $layerLists;
                }   
                break;
            case 2:
                if($MasterId == 0){
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0 and minor_id = :master_id".$time;
                    $param[":master_id"] =$master_id;
                } else {
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0 and minor_id = :master_id and layer_id in :layer_list ".$time;
                    $param[":master_id"] =$master_id;
                    $param[':layer_list'] = $layerLists;
                }
                break;
            case 3:
                if($MasterId == 0 ){
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0  and agent_id = :master_id".$time;
                    $param[":master_id"] =$master_id;
                } else {
                    $firstSql = "select register_time,user_id,user_key,layer_name,first_deposit_time from user_event where first_deposit_time>0  and agent_id = :master_id and layer_id in :layer_list ".$time;
                    $param[":master_id"] =$master_id;
                    $param[':layer_list'] = $layerLists;
                }
                break;
        }
        $firstSql .=" order by first_deposit_time desc";
        $firstDate = iterator_to_array($mysql -> query($firstSql,$param));
        $first_list = [];
        if (!empty($firstDate)){
            foreach ($firstDate as $k=>$v){
                $first_list[$k]['user_key'] = $v['user_key'];
                $first_list[$k]['layer_name'] = $v['layer_name'];
                $first_list[$k]['first_deposit_time'] = date("Y-m-d H:i:s",$v['first_deposit_time']);
                $sql = 'select money from user_cumulate where user_id=:user_id';
                foreach ($mysql->query($sql, [':user_id' => $v['user_id']]) as $rows) {
                    $first_list[$k]['money'] = empty($rows['money'])? '0.00' :$this->intercept_num($rows['money']);
                }
                $first_time = date("Ymd",$v['first_deposit_time']);
                $first_list[$k]['deposit_amount'] = '';
                $first_list[$k]['deposit_type'] = '';
                $first_list[$k]['deposit_coupon'] = '';
                $usersql = "select staff_deposit_amount,deposit_bank_amount,deposit_weixin_amount,deposit_alipay_amount,(bank_deposit_amount -bank_deposit_coupon) as bank_deposit_amount,simple_deposit_amount,bank_deposit_coupon from daily_user where daily = :daily and user_id = :user_id";
                foreach ($mysql->query($usersql, [':user_id' => $v['user_id'],':daily' => $first_time]) as $row) {
                    $deposit_amout = $row['deposit_bank_amount'] + $row['deposit_weixin_amount'] + $row['deposit_alipay_amount'] + $row['bank_deposit_amount'] + $row['simple_deposit_amount']+$row['staff_deposit_amount'];
                    $first_list[$k]['deposit_amount'] = empty($deposit_amout)?'0.00' : $this->intercept_num($deposit_amout);
                    $first_list[$k]['deposit_coupon'] = empty($row['bank_deposit_coupon'])? '0.00' :$this->intercept_num($row['bank_deposit_coupon']);
                    $first_list[$k]['deposit_type'] = '';
                    if ($row['deposit_alipay_amount'] > 0) {
                        $first_list[$k]['deposit_type'] .= '支付宝支付 ';
                    }
                    if ($row['deposit_weixin_amount'] > 0) {
                        $first_list[$k]['deposit_type'] .= '微信支付 ';
                    }
                    if ($row['deposit_bank_amount'] > 0) {
                        $first_list[$k]['deposit_type'] .= '网银支付 ';
                    }
                    if ($row['bank_deposit_amount'] > 0) {
                        $first_list[$k]['deposit_type'] .= '银行卡充值 ';
                    }
                    if ($row['simple_deposit_amount'] > 0) {
                        $first_list[$k]['deposit_type'] .= '便捷支付 ';
                    }
                    if ($row['staff_deposit_amount'] > 0){
                        $first_list[$k]['deposit_type'] .= '人工充值 ';
                    }
                }
                $first_list[$k]['register_time'] = date("Y-m-d H:i:s",$v['register_time']);
            }
        }
        $context->reply([
            "status"=>200,
            "msg"=>"获取成功",
            "list"=>$first_list
        ]);
    }
}