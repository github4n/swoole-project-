<?php

namespace App\Websocket\BetRecord;

use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

/*
 * 注单--注单记录--注单详情
 * BetRecord/BetDetail {"bet_serial":"190218091639000002","rule_id":0}
 * bet_serial：投注单号
 * rule_id:内容序号
 * status:
 * 0 待开奖
 * 1 未中奖
 * 2 已中奖
 * 3 和
 * -1 追号停止/注单取消
 * -2 期号取消
 * */

class BetDetail extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $betSerial = $data['bet_serial'];
        $rule_id = $data['rule_id'];
        if (empty($betSerial)) {
            $context->reply(['status' => 201, 'msg' => '投注单号为空']);
            return;
        }
        if ($rule_id!=0 && $rule_id == null) {
            $context->reply(['status' => 201, 'msg' => '内容序号为空']);
            return;
        }
        $userId = $context->getInfo('UserId');
        $deal_key = $context->getInfo('DealKey');
        $mysql = $config->__get("data_" . $deal_key);

        $publicMysql = $config->data_public;
        $staffMysql = $config->data_staff;

        //查询是否是为追号订单
        $sql = 'SELECT bet_serial FROM bet_chase WHERE bet_serial=:bet_serial';
        $param = [':bet_serial' => $betSerial];
        $result = $mysql->execute($sql, $param);
        if ($result == 1) {

            //查询彩种名,投注号码
            $key_sql = 'SELECT game_key,play_key,period,launch_time,bonus,number,bet_launch,price,rebate,multiple,quantity,chase_mode,stop_mode FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_id=:user_id AND rule_id=:rule_id';
            $key_param = [
                ':bet_serial' => $betSerial,
                ':user_id' => $userId,
                ':rule_id' => $rule_id
            ];
            foreach ($mysql->query($key_sql, $key_param) as $game) {
                $game_key = $game['game_key'];
                $play_key = $game['play_key'];
                $periods[] = $game['period'];
                $bonus[] = $game['bonus'];
                $launch_time = $game['launch_time'];
                $bet_launch[] = $game['bet_launch'];
                $rebate[] = $game['rebate'];
                $chase_mode = json_decode($game['chase_mode'], true);
            }
            if (empty($game_key)) {
                $context->reply(['status' => 202, 'msg' => '没有这个订单']);
                return;
            }
            //查询投注号码
            $tmp_num = [];
            $num_sql = 'SELECT DISTINCT number FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_id=:user_id AND rule_id=:rule_id';
            foreach ($mysql->query($num_sql,$key_param) as $item)
            {
                $tmp_num = json_decode($item['number'],true);
            }

            //查询彩种名字，玩法名字
            $name_sql = 'SELECT DISTINCT game_name,play_name FROM lottery_game_play_intact WHERE game_key=:game_key AND play_key=:play_key';
            $param = [
                ':game_key' => $game_key,
                ':play_key' => $play_key
            ];
            foreach ($publicMysql->query($name_sql, $param) as $item) {
                $play_name = $item['play_name'];
            }

            //查找投注号码对应的名称
            $originalNum = [];
            $win_name_sql = 'SELECT win_name FROM lottery_game_win_intact WHERE win_key=:win_key AND game_key=:game_key';
            $win_rate_sql = 'SELECT bonus_rate FROM lottery_game_win WHERE game_key=:game_key AND play_key=:play_key AND win_key=:win_key';
            for ($i=0;$i<count($tmp_num);$i++)
            {
                if (is_array($tmp_num[$i]))
                {
                    $ret = [];
                    $test = $this->NumberSettle($tmp_num[$i],$ret);
                    foreach ($test as $row)
                    {
                        $win_name_param = [
                            ':win_key' => $row,
                            ':game_key' => $game_key,
                        ];
                        foreach ($publicMysql->query($win_name_sql, $win_name_param) as $win)
                        {
                            $originalNum[] = $win['win_name'];
                        }

                        //查赔率
                        $win_rate_param = [
                            ':game_key' => $game_key,
                            ':play_key' => $play_key,
                            ':win_key' => $row
                        ];

                        foreach ($staffMysql->query($win_rate_sql, $win_rate_param) as $item)
                        {
                            $rate_tmp[] = $item['bonus_rate'];
                        }
                    }
                        $originalNum[] = '|';
                }else
                {
                    $win_name_param = [
                        ':win_key' => $tmp_num[$i],
                        ':game_key' => $game_key,
                    ];
                    foreach ($publicMysql->query($win_name_sql, $win_name_param) as $win)
                    {
                        $originalNum[] = $win['win_name'];
                    }

                    //查赔率
                    $win_rate_param = [
                        ':game_key' => $game_key,
                        ':play_key' => $play_key,
                        ':win_key' => $tmp_num[$i]
                    ];

                    foreach ($staffMysql->query($win_rate_sql, $win_rate_param) as $item)
                    {
                        $rate_tmp[] = $item['bonus_rate'];
                    }
                }
            }

            if (end($originalNum) == '|')
            {
                unset($originalNum[count($originalNum)-1]);
            }

            //设置赔率
            $win_rate = [];
            sort($rate_tmp);
            if ($rate_tmp[0] != end($rate_tmp)) {
                $win_rate = [$rate_tmp[0], end($rate_tmp)];
            } else {
                $win_rate[] = $rate_tmp[0];
            }

            $winningNum = [];
            //总体数据
            $totalData['game_key'] = $game_key;
            $totalData['period_count'] = count(array_unique($periods));
            $totalData['bonus'] =array_sum($bonus);
            $totalData['bet_number'] = $originalNum;
            $totalData['bet_serial'] = $betSerial;
            $totalData['play_name'] = $play_name;
            $totalData['step'] = $chase_mode['step'];
            $totalData['multiple'] = $chase_mode['multiple'];
            $totalData['bet_launch'] = array_sum($bet_launch);
            $totalData['win_rate'] = $win_rate;
            $totalData['rebate'] = array_sum($rebate);
            $totalData['launch_time'] = $launch_time;
            //每期开奖结果
            $total_param = [
                ':bet_serial' => $betSerial,
                ':user_id' => $userId,
                ':rule_id' => $rule_id
            ];
            $detail_sql = 'SELECT period,SUM(bet_launch) AS bet_launch,SUM(bonus) AS bonus_all FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_id=:user_id AND rule_id=:rule_id GROUP BY period';
            $status_sql = 'SELECT period,status,result FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_id=:user_id AND rule_id=:rule_id ';
            $win_num_sql = 'SELECT normal1,normal2,normal3,normal4,normal5,normal6,normal7,normal8,normal9,normal10,normal11,normal12,normal13,normal14,normal15,normal16,normal17,normal18,normal19,normal20,special1,special2 FROM lottery_number WHERE period=:period AND game_key=:game_key ';
            $status = [];
            $results = [];
            foreach ($mysql->query($status_sql, $total_param) as $item) {
                $status[$item['period']] = $item['status'];
                $results[$item['period']][] = $item['result'];
            }

            foreach ($mysql->query($detail_sql, $total_param) as $row) {
                $detailList[] = $row;
            }
            foreach ($detailList as $k => $v) {
                $bet_period = $v['period'];
                $detailData[$k]['period'] = $bet_period;
                $win_num_param = [
                    'period' => $bet_period,
                    ':game_key' => $game_key
                ];

                foreach ($publicMysql->query($win_num_sql, $win_num_param) as $row) {
                    $winningNum[$bet_period] = $row;
                }
                if (!empty($winningNum[$bet_period])) {
                    for ($i = 1; $i < 21; $i++) {
                        if ($winningNum[$bet_period]['normal' . $i] == -1) {
                            unset($winningNum[$bet_period]['normal' . $i]);
                        }
                        if ($i < 3) {
                            if ($winningNum[$bet_period]['special' . $i] == -1) {
                                unset($winningNum[$bet_period]['special' . $i]);
                            }
                        }
                    }
                }
                if ((array_sum($results[$bet_period]) == 0 && $status[$bet_period] == 1) || (array_sum($results[$bet_period]) == 0 && $status[$bet_period] == 0)) {
                    $detailData[$k]['winning_number'] = 0;
                    $detailData[$k]['bonus'] = $v['bonus_all'];
                    $detailData[$k]['status'] = '0';
                } else if (in_array(2,$results[$bet_period])) {
                    $detailData[$k]['winning_number'] = empty($winningNum[$bet_period]) ? null : array_values($winningNum[$bet_period]);
                    $detailData[$k]['bonus'] = $v['bonus_all'];
                    $detailData[$k]['status'] = '2';
                } else if (in_array(1,$results[$bet_period]) && $v['bonus_all'] ==0) {
                    $detailData[$k]['winning_number'] = empty($winningNum[$bet_period]) ? null : array_values($winningNum[$bet_period]);
                    $detailData[$k]['bonus'] = $v['bonus_all'];
                    $detailData[$k]['status'] = '1';
                } else if ($status[$bet_period] == -1 && array_sum($results[$bet_period]) == 0) {
                    $detailData[$k]['winning_number'] = empty($winningNum[$bet_period]) ? null : array_values($winningNum[$bet_period]);
                    $detailData[$k]['bonus'] = $v['bonus_all'];
                    $detailData[$k]['status'] = '-1';
                }else if ($status[$bet_period] == -2)
                {
                    $detailData[$k]['bonus'] = '-2';
                }else if(in_array(3,$results[$bet_period]))
                {
                    $detailData[$k]['winning_number'] = empty($winningNum[$bet_period]) ? null : array_values($winningNum[$bet_period]);
                    $detailData[$k]['bonus'] = $v['bonus_all'];
                    $detailData[$k]['status'] = '3';
                }
                $detailData[$k]['bet_launch'] = $v['bet_launch'];
            }
            $returnData = [$totalData, $detailData];

        } else {
            //查询彩种名,投注号码
            $key_sql = 'SELECT game_key,play_key,period,launch_time,quantity,bet_launch,rebate,bonus,price FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_id=:user_id AND rule_id=:rule_id';
            $key_param = [
                ':bet_serial' => $betSerial,
                ':user_id' => $userId,
                ':rule_id' => $rule_id
            ];
            foreach ($mysql->query($key_sql, $key_param) as $game) {
                $game_key = $game['game_key'];
                $play_key = $game['play_key'];
                $period = $game['period'];
                $launch_time = $game['launch_time'];
                $quantity = $game["quantity"];
                $bet_launch = $game["bet_launch"];
                $rebate = $game["rebate"];
                $bonus = $game["bonus"];
                $price = $game["price"];
            }
            if (empty($period)) {
                $context->reply(['status' => 202, 'msg' => '没有这个订单']);
                return;
            }
            //查询win_name
            $win_name_sql = 'SELECT win_name FROM lottery_game_win_intact WHERE win_key=:win_key AND game_key=:game_key';

            //查询彩种名字，玩法名字
            $name_sql = 'SELECT DISTINCT game_name,play_name FROM lottery_game_play_intact WHERE game_key=:game_key AND play_key=:play_key';

            //查找赔率
            $rate_sql = 'SELECT bonus_rate FROM lottery_game_win WHERE game_key=:game_key AND play_key=:play_key AND win_key=:win_key';


            $sql = 'SELECT number FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_id=:user_id AND rule_id=:rule_id';
            $param = [
                ':game_key' => $game_key,
                ':play_key' => $play_key
            ];
            $params = [
                ':bet_serial' => $betSerial,
                ':user_id' => $userId,
                ':rule_id' => $rule_id
            ];

            //查询开奖号码
            $normal_number = [];
            foreach ($mysql->query($sql, $params) as $row) {
                $normal_number = json_decode($row['number']);
            }
            //查询玩法名称
            foreach ($publicMysql->query($name_sql, $param) as $item) {
                //$game_name = $item['game_name'];
                $play_name = $item['play_name'];
            }

            $originalNum = [];
            for ($i=0;$i<count($normal_number);$i++)
            {
                if (is_array($normal_number[$i]))
                {
                    $ret = [];
                    $test = $this->NumberSettle($normal_number[$i],$ret);
                    foreach ($test as $row)
                    {
                        $bet_num = $row;
                        $win_name_param = [
                            ':win_key' => $bet_num,
                            ':game_key' => $game_key
                        ];
                        //查询投注号码
                        foreach ($publicMysql->query($win_name_sql, $win_name_param) as $win)
                        {
                            $originalNum[] = $win['win_name'];
                        }
                        //查赔率
                        $rate_param = [
                            ':game_key' => $game_key,
                            ':play_key' => $play_key,
                            ':win_key' => $bet_num
                        ];
                        foreach ($staffMysql->query($rate_sql, $rate_param) as $rate)
                        {
                            $bonus_rate[] = $rate['bonus_rate'];
                        }
                    }
                        $originalNum[] = '|';

                }else
                {
                    $win_name_param = [
                        ':win_key' => $normal_number[$i],
                        ':game_key' => $game_key
                    ];
                    //查询投注号码
                    foreach ($publicMysql->query($win_name_sql, $win_name_param) as $win)
                    {
                        $originalNum[] = $win['win_name'];
                    }
                    //查赔率
                    $rate_param = [
                        ':game_key' => $game_key,
                        ':play_key' => $play_key,
                        ':win_key' => $normal_number[$i]
                    ];
                    foreach ($staffMysql->query($rate_sql, $rate_param) as $rate)
                    {
                        $bonus_rate[] = $rate['bonus_rate'];
                    }
                }
            }

            if (end($originalNum) == '|')
            {
                unset($originalNum[count($originalNum)-1]);
            }

            sort($bonus_rate);
            if ($bonus_rate[0] != end($bonus_rate))
            {
                $win_rate = [$bonus_rate[0], end($bonus_rate)];
            } else {
                $win_rate[] = $bonus_rate[0];

            }
            //查询开奖号码
            $win_number = [];
            $lottery_number = [];
            $number_sql = 'SELECT normal1,normal2,normal3,normal4,normal5,normal6,normal7,normal8,normal9,normal10,normal11,normal12,normal13,normal14,normal15,normal16,normal17,normal18,normal19,normal20,special1,special2 FROM lottery_number WHERE period=:period AND game_key=:game_key';
            $number_param = [
                ':period' => $period,
                ':game_key' => $game_key
            ];
            foreach ($publicMysql->query($number_sql, $number_param) as $winNum) {
                $win_number[] = $winNum;
            }
            foreach ($win_number as $k => $v) {
                for ($i = 1; $i < 21; $i++) {
                    if ($v['normal' . $i] > -1) {
                        $lottery_number['normal' . $i] = $v['normal' . $i];
                    }
                }
                if ($v['special1'] > -1) {
                    $lottery_number['special1'] = $v['special1'];
                }
                if ($v['special2'] > -1) {
                    $lottery_number['special2'] = $v['special2'];
                }
            }


            $totalData['game_key'] = $game_key;
            $totalData['period'] = $period;
            $totalData['bonus'] = $bonus;
            $totalData['winningNumber'] = $lottery_number;
            $totalData['play_name'] = $play_name;
            $totalData['lauch_amount'] = $bet_launch;
            $totalData['bet_number'] = $originalNum;
            $totalData['bet_serial'] = $betSerial;
            $totalData['quantity'] = $quantity;
            $totalData['price'] = $price;
            $totalData['win_rate'] = $win_rate;
            $totalData['rebate_count'] = $rebate;
            $totalData['launch_time'] = $launch_time;

            $returnData[] = $totalData;
        }

        //返回数据
        $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => $returnData]);
    }
    private function NumberSettle($arr,&$res)
    {
        foreach ($arr as $k => $v)
        {
            if (is_array($v))
            {
                $this->NumberSettle($v,$res);
            }else
            {
                $res[] = $v;
            }
        }
        return $res;
    }
}
