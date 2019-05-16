<?php

/**
 * Class RebateCount
 * @description 返水查询及派发列表类
 * @author Blake
 * @date 2019-01-09
 * @link Websocket: Rebate/RebateCount/RebateCount {"layer_id":"1","is_deliver":"0","deliver_time_start":"","deliver_time_end":""}
 * @param string $layer_id 层级Id
 * @param string $is_deliver 是否派发
 * @param string $deliver_time_start 搜索派发开始时间
 * @param string $deliver_time_end 搜索派发结束派发
 * @returnDate []
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-08
 */

namespace Site\Websocket\Rebate\RebateCount;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

class RebateCount extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        // 检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('subsidy_report', $auth)) {
            $context->reply([
                'status' => 202,
                'msg' => '你还没有操作权限',
            ]);
            return;
        }

        // 接收参数
        $data = $context->getData();
        $mysql_report = $config->data_report;
        $mysql_user = $config->data_user;
        $cache = $config->cache_site;
        $layer_list = json_decode($cache->hget('LayerList', 'allLayer'), true);
        if (empty($layer_list)) {
            $sqlList = 'Select `layer_id`,`layer_name` From `layer_info` Order By `layer_id` Asc';
            $layer_list = iterator_to_array($mysql_user->query($sqlList));
        }

        //Subsidy_Stop
        $promitList = [];
        $layer_permit_sql = 'select * from layer_permit';
        $layer_permit_data = iterator_to_array($mysql_user->query($layer_permit_sql));
        if (!empty($layer_permit_data)) {
            foreach ($layer_permit_data as $permitData) {
                if ($permitData['operate_key'] == 'subsidy_stop') {
                    array_push($promitList, $permitData['layer_id']);
                }
            }
        }

        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo('StaffGrade');
        $finale_data = [];

        // 层级
        $masterId = $context->getInfo('MasterId');
        $master_id = $masterId == 0 ? $staffId : $masterId;
        if($masterId > 0) {            
            $sqlLayer = 'Select `layer_id_list` From `staff_info_intact` Where `staff_id` = :staffId Limit 1';
            $mysqlStaff = $config->data_staff;
            $paramLayer = [':staffId' => $staffId];
            $layerList = iterator_to_array($mysqlStaff->query($sqlLayer, $paramLayer));
            $layerList = empty($layerList[0]['layer_id_list']) ? '' : $layerList[0]['layer_id_list'];
            $layerList = str_replace(', ', ',', trim($layerList, '[]'));
            $layerArray = explode(',', $layerList);

            $layerAl = [];
            foreach($layer_list as $v) {
                if(in_array($v['layer_id'], $layerArray)) {
                    $layerAl[] = $v;
                }
            }
            $layer_list = $layerAl;
        }

        // 站长
        if ($StaffGrade == 0) {
            $rebate_sql = 'select daily,layer_id,layer_name,bet_all,subsidy_all,bet_lottery,subsidy_lottery,bet_video,subsidy_video,bet_game,subsidy_game,bet_sports,subsidy_sports,bet_cards,subsidy_cards,auto_deliver,deliver_staff_id, deliver_staff_name,deliver_launch_time,deliver_finish_time,1 as is_promit_subsidy from daily_layer_subsidy where 1 = 1 ';
            $paramSearch = [];
            if (!empty($data['deliver_time_start']) && empty($data['deliver_time_end'])) {
                if (date('Y-m-d H:i', strtotime($data['deliver_time_start'])) != $data['deliver_time_start']) {
                    $context->reply(['status' => 202, 'msg' => '初始时间类型错误']);
                    return;
                }
                $start = strtotime($data['deliver_time_start']);
                $rebate_sql .= ' AND deliver_finish_time  >= :start ';
                $paramSearch[':start'] = $start;
            }
            if (!empty($data['deliver_time_end']) && empty($data['deliver_time_start'])) {
                if (date('Y-m-d H:i', strtotime($data['deliver_time_end'])) != $data['deliver_time_end']) {
                    $context->reply(['status' => 203, 'msg' => '结束时间类型错误']);
                    return;
                }
                $end = strtotime($data['deliver_time_end']);
                $rebate_sql .= ' AND deliver_finish_time  <= :end ';
                $paramSearch[':end'] = $end;
            }
            if (!empty($data['deliver_time_end']) && !empty($data['deliver_time_start'])) {
                if (date('Y-m-d H:i', strtotime($data['deliver_time_end'])) != $data['deliver_time_end']) {
                    $context->reply(['status' => 203, 'msg' => '结束时间类型错误']);
                    return;
                }
                if (date('Y-m-d H:i', strtotime($data['deliver_time_start'])) != $data['deliver_time_start']) {
                    $context->reply(['status' => 202, 'msg' => '初始时间类型错误']);
                    return;
                }
                if (strtotime($data['deliver_time_start']) > strtotime($data['deliver_time_end'])) {
                    $context->reply(['status' => 204, 'msg' => '初始时间不可大于结束时间']);
                    return;
                }
                $start = strtotime($data['deliver_time_start']);
                $end = strtotime($data['deliver_time_end']);
                $rebate_sql .= ' AND deliver_finish_time between :start and :end ';
                $paramSearch += [
                    ':start' => $start,
                    ':end' => $end,
                ];
            }

            if (!empty($data['is_deliver']) && $data['is_deliver'] != 0) {
                $rebate_sql .= " AND deliver_finish_time != ''";
            } elseif (isset($data['is_deliver']) && $data['is_deliver'] == '0' && empty($data['deliver_time'])) {
                $rebate_sql .= " AND deliver_finish_time = ''";
            }

            // 子帐号层级权限控制
            if ($masterId > 0) {
                if(empty($data['layer_id'])) {
                    $rebate_sql .= ' And Find_In_Set(`layer_id`, :layerList)';
                    $paramSearch[':layerList'] = $layerList;
                }
            }

            if (!empty($data['layer_id'])) {
                // 如果是子帐号则判断该用户是否有该层级权限
                if (isset($layerArray)) {
                    if (!in_array($data['layer_id'], $layerArray)) {
                        $context->reply(['status' => 500, 'msg' => '没有该层级权限！']);
                        return;
                    }
                }
                
                $layer_id = $data['layer_id'];
                $rebate_sql .= ' AND layer_id = :layerId ';
                $paramSearch[':layerId'] = $layer_id;
            }

            $rebate_sql .= ' ORDER BY daily Desc,layer_id asc ';
            foreach ($mysql_report->query($rebate_sql, $paramSearch) as $row) {
                if (in_array($row['layer_id'], $promitList)) {
                    $row['is_promit_subsidy'] = 0;
                }
                // 时间格式化
                if ($row['deliver_finish_time'] > 0) {
                    $row['deliver_finish_time'] = date('Y-m-d H:i:s', $row['deliver_finish_time']);
                }
                if ($row['deliver_launch_time'] > 0) {
                    $row['deliver_launch_time'] = date('Y-m-d H:i:s', $row['deliver_launch_time']);
                }
                $finale_data[] = $row;
            }

        // 非站长
        } else {
            $param = [];
            switch ($StaffGrade) {
                case 1:
                    $staff_grade = ' and major_id = :masterId  ';
                    $param[':masterId'] = $master_id;
                    break;
                case 2:
                    $staff_grade = ' and minor_id = :masterId  ';
                    $param[':masterId'] = $master_id;
                    break;
                case 3:
                    $staff_grade = ' and agent_id = :masterId  ';
                    $param[':masterId'] = $master_id;
                    break;
            }

            $subsidy_game_sql = 'select layer_name,category_key,daily,layer_id,sum(bet_amount) as bet_amount ,sum(subsidy) as subsidy from daily_user_game_subsidy where 1=1 '.$staff_grade;

            // 子帐号层级权限控制
            if ($masterId > 0) {
                if (empty($data['layer_id'])) {
                    $subsidy_game_sql .= ' And Find_In_Set(`layer_id`, :layerList)';
                    $param[':layerList'] = $layerList;
                }
            }

            if (!empty($data['layer_id'])) {
                // 如果是子帐号则判断该用户是否有该层级权限
                if (!empty($layerArray)) {
                    if (!in_array($data['layer_id'], $layerArray)) {
                        $context->reply(['status' => 500, 'msg' => '没有该层级权限！']);
                        return;
                    }
                } else {
                    $context->reply(['status' => 500, 'msg' => '没有任何层级权限！']);
                    return;
                }

                $layer_id = $data['layer_id'];
                $subsidy_game_sql .= ' AND layer_id = :layerId ';
                $param[':layerId'] = $layer_id;
            }

            $subsidy_game_sql .= ' group by daily,layer_id,category_key,layer_name ORDER BY daily Desc,layer_id asc';
            $subsidy_game_data = iterator_to_array($mysql_report->query($subsidy_game_sql, $param));
            $finale_data = [];
            $twoTranslation = [];
            $threeTranslation = [];
            $fourTranslation = [];
            $is_deliver = '';
            $deliver_time_start = '';
            $deliver_time_end = '';
            if (!empty($subsidy_game_data) && $subsidy_game_data[0] != null) {
                foreach ($subsidy_game_data as $value) {
                    $time_start = 'true';
                    $time_end = 'true';
                    $diver_judge_no = 'true';
                    $diver_judge_yes = 'true';
                    $fourTranslation = end($finale_data);
                    $threeTranslation = $fourTranslation;
                    if (!empty($data['is_deliver']) && $data['is_deliver'] == 0) {
                        $is_deliver = 0;
                    }
                    if (!empty($data['is_deliver']) && $data['is_deliver'] == 1) {
                        $is_deliver = 1;
                    }
                    if (!empty($data['deliver_time_start']) && empty($data['deliver_time_end'])) {
                        if (date('Y-m-d H:i', strtotime($data['deliver_time_start'])) != $data['deliver_time_start']) {
                            $context->reply(['status' => 202, 'msg' => '初始时间类型错误']);
                            return;
                        }
                        $deliver_time_start = strtotime($data['deliver_time_start']);
                    }
                    if (!empty($data['deliver_time_end']) && empty($data['deliver_time_start'])) {
                        if (date('Y-m-d H:i', strtotime($data['deliver_time_end'])) != $data['deliver_time_end']) {
                            $context->reply(['status' => 203, 'msg' => '结束时间类型错误']);
                            return;
                        }
                        $deliver_time_end = strtotime($data['deliver_time_end']);
                    }
                    if (!empty($data['deliver_time_end']) && !empty($data['deliver_time_start'])) {
                        if (date('Y-m-d H:i', strtotime($data['deliver_time_end'])) != $data['deliver_time_end']) {
                            $context->reply(['status' => 203, 'msg' => '结束时间类型错误']);
                            return;
                        }
                        if (date('Y-m-d H:i', strtotime($data['deliver_time_start'])) != $data['deliver_time_start']) {
                            $context->reply(['status' => 202, 'msg' => '初始时间类型错误']);
                            return;
                        }
                        if (strtotime($data['deliver_time_start']) > strtotime($data['deliver_time_end'])) {
                            $context->reply(['status' => 204, 'msg' => '初始时间不可大于结束时间']);
                            return;
                        }
                        $deliver_time_start = strtotime($data['deliver_time_start']);
                        $deliver_time_end = strtotime($data['deliver_time_end']);
                    }
                    if (!$threeTranslation || $threeTranslation['daily'] != $value['daily'] || $threeTranslation['layer_id'] != $value['layer_id']) {
                        $twoTranslation = [
                            'daily' => $value['daily'],
                            'layer_id' => $value['layer_id'],
                            'layer_name' => $value['layer_name'],
                            'bet_all' => $value['bet_amount'],
                            'subsidy_all' => $value['subsidy'],
                            'bet_lottery' => 0,
                            'subsidy_lottery' => 0,
                            'bet_video' => 0,
                            'subsidy_video' => 0,
                            'bet_game' => 0,
                            'subsidy_game' => 0,
                            'bet_sports' => 0,
                            'subsidy_sports' => 0,
                            'bet_cards' => 0,
                            'subsidy_cards' => 0,
                            'auto_deliver' => 0,
                            'deliver_staff_id' => 0,
                            'deliver_staff_name' => 0,
                            'deliver_launch_time' => 0,
                            'deliver_finish_time' => 0,
                            'is_promit_subsidy' => 1,
                        ];
                        switch ($value['category_key']) {
                            case 'video':
                                $twoTranslation['bet_video'] += $value['bet_amount'];
                                $twoTranslation['subsidy_video'] += $value['subsidy'];
                                break;
                            case 'game':
                                $twoTranslation['bet_game'] += $value['bet_amount'];
                                $twoTranslation['subsidy_game'] += $value['subsidy'];
                                break;
                            case 'sports':
                                $twoTranslation['bet_sports'] += $value['bet_amount'];
                                $twoTranslation['subsidy_sports'] += $value['subsidy'];
                                break;
                            case 'cards':
                                $twoTranslation['bet_cards'] += $value['bet_amount'];
                                $twoTranslation['subsidy_cards'] += $value['subsidy'];
                                break;
                            case 'lottery':
                                $twoTranslation['bet_lottery'] += $value['bet_amount'];
                                $twoTranslation['subsidy_lottery'] += $value['subsidy'];
                                break;
                        }
                        $daily = $value['daily'];
                        $layer = $value['layer_id'];
                        $is_diver_sql = 'select auto_deliver,deliver_staff_id,deliver_staff_name,deliver_launch_time,deliver_finish_time from daily_layer_subsidy where daily=:daily and  layer_id=:layer ';
                        $param = [
                            ':daily' => $daily,
                            ':layer' => $layer,
                        ];
                        $dive_data = iterator_to_array($mysql_report->query($is_diver_sql, $param));
                        if (isset($dive_data[0]) && $dive_data[0] != null) {
                            $twoTranslation['auto_deliver'] = $dive_data[0]['auto_deliver'];
                            $twoTranslation['deliver_launch_time'] = $dive_data[0]['deliver_launch_time'];
                            $twoTranslation['deliver_finish_time'] = $dive_data[0]['deliver_finish_time'];
                        }
                        if (in_array($layer, $promitList)) {
                            $twoTranslation['is_promit_subsidy'] = 0;
                        }

                        if ($deliver_time_start != '') {
                            if ($twoTranslation['deliver_finish_time'] < $deliver_time_start) {
                                $time_start = 'false';
                            }
                        }
                        if ($deliver_time_end != '') {
                            if ($twoTranslation['deliver_finish_time'] > $deliver_time_end) {
                                $time_end = 'false';
                            }
                        }
                        if ($deliver_time_end == '' && $deliver_time_start == '') {
                            if ($is_deliver != '' && $is_deliver == 0 && $twoTranslation['deliver_finish_time'] != 0) {
                                $diver_judge_no = 'false';
                            }
                        }
                        if ($is_deliver != '' && $is_deliver == 1 && $twoTranslation['deliver_finish_time'] == 0) {
                            $diver_judge_yes = 'false';
                        }
                        if ($diver_judge_yes == 'true' && $diver_judge_no == 'true' && $time_end == 'true' && $time_start == 'true') {
                            if ($twoTranslation['deliver_launch_time'] > 0) {
                                $twoTranslation['deliver_launch_time'] = date('Y-m-d H:i:s', $twoTranslation['deliver_launch_time']);
                            }
                            if ($twoTranslation['deliver_finish_time'] > 0) {
                                $twoTranslation['deliver_finish_time'] = date('Y-m-d H:i:s', $twoTranslation['deliver_finish_time']);
                            }
                            array_push($finale_data, $twoTranslation);
                        }
                    } else {
                        $threeTranslation['bet_all'] += $value['bet_amount'];
                        $threeTranslation['subsidy_all'] += $value['subsidy'];
                        switch ($value['category_key']) {
                            case 'video':
                                $threeTranslation['bet_video'] += $value['bet_amount'];
                                $threeTranslation['subsidy_video'] += $value['subsidy'];
                                break;
                            case 'game':
                                $threeTranslation['bet_game'] += $value['bet_amount'];
                                $threeTranslation['subsidy_game'] += $value['subsidy'];
                                break;
                            case 'sports':
                                $threeTranslation['bet_sports'] += $value['bet_amount'];
                                $threeTranslation['subsidy_sports'] += $value['subsidy'];
                                break;
                            case 'cards':
                                $threeTranslation['bet_cards'] += $value['bet_amount'];
                                $threeTranslation['subsidy_cards'] += $value['subsidy'];
                                break;
                            case 'lottery':
                                $threeTranslation['bet_lottery'] += $value['bet_amount'];
                                $threeTranslation['subsidy_lottery'] += $value['subsidy'];
                                break;
                        }
                    }

                    if ($threeTranslation && $threeTranslation['daily'] == $value['daily'] && $threeTranslation['layer_id'] == $value['layer_id']) {
                        array_pop($finale_data);
                        array_push($finale_data, $threeTranslation);
                    }
                }
            }
        }

        // 保留小数点后两位
        foreach($finale_data as $k => $v) {
            $finale_data[$k]['bet_all'] = $this->intercept_num($v['bet_all']);
            $finale_data[$k]['bet_cards'] = $this->intercept_num($v['bet_cards']);
            $finale_data[$k]['bet_game'] = $this->intercept_num($v['bet_game']);
            $finale_data[$k]['bet_lottery'] = $this->intercept_num($v['bet_lottery']);
            $finale_data[$k]['bet_sports'] = $this->intercept_num($v['bet_sports']);
            $finale_data[$k]['bet_video'] = $this->intercept_num($v['bet_video']);
            $finale_data[$k]['subsidy_all'] = $this->intercept_num($v['subsidy_all']);
            $finale_data[$k]['subsidy_cards'] = $this->intercept_num($v['subsidy_cards']);
            $finale_data[$k]['subsidy_game'] = $this->intercept_num($v['subsidy_game']);
            $finale_data[$k]['subsidy_lottery'] = $this->intercept_num($v['subsidy_lottery']);
            $finale_data[$k]['subsidy_sports'] = $this->intercept_num($v['subsidy_sports']);
            $finale_data[$k]['subsidy_video'] = $this->intercept_num($v['subsidy_video']);
        }

        $context->reply(['status' => 200, 'msg' => '获取数据成功', 'layer_list' => $layer_list, 'list' => $finale_data]);
        return;
    }
}
