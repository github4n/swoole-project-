<?php
namespace Site\Websocket\AgentRebate\Brokerage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/*
 * 佣金查询
 * AgentRebate/Brokerage/BrokerageSearch {"layer_name":"新代理","deliver":"","start_time":"","end_time":""}
 * layer_name 代理层级
 * deliver  是否派发 y(已派发) or n(未派发)
 * start_time 派发开始时间/end_time 派发结束时间
 * page：当前页数,num:每页显示数量,go_num:跳转页数
 * */

class BrokerageSearch extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {

        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array("broker_select",$auth)){
            $context->reply(["status"=>202,"msg"=>"你还没有操作权限"]);
            return;
        }
        $data = $context->getData();
        $cache = $config->cache_site;
        $agent_list = json_decode($cache->hget("LayerList","agentLayer"));
        $start_time = isset($data['start_time']) ? $data['start_time'] : '';
        $end_time = isset($data['end_time']) ? $data['end_time'] : '';
        $layer_name = isset($data['layer_name']) ? $data['layer_name'] : '';
        $deliver = isset($data['deliver']) ? $data['deliver'] : '';
        $page = isset($data["page"]) ?$data["page"]: 1;
        $num = isset($data["num"]) ?$data["num"]: 10;
        $go_num = isset($data["go_num"]) ?$data["go_num"]:'';
        $staffGrade = $context->getInfo("StaffGrade");
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo("MasterId");
        if(!is_numeric($page) && $page <= 0){
            $context->reply(["status"=>202,"msg"=>"当前页数不正确"]);
            return;
        }
<<<<<<< .mine

        $brokerageDate = "select daily,layer_id,sum(brokerage) as brokerage,count(user_id) as user_count from daily_user_brokerage  where 1=1  ";
        $ybrokerageDate = "select daily,layer_id,deliver_time,sum(brokerage) as ybrokerage,count(user_id) as user_count from daily_user_brokerage where deliver_time > 0 ";
        $nbrokerageDate = "select daily,layer_id,deliver_time,sum(brokerage) as nbrokerage,count(user_id) as user_count from daily_user_brokerage where deliver_time = 0 ";
        if ($staffGrade == 1) {
            $brokerageDate .= "and major_id='$staffId' ";
            $ybrokerageDate .= "and major_id='$staffId' ";
            $nbrokerageDate .= "and major_id='$staffId' ";
||||||| .r12487


        $brokerageDate = "select daily,layer_id,sum(brokerage) as brokerage,count(user_id) as user_count from daily_user_brokerage  where 1=1  ";
        $ybrokerageDate = "select daily,layer_id,deliver_time,sum(brokerage) as ybrokerage,count(user_id) as user_count from daily_user_brokerage where deliver_time > 0 ";
        $nbrokerageDate = "select daily,layer_id,deliver_time,sum(brokerage) as nbrokerage,count(user_id) as user_count from daily_user_brokerage where deliver_time = 0 ";
        if ($staffGrade == 1) {
            $brokerageDate .= "and major_id='$staffId' ";
            $ybrokerageDate .= "and major_id='$staffId' ";
            $nbrokerageDate .= "and major_id='$staffId' ";
=======
        if(!is_numeric($num)){
            $context->reply(["status"=>203,"msg"=>"每页显示的数量不正确"]);
            return;
>>>>>>> .r13118
        }
        $limit = " LIMIT ".($page-1)*$num.",".$num;
        if(!empty($go_num) && is_numeric($go_num)){
            if($go_num <= 0){
                $context->reply(["status"=>204,"msg"=>"跳转的页数不正确"]);
                return;
            }
            $limit = " LIMIT ".($go_num-1)*$num.",".$num;
        }
        if ($MasterId != 0) {
            $staffId =$MasterId;
        }

        if ($staffGrade == 0) {
            $brokerageDate = "SELECT daily,layer_id,layer_name,user_count,brokerage_count,brokerage_amount,auto_deliver,deliver_staff_name,deliver_finish_time from daily_layer_brokerage where 1=1 ";
            $ybrokerageDate = "SELECT daily,layer_id,sum(brokerage) as ybrokerage from daily_user_brokerage where deliver_time > 0 ";
            $nbrokerageDate = "SELECT daily,layer_id,sum(brokerage) as nbrokerage from daily_user_brokerage where deliver_time = 0 ";

            if (!empty($deliver)) {
                switch ($deliver) {
                    case 'y':
                        $brokerageDate .= "and deliver_finish_time >0 ";
                        break;
                    case 'n':
                        $brokerageDate .= "and deliver_finish_time =0 ";
                        break;
                    case null:

                        break;
                }

            }

            if(!empty($end_time)) {
                $end_time = strtotime($end_time);
                $brokerageDate .= "and deliver_finish_time <= $end_time  ";
                $ybrokerageDate .= "and deliver_time <= $end_time  ";
                $nbrokerageDate .= "and deliver_time <= $end_time  ";
            }
            if(!empty($start_time)) {
                $start_time = strtotime($start_time);
                $brokerageDate .= "and deliver_finish_time >= $start_time ";
                $ybrokerageDate .= "and deliver_time >= $start_time ";
                $nbrokerageDate .= "and deliver_time >= $start_time ";
            }

            if(!empty($layer_name)) {
                $user_mysql=$config->data_user;
                $usersql = "SELECT layer_id FROM layer_info where layer_name = '$layer_name'";
                $layer_data = iterator_to_array($user_mysql -> query($usersql));
                if ($layer_data[0] == null) {
                    $context->reply(["status" => 203, "msg" => "代理层级错误,没有找到该代理层级"]);
                    return;
                }
                $layer_id=$layer_data[0]['layer_id'];
                $brokerageDate .= "and layer_id = '$layer_id'  ";
                $ybrokerageDate .= "and layer_id = '$layer_id'  ";
                $nbrokerageDate .= "and layer_id = '$layer_id'  ";
            }

            $order = " ORDER BY daily DESC";
            $group = "group by daily,layer_id";
            $brokerageDate = $brokerageDate . $order.$limit;
            $ybrokerageDate = $ybrokerageDate . $group;
            $nbrokerageDate = $nbrokerageDate . $group;
            $report_mysql = $config->data_report;
            $BrokerageDate=iterator_to_array($report_mysql->query($brokerageDate));
            $ybrokerageDate=iterator_to_array($report_mysql->query($ybrokerageDate));
            $nbrokerageDate=iterator_to_array($report_mysql->query($nbrokerageDate));
            $list = [];
            foreach ($BrokerageDate as $k => $v){
                $list[$k]['daily'] = $v['daily'];
                $list[$k]['layer_id'] = $v['layer_id'];
                $list[$k]['layer_name'] = $v['layer_name'];
                $list[$k]['user_count'] = $v['user_count'];
                $list[$k]['brokerage_count'] = $v['brokerage_count'];
                $list[$k]['brokerage_amount'] = $v['brokerage_amount'];
                $list[$k]['ybrokerage'] = 0;
                $list[$k]['nbrokerage'] = 0;
                $list[$k]['deliver'] = "y";
                $list[$k]['auto_deliver'] = $v['auto_deliver'];
                $list[$k]['deliver_staff_name'] = $v['deliver_staff_name'];
                $list[$k]['deliver_finish_time'] = $v['deliver_finish_time'];
                foreach ($ybrokerageDate as $key => $val){
                    if($v['daily'] == $val['daily'] && $v['layer_id'] == $val['layer_id']){
                        $list[$k]['ybrokerage'] = $val['ybrokerage'];
                    }
                }
                foreach ($nbrokerageDate as $a => $b){
                    if($v['daily'] == $b['daily'] && $v['layer_id'] == $b['layer_id']){
                        $list[$k]['nbrokerage'] = $b['nbrokerage'];
                        $list[$k]['deliver'] = "n";
                    }
                }
            }
            $context->reply(["status" => 200, "msg" => "获取成功", "list" => $list,"agent_list"=>$agent_list]);
            return;
        }
        $brokerageDate = "select daily,layer_id,sum(brokerage) as brokerage,count(user_id) as user_count from daily_user_brokerage  where 1=1  ";
        $ybrokerageDate = "select daily,layer_id,deliver_time,sum(brokerage) as ybrokerage,count(user_id) as user_count from daily_user_brokerage where deliver_time > 0 ";
        $nbrokerageDate = "select daily,layer_id,deliver_time,sum(brokerage) as nbrokerage,count(user_id) as user_count from daily_user_brokerage where deliver_time = 0 ";

        if ($staffGrade == 1) {
            $brokerageDate .= "and major_id='$staffId' ";
            $ybrokerageDate .= "and major_id='$staffId' ";
            $nbrokerageDate .= "and major_id='$staffId' ";
        }
        if ($staffGrade == 2) {
            $brokerageDate .= "and minor_id='$staffId' ";
            $ybrokerageDate .= "and minor_id='$staffId' ";
            $nbrokerageDate .= "and minor_id='$staffId' ";
        }
        if ($staffGrade == 3) {
            $brokerageDate .= "and agent_id='$staffId' ";
            $ybrokerageDate .= "and agent_id='$staffId' ";
            $nbrokerageDate .= "and agent_id='$staffId' ";
        }


        if (!empty($deliver)) {
            switch ($deliver) {
                case 'y':
                    $brokerageDate .= "and deliver_time >0 ";
                    $ybrokerageDate .= "and deliver_time >0 ";
                    $nbrokerageDate .= "and deliver_time >0 ";
                    break;
                case 'n':
                    $brokerageDate .= "and deliver_time =0 ";
                    $ybrokerageDate .= "and deliver_time =0 ";
                    $nbrokerageDate .= "and deliver_time =0 ";
                    break;
                case null:

                    break;
            }

        }

        if(!empty($end_time)) {
            $end_time = strtotime($end_time);
            $brokerageDate .= "and deliver_time <= $end_time  ";
            $ybrokerageDate .= "and deliver_time <= $end_time  ";
            $nbrokerageDate .= "and deliver_time <= $end_time  ";
        }

        if(!empty($start_time)) {
            $start_time =strtotime($start_time);
            $brokerageDate .= "and deliver_time >= $start_time ";
            $ybrokerageDate .= "and deliver_time >= $start_time ";
            $nbrokerageDate .= "and deliver_time >= $start_time ";
        }

        if(!empty($layer_name)) {
            $user_mysql=$config->data_user;
            $usersql = "SELECT layer_id FROM layer_info where layer_name = '$layer_name'";
            $layer_data = iterator_to_array($user_mysql -> query($usersql));
            if ($layer_data[0] == null) {
                $context->reply(["status" => 203, "msg" => "代理层级错误,没有找到该代理层级"]);
                return;
            }
            $layer_id=$layer_data[0]['layer_id'];
            $brokerageDate .= "and layer_id = '$layer_id'  ";
            $ybrokerageDate .= "and layer_id = '$layer_id'  ";
            $nbrokerageDate .= "and layer_id = '$layer_id'  ";
        }

        $order = " ORDER BY daily DESC";
        $bgroup = "group by layer_id,daily";
        $group = "group by deliver_time,layer_id,daily";
        $brokerageDate = $brokerageDate . $bgroup .$order.$limit ;
        $ybrokerageDate = $ybrokerageDate . $group .$order;
        $nbrokerageDate = $nbrokerageDate . $group .$order;
        $report_mysql = $config->data_report;
        $brokerageDate=iterator_to_array($report_mysql->query($brokerageDate));
        $ybrokerageDate=iterator_to_array($report_mysql->query($ybrokerageDate));
        $nbrokerageDate=iterator_to_array($report_mysql->query($nbrokerageDate));

        $list=[];
        foreach ($brokerageDate as $k => $v) {
            $list[$k]['daily'] = $v['daily'];
            $list[$k]['layer_id'] = $v['layer_id'];
            $list[$k]['layer_name'] = $context->getInfo($v['layer_id']);
            $list[$k]['user_count'] = $v['user_count'];
            $list[$k]['brokerage_count'] = 0;
            $list[$k]['brokerage_amount'] = $v['brokerage'];
            $list[$k]['ybrokerage'] = 0;
            $list[$k]['nbrokerage'] = 0;
            $list[$k]['auto_deliver'] = "0";
            $list[$k]['deliver'] = "y";
            $list[$k]['deliver_finish_time'] = 0;
            $list[$k]['deliver_staff_name'] = '';
            foreach ($ybrokerageDate as $key => $val){
                if ($v['daily'] == $val['daily'] && $v['layer_id'] == $val['layer_id']){
                    $list[$k]['brokerage_count'] = $val['user_count'];
                    $list[$k]['ybrokerage'] = $val['ybrokerage'];
                    $list[$k]['nbrokerage'] = $v['brokerage'] - $val['ybrokerage'];
                    $list[$k]['deliver'] = 1;
                    $list[$k]['deliver_finish_time'] = $val['deliver_time'];
                }
            }

            foreach ($nbrokerageDate as $a => $b){
                if ($v['daily'] == $b['daily'] && $v['layer_id'] == $b['layer_id']){
                    $list[$k]['nbrokerage'] = $b['nbrokerage'];
                    $list[$k]['ybrokerage'] = $v['brokerage'] - $b['nbrokerage'];
                    $list[$k]['deliver'] = "n";
                    $list[$k]['deliver_finish_time'] = 0;
                }
            }
        }

        $context->reply(["status" => 200, "msg" => "获取成功", "list" => $list,"agent_list"=>$agent_list]);


    }
}