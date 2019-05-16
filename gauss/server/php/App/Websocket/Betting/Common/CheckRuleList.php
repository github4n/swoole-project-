<?php

/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 18-12-29
 * Time: 上午11:42
 */

namespace App\Websocket\Betting\Common;

class CheckRuleList {

    public function checkRule(array $rule, $config) {

        //定义返回
        $result = [
            'status' => 200,
            'msg' => '成功',
            'data' => ''
        ];

        //检查玩法key及内容
        if (!isset($rule['play_key']) || empty($rule['play_key'])) {
            $result['status'] = 305;
            $result['msg'] = '玩法有误';
            return $result;
        }

        //投注数类型检查及字段检查
        if (!isset($rule['quantity']) || !is_numeric($rule['quantity']) || empty($rule['quantity'])) {
            $result['status'] = 409;
            $result['msg'] = '投注数量错误';
            return $result;
        }

        //投注金额key及类型检查
        if (!isset($rule['price']) || !is_numeric($rule['price']) || empty($rule['price'])) {
            $result['status'] = 408;
            $result['msg'] = '投注金额错误';
            return $result;
        }

        $type = $rule['play_key'];

        //监测重复号码
        if ($rule['play_key'] == 'dice_merge3' || $rule['play_key'] == 'dice_pairtow' || $rule['play_key'] == 'dice_merge2') {
            if (empty($rule['number'][0]) || empty($rule['number'][1]) || count($rule['number']) > 2) {
                $result['status'] = 307;
                $result['msg'] = '投注号码有误';
                return $result;
            }
            if (gettype($rule['number'][0]) !== 'array' || gettype($rule['number'][1]) !== 'array') {
                $result['status'] = 306;
                $result['msg'] = '数据传输格式有误';
                return $result;
            }

            $numberTransltion = $rule['number'][1];
            foreach ($rule['number'][0] as $value) {
                array_push($numberTransltion, $value);
            }
            if (count($rule['number'][0]) + count($rule['number'][1]) != count(array_unique($numberTransltion))) {
                $result['status'] = 303;
                $result['msg'] = '该注号码内有重复值';
                return $result;
            }
        } else {
            if (count($rule['number']) != count(array_unique($rule['number']))) {
                $result['status'] = 303;
                $result['msg'] = '该注号码内有重复值';
                return $result;
            }
        }

        $mysql = $config->data_staff;

        //根据玩法检查范围及各玩法的注数计算
        switch ($type) {
            //梯子
            case 'ladder_from':
            case 'ladder_to' :
            case 'ladder_set' :
            case 'ladder_step':


            //快三
            case 'dice_pair':
                $count = 1;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        $result['data'] = $range;
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'dice_pairtow':
                $count = 1;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                $ruleTranslate = array_merge($rule['number'][0], $rule['number'][1]);
                foreach ($ruleTranslate as $value) {
                    if (!in_array($value, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        $result['data'] = $range;
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combinationSpecial($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;
            case 'dice_sum':
            case 'dice_halfsum':
                $count = 1;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        $result['data'] = $range;
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'dice_any2':
                $count = 2;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;
            case 'dice_merge2':
                $count = 2;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                $ruleTranslate = array_merge($rule['number'][0], $rule['number'][1]);
                foreach ($ruleTranslate as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if ((count($rule['number'][0]) + count($rule['number'][1])) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combinationSpecial($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'dice_any3':
                $count = 3;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {
                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;
            case 'dice_merge3':
                $count = 3;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                $ruleTranslate = array_merge($rule['number'][0], $rule['number'][1]);
                foreach ($ruleTranslate as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if ((count($rule['number'][0]) + count($rule['number'][1])) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combinationDiceMerge3($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } elseif ((count($rule['number'][0]) + count($rule['number'][1])) == $count) {
                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'dice_serialall':
            case 'dice_tripleall':
            case 'dice_triple':


            //赛车PK10
            case 'racer_car1' :
            case 'racer_car2' :
            case 'racer_car3' :
            case 'racer_car4' :
            case 'racer_car5' :
            case 'racer_car6' :
            case 'racer_car7' :
            case 'racer_car8' :
            case 'racer_car9' :
            case 'racer_car10' :
            case 'racer_half1' :
            case 'racer_half2' :
            case 'racer_half3' :
            case 'racer_half4' :
            case 'racer_half5' :
            case 'racer_half6' :
            case 'racer_half7' :
            case 'racer_half8' :
            case 'racer_half9' :
            case 'racer_half10' :
            case 'racer_halfsum' :
            case 'racer_sum':
            case 'racer_versus1' :
            case 'racer_versus2' :
            case 'racer_versus3' :
            case 'racer_versus4' :
            case 'racer_versus5' :
                $count = 1;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;


            //时时彩
            case 'tiktok_any2':
                $count = 2;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'tiktok_any3':
                $count = 3;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'tiktok_any1':
            case 'tiktok_bit1':
            case 'tiktok_bit2':
            case 'tiktok_bit3':
            case 'tiktok_bit4':
            case 'tiktok_bit5':
            case 'tiktok_poker123':
            case 'tiktok_poker234':
            case 'tiktok_poker345':
            case 'tiktok_poker5':
            case 'tiktok_sum123':
            case 'tiktok_sum234':
            case 'tiktok_sum345':
            case 'tiktok_sum':
            case 'tiktok_versus12':
            case 'tiktok_versus13':
            case 'tiktok_versus14':
            case 'tiktok_versus15':
            case 'tiktok_versus23':
            case 'tiktok_versus24':
            case 'tiktok_versus25':
            case 'tiktok_versus34':
            case 'tiktok_versus35':
            case 'tiktok_versus45':
            case 'tiktok_half1':
            case 'tiktok_half2':
            case 'tiktok_half3':
            case 'tiktok_half4':
            case 'tiktok_half5':
                $count = 1;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }

                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;


            //幸运农场
            case 'lucky_link2' :

            case 'lucky_any2' :
                $count = 2;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'lucky_any3' :
                $count = 3;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'lucky_any4' :
                $count = 4;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;


            case 'lucky_any5' :
                $count = 5;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'lucky_ball1':
            case 'lucky_ball2':
            case 'lucky_ball3':
            case 'lucky_ball4':
            case 'lucky_ball5':
            case 'lucky_ball6':
            case 'lucky_ball7':
            case 'lucky_ball8':

            case 'lucky_color1':
            case 'lucky_color2':
            case 'lucky_color3':
            case 'lucky_color4':
            case 'lucky_color5':
            case 'lucky_color6':
            case 'lucky_color7':
            case 'lucky_color8':

            case 'lucky_half1':
            case 'lucky_half2':
            case 'lucky_half3':
            case 'lucky_half4':
            case 'lucky_half5':
            case 'lucky_half6':
            case 'lucky_half7':
            case 'lucky_half8':
            case 'lucky_halfsum':

            case 'lucky_versus1' :
            case 'lucky_versus2' :
            case 'lucky_versus3' :
            case 'lucky_versus4' :
            case 'lucky_wind1':
            case 'lucky_wind2':
            case 'lucky_wind3':
            case 'lucky_wind4':
            case 'lucky_wind5':
            case 'lucky_wind6':
            case 'lucky_wind7':
            case 'lucky_wind8':
                $count = 1;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;


            //六合彩
            case 'six_hit22':
            case 'six_hit2s':
            case 'six_hits2':

            case 'six_zodiac2hit':
            case 'six_zodiac2miss':

            case 'six_tail2hit':
            case 'six_tail2miss':
                $count = 2;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_hit32':
            case 'six_hit33':
            case 'six_zodiac3hit':
            case 'six_zodiac3miss':
            case 'six_tail3hit':
            case 'six_tail3miss':
                $count = 3;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_zodiac4hit':
            case 'six_zodiac4miss':
            case 'six_tail4hit':
            case 'six_tail4miss':
                $count = 4;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_miss10':
                $count = 10;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_miss5':
                $count = 5;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_szodiac6hit':
            case 'six_szodiac6miss':

            case 'six_miss6':
                $count = 6;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_miss7':
                $count = 7;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_miss8':
                $count = 8;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_miss9':
                $count = 9;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            case 'six_n1code':
            case 'six_n1color':
            case 'six_n1half':

            case 'six_n2code':
            case 'six_n2color':
            case 'six_n2half':

            case 'six_n3code':
            case 'six_n3color':
            case 'six_n3half':

            case 'six_n4code':
            case 'six_n4color':
            case 'six_n4half':

            case 'six_n5code':
            case 'six_n5color':
            case 'six_n5half':

            case 'six_n6code':
            case 'six_n6color':
            case 'six_n6half':

            case 'six_normal':
            case 'six_scode':
            case 'six_scolor':
            case 'six_shalfolor':
            case 'six_shalf':
            case 'six_sregion':
            case 'six_stail':
            case 'six_tail':
            case 'six_sumhalf' :
            case 'six_zodiac1hit':

            case 'six_szodiac':

            case 'six_versus12':
            case 'six_versus13':
            case 'six_versus14':
            case 'six_versus15':
            case 'six_versus16':
            case 'six_versus23':
            case 'six_versus24':
            case 'six_versus25':
            case 'six_versus26':
            case 'six_versus34':
            case 'six_versus35':
            case 'six_versus36':
            case 'six_versus45':
            case 'six_versus46':
            case 'six_versus56':


            //十一选五
            case 'eleven_ball1':
            case 'eleven_ball2':
            case 'eleven_ball3':
            case 'eleven_ball4':
            case 'eleven_ball5':

            case 'eleven_half1':
            case 'eleven_half2':
            case 'eleven_half3':
            case 'eleven_half4':
            case 'eleven_half5':
            case 'eleven_halfsum':

            case 'eleven_poker123':
            case 'eleven_poker234':
            case 'eleven_poker345':

            case 'eleven_versus12':
            case 'eleven_versus13':
            case 'eleven_versus14':
            case 'eleven_versus15':
            case 'eleven_versus23':
            case 'eleven_versus24':
            case 'eleven_versus25':
            case 'eleven_versus34':
            case 'eleven_versus35':
            case 'eleven_versus45':

                $count = 1;
                //查询win_key
                $range = [];
                $sql = "SELECT win_key FROM lottery_game_win WHERE play_key = '$type'";
                foreach ($mysql->query($sql) as $row) {
                    $range[] = $row['win_key'];
                }
                //验证投注号码的范围
                foreach ($rule['number'] as $number) {
                    if (!in_array($number, $range)) {
                        $result['status'] = 304;
                        $result['msg'] = '投注号码范围有误';
                        return $result;
                    }
                }
                if (count($rule['number']) > $count) {
                    //排列组合计算注数校验
                    $numberList = $this->combination($rule['number'], $count);
                    if (count($numberList) != $rule['quantity']) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }

                    $result['data'] = $rule;
                    return $result;
                } elseif (count($rule['number']) == $count) {

                    if ($rule['quantity'] != 1) {
                        $result['status'] = 301;
                        $result['msg'] = '投注数有误';
                        return $result;
                    }
                    $result['data'] = $rule;
                    return $result;
                } else {
                    $result['status'] = 300;
                    $result['msg'] = '投注号码数量有误';
                    return $result;
                }
                break;

            default :
                $result['status'] = 455;
                $result['msg'] = '该玩法不存在';
                return $result;
                break;
        }
    }

    /*
     * 排列算法 计算总注数及输出各列数据
     * array $a 选注号码
     * $m  单注所需基数
     */

    public function combination($a, $m) {
        $r = array();

        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i = 0; $i < $n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {

                $b = array_slice($a, $i + 1);
                $c = $this->combination($b, $m - 1);
                foreach ($c as $v) {
                    //array_merge() 函数把一个或多个数组合并为一个数组
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }

    /**
     * 检查快3胆拖3玩法
     * @param $a
     * @param $mcombinationDiceMerge3
     * @return array
     */
    public function combinationDiceMerge3($a, $m) {
        $r = array();
        if (count($a[0]) == 2) {
            foreach ($a[1] as $value) {
                $arrayTranslation = $a[0];
                array_push($arrayTranslation, $value);
                $r[] = $arrayTranslation;
            }
        }
        if (count($a[0]) == 1) {
            $assemble = $this->combination($a[1], 2);
            foreach ($assemble as $value) {
                $r[] = array_merge($a[0], $value);
            }
        }
        return $r;
    }

    /**
     * 拆分快3胆拖2,二同号单选玩法
     * @param $a
     * @param $m
     * @return array
     */
    public function combinationSpecial($a, $m) {
        $r = array();
        foreach ($a[0] as $value1) {
            foreach ($a[1] as $value2) {
                $r[] = [$value1, $value2];
            }
        }
        return $r;
    }

}
