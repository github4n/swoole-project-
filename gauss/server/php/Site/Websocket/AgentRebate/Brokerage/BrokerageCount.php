<?php

namespace Site\Websocket\AgentRebate\Brokerage;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * BrokerageCount.php.
 *
 * @description   佣金查询--佣金统计
 * @Author  Luis
 * @date  2019-04-07
 * @links  参数：AgentRebate/Brokerage/BrokerageCount{"start_time":"","end_time":"","setup_name":""}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-09
 */
class BrokerageCount extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_report', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $mysqlStaff = $config->data_staff;
        $report_mysql = $config->data_report;
        $data = $context->getData();
        $start_time = isset($data['start_time']) ? $data['start_time'] : '';
        $end_time = isset($data['end_time']) ? $data['end_time'] : '';
        $staffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');
        $param = [];
        $layer_info = [];
        if ($MasterId != 0) {
            $sql = 'select layer_id_list from staff_info_intact where staff_id=:staff_id';
            foreach ($mysqlStaff->query($sql, [':staff_id' => $staffId]) as $row) {
                $layer_info = $row;
            }
            $layerLists = json_decode($layer_info['layer_id_list'], true);
        }
        if ($staffGrade == 0) {
            if ($MasterId == 0) {
                $sql = 'select daily,count(user_id) as user_all,sum(broker_1_bet_user+broker_2_bet_user+broker_3_bet_user) as bet_user,sum(broker_1_bet+broker_2_bet+broker_3_bet) as bet_amount,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where 1=1';
                $total_sql = 'select daily,count(user_id) as user_all,sum(broker_1_bet_user+broker_2_bet_user+broker_3_bet_user) as bet_user,sum(broker_1_bet+broker_2_bet+broker_3_bet) as bet_amount,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where 1=1';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user where 1=1  ';
                $param = [];
                $params = [];
                $user_param = [];
            } else {
                $sql = 'select daily,count(user_id) as user_all,sum(broker_1_bet_user+broker_2_bet_user+broker_3_bet_user) as bet_user,sum(broker_1_bet+broker_2_bet+broker_3_bet) as bet_amount,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where layer_id in :layer_list';
                $total_sql = 'select daily,count(user_id) as user_all,sum(broker_1_bet_user+broker_2_bet_user+broker_3_bet_user) as bet_user,sum(broker_1_bet+broker_2_bet+broker_3_bet) as bet_amount,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where layer_id in :layer_list';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user where layer_id in :layer_list  ';
                $param = [':layer_list' => $layerLists];
                $params = [':layer_list' => $layerLists];
                $user_param = [':layer_list' => $layerLists];
            }
        } elseif ($staffGrade == 1) {
            if ($MasterId == 0) {
                $sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where major_id=:major_id  ';
                $total_sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where major_id=:major_id  ';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user  where major_id=:major_id ';
                $param = [':major_id' => $staffId];
                $params = [':major_id' => $staffId];
                $user_param = [':major_id' => $staffId];
            } else {
                $sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where major_id=:major_id and layer_id in :layer_list ';
                $total_sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where major_id=:major_id and layer_id in :layer_list ';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user  where major_id=:major_id and layer_id in :layer_list';
                $param = [':major_id' => $MasterId, ':layer_list' => $layerLists];
                $params = [':major_id' => $MasterId, ':layer_list' => $layerLists];
                $user_param = [':major_id' => $MasterId, ':layer_list' => $layerLists];
            }
        } elseif ($staffGrade == 2) {
            if ($MasterId == 0) {
                $sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where minor_id=:minor_id ';

                $total_sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where minor_id=:minor_id ';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user   where minor_id=:minor_id';
                $param = [':minor_id' => $staffId];
                $params = [':minor_id' => $staffId];
                $user_param = [':minor_id' => $staffId];
            } else {
                $sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where minor_id=:minor_id and layer_id in :layer_list ';
                $total_sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where minor_id=:minor_id and layer_id in :layer_list ';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user where minor_id=:minor_id and layer_id in :layer_list';
                $param = [':minor_id' => $MasterId, ':layer_list' => $layerLists];
                $params = [':minor_id' => $MasterId, ':layer_list' => $layerLists];
                $user_param = [':minor_id' => $MasterId, ':layer_list' => $layerLists];
            }
        } elseif ($staffGrade == 3) {
            if ($MasterId == 0) {
                $sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where agent_id=:agent_id ';
                $total_sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where agent_id=:agent_id ';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user where agent_id=:agent_id';
                $param = [':agent_id' => $staffId];
                $params = [':agent_id' => $staffId];
                $user_param = [':agent_id' => $staffId];
            } else {
                $sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage where agent_id=:agent_id and layer_id in :layer_list  ';
                $total_sql = 'select daily,count(user_id) as user_all,sum(if(deliver_time>0,brokerage,0)) as ybrokerage,sum(if(deliver_time=0,brokerage,0)) as nbrokerage,count(if(deliver_time>0,user_id,null)) as yuser,count(if(deliver_time=0,user_id,null)) as nuser from daily_user_brokerage from daily_user_brokerage where agent_id=:agent_id and layer_id in :layer_list  ';
                $user_sql = 'select count(distinct(if(wager_amount>0,user_id,null))) as user_all, sum(wager_amount) as bet_all from daily_user where agent_id=:agent_id and layer_id in :layer_list';
                $param = [':agent_id' => $MasterId, ':layer_list' => $layerLists];
                $params = [':agent_id' => $MasterId, ':layer_list' => $layerLists];
                $user_param = [':agent_id' => $MasterId, ':layer_list' => $layerLists];
            }
        }
        if (!empty($start_time) && !empty($end_time)) {
            $sql .= ' and daily between :start_time and :end_time';
            $total_sql .= ' and daily between :start_time and :end_time';
            $param[':start_time'] = intval(date('Ymd', strtotime($start_time)));
            $param[':end_time'] = intval(date('Ymd', strtotime($end_time)));
            $params[':start_time'] = intval(date('Ymd', strtotime($start_time)));
            $params[':end_time'] = intval(date('Ymd', strtotime($end_time)));
        }

        $sql .= ' group by daily order by daily desc limit 1000';
        $total_sql .= ' group by daily';
        $total = $report_mysql->execute($total_sql, $params);
        $all_list = iterator_to_array($report_mysql->query($sql, $param));
        $list = [];
        if (!empty($all_list)) {
            foreach ($all_list as $k => $v) {
                $datas = [
                    'daily' => $v['daily'],
                    'nbrokerage' => $this->intercept_num($v['nbrokerage']),
                    'nuser' => $v['nuser'],
                    'user_all' => $v['user_all'],
                    'ybrokerage' => $this->intercept_num($v['ybrokerage']),
                    'yuser' => $v['yuser'],
                ];
                $user_sql .= ' and daily=:daily ';
                $user_param[':daily'] = $v['daily'];
                foreach ($report_mysql->query($user_sql, $user_param) as $row) {
                    $datas['bet_user'] = empty($row['user_all']) ? 0 : $row['user_all'];
                    $datas['bet_amount'] = empty($row['bet_all']) ? 0 : $this->intercept_num($row['bet_all']);
                }

                $list[] = $datas;
            }
        }
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $list, 'total' => $total]);
    }
}
