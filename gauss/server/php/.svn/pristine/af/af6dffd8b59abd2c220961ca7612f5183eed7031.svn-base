<?php

namespace Site\Websocket\Member\BetRecord;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;
use Lib\Calender;

/**
 * BetRecordAll class.
 *
 * @description   会员管理-投注记录
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/BetRecord/BetRecordAll {"game_key":"six_hk","result":"0","start_time":"2018-12-18","end_time":"2018-12-20","chase":"1","user_key":"user123"}
 * 搜索参数：period:期号，game_key:彩票类型，result:订单状态，launch_time:下注时间，chase_mode:是否追号，user_key:会员账号
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class BetRecordAll extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $search_period = empty($data['period']) ? '' : $data['period'];
        $gameKey = empty($data['game_key']) ? '' : $data['game_key'];
        $result = empty($data['result']) ? '' : $data['result'];
        $start_time = empty($data['start_time']) ? '' : $data['start_time'];
        $mysql = $config->data_staff;
        $mysqlUser = $config->data_user;
        $cache = $config->cache_site;
        $end_time = empty($data['end_time']) ? '' : $data['end_time'];
        $chase = empty($data['chase']) ? '' : $data['chase'];
        $userKey = empty($data['user_key']) ? '' : $data['user_key'];
        $staffId = $context->getInfo('StaffId');
        $staffGrade = $context->getInfo('StaffGrade');
        $gameList = json_decode($context->getInfo('GameList'));
        $MasterId = $context->getInfo('MasterId');
        if ($MasterId != 0) {
            $staffId = $MasterId;
        }
        //检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));

        if (!in_array('user_bet', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $agent_list = [0];
        $layer_list = [0];
        switch ($staffGrade) {
            case 0:
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_list = [];

                    foreach ($mysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':layer_list' => $layer_list]);
                } else {
                    $user_sql = 'select user_id  from user_info_intact';
                    $query = $mysqlUser->query($user_sql);
                }

                $user_list = [];
                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
            case 1:
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE major_id=:major_id';
                $agent_list = [];
                foreach ($mysql->query($sql, [':major_id' => $staffId]) as $row) {
                    $agent_list[] = $row['agent_id'];
                }
                $agent_list = empty($agent_list) ? [0] : $agent_list;
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_list = [];
                    foreach ($mysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list and layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list, ':layer_list' => $layer_list]);
                } else {
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list]);
                }

                $user_list = [];
                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
            case 2:
                $sql = 'SELECT agent_id FROM staff_struct_agent WHERE minor_id=:major_id';
                foreach ($mysql->query($sql, [':major_id' => $staffId]) as $row) {
                    $agent_list[] = $row['agent_id'];
                }
                $agent_list = empty($agent_list) ? [0] : $agent_list;
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_list = [];
                    foreach ($mysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list and layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list, ':layer_list' => $layer_list]);
                } else {
                    $user_sql = 'select user_id from user_info_intact where agent_id in :agent_list';
                    $query = $mysqlUser->query($user_sql, [':agent_list' => $agent_list]);
                }

                $user_list = [];
                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
            case 3:
                if (empty(!$MasterId)) {
                    $accout_sql = 'select layer_id from staff_layer where staff_id=:staff_id';
                    $layer_list = [];
                    foreach ($mysql->query($accout_sql, [':staff_id' => $context->getInfo('StaffId')]) as $row) {
                        $layer_list[] = $row['layer_id'];
                    }
                    $layer_list = empty($layer_list) ? [0] : $layer_list;
                    $user_sql = 'select user_id from user_info_intact where agent_id =:agent_id and layer_id in :layer_list';
                    $query = $mysqlUser->query($user_sql, [':layer_list' => $layer_list, ':agent_id' => $staffId]);
                } else {
                    $user_sql = 'select user_id from user_info_intact where agent_id = :agent_id';
                    $query = $mysqlUser->query($user_sql, [':agent_id' => $staffId]);
                }

                $user_list = [];
                foreach ($query as $row) {
                    $user_list[] = $row['user_id'];
                }
                break;
        }

        if (empty($user_list)) {
            $user_list = [0];
        }
        $sql = 'SELECT bet_serial,status,user_id,user_key,launch_time,period,game_key,play_key,number,quantity,period_list,settle_time,chase_mode,result,bet_launch,bonus,rebate,revert,(bonus-bet_launch+rebate) AS bet_profit FROM bet_unit_intact WHERE user_id in :user_list';
        $params = [':user_list' => $user_list];
        if (!empty($search_period)) {
            $sql .= ' and period=:period';
            $params[':period'] = $search_period;
        }
        if (!empty($gameKey)) {
            $sql .= ' and game_key=:game_key';
            $params[':game_key'] = $gameKey;
        }
        if ($chase == 1) {
            $sql .= ' and period_list IS NOT NULL';
        } elseif ($chase == 2) {
            $sql .= ' and period_list IS NULL';
        }
        if (!empty($userKey)) {
            $sql .= ' and user_key=:user_key';
            $params[':user_key'] = $userKey;
        }
        if (in_array($result, [0, 1, 2, 3])) {
            $sql .= ' and result=:result';
            $params[':result'] = $result;
        }
        if ($result == 4) {
            $sql .= ' and status=:status';
            $params[':status'] = -1;
        }
        if (!empty($start_time) && !empty($end_time)) {
            $start = date('Y-m-d', strtotime($start_time)).' 00:00:00';
            $end = date('Y-m-d', strtotime($end_time)).'23:59:59';
            $sql .= ' and launch_time BETWEEN :start_time and :end_time';
            $params[':start_time'] = strtotime($start);
            $params[':end_time'] = strtotime($end);
        }
        $sql .= ' order by launch_time desc, period desc LIMIT 200 ';
        $betList = [];
        $deal_list = $config->deal_list;
        $betLists = [];
        foreach ($deal_list as $deal) {
            $mysql = $config->__get('data_'.$deal);
            $list = iterator_to_array($mysql->query($sql, $params));
            if (!empty($list)) {
                foreach ($list as $key => $val) {
                    $play_key = $val['play_key'];
                    $betList['bet_serial'] = $val['bet_serial'];
                    $betList['user_id'] = $val['user_id'];
                    $betList['user_key'] = $val['user_key'];
                    $betList['launch_time'] = date('Y-m-d H:i:s', $val['launch_time']);
                    $betList['period'] = $val['period'];
                    $betList['game_name'] = $context->getInfo($val['game_key']);
                    $betList['play_key'] = $val['play_key'];
                    $betList['play_name'] = $context->getInfo($val['play_key']);
                    $tmp_number = json_decode($val['number'], true);
                    $res = [];
                    $tmp_num = [];
                    $settle_time = empty($val['settle_time']) ? time() : $val['settle_time'];
                    $zodicList = Calender::getZodiacList($settle_time);
                    $number = $this->NumberSettle($tmp_number, $res);
                    foreach ($number as $k => $v) {
                        if (in_array($val['play_key'], ['six_szodiac', 'six_szodiac6hit', 'six_szodiac6miss', 'six_zodiac1hit', 'six_zodiac2hit', 'six_zodiac2miss', 'six_zodiac3hit', 'six_zodiac3miss', 'six_zodiac4hit', 'six_zodiac4miss'])) {
                            $n = intval(substr($v, strripos($v, '_') + 1));
                            $sx = $zodicList[$n];
                            switch ($sx) {
                                case 'rat':
                                $winName = '鼠';
                                break;
                                case 'ox':
                                $winName = '牛';
                                break;
                                case 'tiger':
                                $winName = '虎';
                                break;
                                case 'rabbit':
                                $winName = '兔';
                                break;
                                case 'dragon':
                                $winName = '龙';
                                break;
                                case 'snake':
                                $winName = '蛇';
                                break;
                                case 'horse':
                                $winName = '马';
                                break;
                                case 'sheep':
                                $winName = '羊';
                                break;
                                case 'monkey':
                                $winName = '猴';
                                break;
                                case 'chicken':
                                $winName = '鸡';
                                break;
                                case 'dog':
                                $winName = '狗';
                                break;
                                case 'pig':
                                $winName = '猪';
                                break;
                                default:
                                $winName = '';
                                break;
                            }
                        } else {
                            $winName = $cache->hget('WinList', $play_key.'-'.$v);
                        }
                        $tmp_num[] = $winName;
                    }
                    $betList['number'] = $tmp_num;
                    $betList['quantity'] = $val['quantity'];
                    $period = json_decode($val['period_list'], true);
                    $betList['chase_num'] = empty($period) ? 0 : count($period);
                    $chase_mode = json_decode($val['chase_mode'], true);
                    $betList['step'] = !empty($chase_mode['step'])?$chase_mode['step']:'';
                    $betList['type'] = !empty($chase_mode['type'])?$chase_mode['type']:'';
                    $betList['multiple'] = !empty($chase_mode['multiple'])?$chase_mode['multiple']:'';
                    $betList['result'] = $val['result'];
                    $betList['bet_launch'] = $this->intercept_num($val['bet_launch']);
                    $betList['bonus'] = $this->intercept_num($val['bonus']);
                    $betList['rebate'] = $this->intercept_num($val['rebate']);
                    $betList['revert'] = $this->intercept_num($val['revert']);
                    $betList['bet_profit'] = $this->intercept_num($val['bet_profit']);
                    $chase_num = empty($period) ? 0 : count($period);
                    if ($result == 4) {
                        $betList['result'] = 4;
                    }
                    if ($val['status'] == -1) {
                        $betList['result'] = 4;
                    }

                    if (!empty($val['revert'])) {
                        $betList['bet_profit'] = '0.00';
                    }
                    $betLists[] = $betList;
                }
            }
        }
        array_multisort(array_column($betLists, 'launch_time'), SORT_DESC, $betLists);
        $context->reply(['status' => 200,
            'msg' => '获取成功',
            'game_list' => $gameList,
            'data' => $betLists, ]
        );
    }

    private function NumberSettle($arr, &$res)
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $this->NumberSettle($v, $res);
            } else {
                $res[] = $v;
            }
        }

        return $res;
    }
}
