<?php

namespace Site\Task\Lottery\Settle;

/*
 * dice.php
 * @description   快三结算任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/Settle/dice 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class dice extends Base
{
    private $sum;
    private $dice_any;
    private $sumAll;
    private $powe;

    /**
     * 准备结算数据
     * @param array $number
     */
    protected function loadNumber(array $number)
    {
        $this->powe = pow(10, 2);
        // 计算二不同
        for ($i = 1; $i < 4; ++$i) {
            $this->dice_any[] = $number['normal'.($i)];
        }
        //总和
        $this->sumAll = $number['normal1'] + $number['normal2'] + $number['normal3'];
        //判定大小单双
        $this->sum['big'] = $this->sumAll >= 11 && $this->sumAll <= 18 ? true : false;
        $this->sum['small'] = $this->sumAll >= 3 && $this->sumAll <= 10 ? true : false;
        $this->sum['dan'] = $this->sumAll % 2 == 1 ? true : false;
        $this->sum['shuang'] = $this->sumAll % 2 == 0 ? true : false;
    }

    /**
     * 二不同
     * Betting/OrdinaryBetting {"game_key":"dice_ah","rule_list":[{"play_key":"dice_any2","number":["dice_any2_1","dice_any2_2","dice_any2_3","dice_any2_4","dice_any2_5","dice_any2_6"],"price":"10","quantity":"1","rebate_rate":"8"}],"period":"20190114038","multiple":"1"}
     * @param array $rule
     * 354/34
     * @return array
     */
    public function dice_any2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'], 2);
        foreach ($bet_num as $value) {
            $num = array();
            $rate = [];
            foreach ($value as $n) {
                $num[] = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_any2', $n, $rule['rebate_rate']);
            }
            if (in_array(min($num), $this->dice_any) && in_array(max($num), $this->dice_any) && min($num) != max($num)) {
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * 三不同
     * Betting/OrdinaryBetting {"game_key":"dice_ah","rule_list":[{"play_key":"dice_any3","number":["dice_any3_1","dice_any3_2","dice_any3_3","dice_any3_4","dice_any3_5","dice_any3_6"],"price":"10","quantity":"20","rebate_rate":"8"}],"period":"20190114038","multiple":"1"}
     * 113/134
     */
    public function dice_any3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'], 3);
        foreach ($bet_num as $value) {
            $num = []; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num[] = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_any3', $n, $rule['rebate_rate']);
            }
            if (min($this->dice_any) == min($num) && max($this->dice_any) == max($num) && array_sum($num) == $this->sumAll) {
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * Betting/OrdinaryBetting {"game_key":"dice_ah","rule_list":[{"play_key":"dice_merge2","number":[["dice_merge2_1","dice_merge2_4"],["dice_merge2_2","dice_merge2_3","dice_merge2_5","dice_merge2_6"]],"price":"10","quantity":"6","rebate_rate":"4"}],"period":"20190113002","multiple":"1"}
     * 胆拖二
     * 23/625
     */
    public function dice_merge2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combinationSpecial($rule['number']);
        foreach ($bet_num as $value) {
            $num = []; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num[] = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_merge2', $n, $rule['rebate_rate']);
            }
            if (in_array(min($num), $this->dice_any) && in_array(max($num), $this->dice_any)) {
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * Betting/OrdinaryBetting {"game_key":"dice_ah","rule_list":[{"play_key":"dice_merge3","number":[["dice_merge3_1"],["dice_merge3_3","dice_merge3_2","dice_merge3_4","dice_merge3_5","dice_merge3_6"]],"price":"10","quantity":"6","rebate_rate":"8"}],"period":"20190103060","multiple":"1"}
     * 胆拖三
     * 123 /125
     */
    public function dice_merge3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combinationDiceMerge3($rule['number']);
        foreach ($bet_num as $value) {
            $num = []; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num[] = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_merge3', $n, $rule['rebate_rate']);
            }
            if (min($this->dice_any) == min($num) && max($this->dice_any) == max($num) && array_sum($num) == $this->sumAll) {
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
    * Betting/OrdinaryBetting {"game_key":"dice_ah","rule_list":[{"play_key":"dice_pair","number":["dice_pair_1","dice_pair_2","dice_pair_3"],"price":"10","quantity":"3","rebate_rate":"8"}],"period":"20190104051","multiple":"1"}
    * 二同号复选
     * 20190113020
     * 156/5*
    */

    public function dice_pair(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'], 1);
        foreach ($bet_num as $value) {
            $num = []; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num[] = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_pair', $n, $rule['rebate_rate']);
            }
            $unique_arr = array_unique($this->dice_any);
            $repeat_arr = array_diff_assoc($this->dice_any, $unique_arr);
            if (count($repeat_arr) != 0 && array_sum($repeat_arr) / count($repeat_arr) == array_sum($num)) {
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * Betting/OrdinaryBetting {"game_key":"dice_ah","rule_list":[{"play_key":"dice_pairtow","number":[["dice_pairtow_22","dice_pairtow_33"],["dice_pairtow_1","dice_pairtow_5","dice_pairtow_6"]],"price":"10","quantity":"6","rebate_rate":"4"}],"period":"20190104043","multiple":"1"}
     * 二同号单选  443 / 334
     */
    public function dice_pairtow(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combinationSpecial($rule['number']);
        foreach ($bet_num as $value) {
            $num = []; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num[] = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_pairtow', $n, $rule['rebate_rate']);
            }
            $numTranslation = str_split(max($num));
            $numTranslation[] = min($num);
            if (min($this->dice_any) == min($numTranslation) && max($this->dice_any) == max($numTranslation) && array_sum($numTranslation) == $this->sumAll) {//单选   dice_pairtow
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * 和值
     * Betting/OrdinaryBetting {"game_key":"dice_ah","rule_list":[{"play_key":"dice_sum","number":["dice_sum_03"],"price":"10","quantity":"1","rebate_rate":"8"}],"period":"20190102081","multiple":"1"}
     */

    public function dice_sum(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'], 1);
        foreach ($bet_num as $value) {
            $num = ''; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_sum', $n, $rule['rebate_rate']);
            }
            if ($this->sumAll == $num) {
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * 三同号
     * 数据库标记快三最大为6，3同号前端只传其一
     */
    public function dice_triple(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'], 1);
        foreach ($bet_num as $value) {
            $num = []; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num[] = intval(substr($n, strripos($n, '_') + 1));
                $rate[] = $this->getRate('dice_triple', $n, $rule['rebate_rate']);
            }
            $count = array_unique($this->dice_any);
            if (count(array_intersect($count, $num)) == 1 && count($count) == 1) {
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * 和值两面,判定大小单双
     * odd奇数
     * even 偶数
     */
    public function dice_halfsum(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'], 1);
        foreach ($bet_num as $value) {
            $num = []; //取用户投注号码
            $rate = [];
            foreach ($value as $n) {
                $num[] = substr($n, strripos($n, '_') + 1);
                $rate[] = $this->getRate('dice_halfsum', $n, $rule['rebate_rate']);
            }
            if (in_array('bigodd', $num) && count($num) == 1 && $this->sum['big'] && $this->sum['dan']) {//大单
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }

            if (in_array('bigeven', $num) && count($num) == 1 && $this->sum['big'] && $this->sum['shuang']) {//大双
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }

            if (in_array('smallodd', $num) && count($num) == 1 && $this->sum['small'] && $this->sum['dan']) {//小单
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }

            if (in_array('smalleven', $num) && count($num) == 1 && $this->sum['small'] && $this->sum['shuang']) {//小双
                $result = 2;
                $bonus += $rule['price'] * min($rate);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * 三连号通选
     */
    public function dice_serialall(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $value) {
            $D_value = max($this->dice_any) - min($this->dice_any);
            if ($D_value == 2 && count(array_unique($this->dice_any)) == 3) {
                $result = 2;
                $bonus += $rule['price'] * $this->getRate('dice_serialall', $value, $rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }

    /*
     * 三同号通选
     */
    public function dice_tripleall(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $value) {
            if (count(array_unique($this->dice_any)) == 1) {
                $result = 2;
                $bonus += $rule['price'] * $this->getRate('dice_tripleall', $value, $rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf('%.3f', $rule['bet_launch'] * $rule['rebate_rate'] / 100), 0, -1),
            'revert' => 0,
        ];
    }
}
