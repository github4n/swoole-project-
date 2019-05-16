<?php

namespace Site\Task\Lottery\Settle;
use Lib\Calender;
/*
 * six.php
 * @description   六合彩结算任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/Settle/six 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class six extends Base
{
    private $number,$all_number,$scode,$six_hit22,$six_scolor,$six_tail, $six_versus12,$six_versus13,$six_versus14,$six_versus15,
        $six_versus16,$six_versus23,$six_versus24,$six_versus25,$six_versus26,$six_versus34,$six_versus35,$six_versus36,$six_versus45,$six_versus46,$six_versus56,$zodiac,$six_pet,$six_wild,$normal_number,$six_sumhalf,$sx_list,$open_sx;

    /**
     * 准备结算数据
     * @param array $number
     */
    protected function loadNumber(array $number)
    {
        $this->number = $number;

        $this->normal_number = [
            $number["normal1"],
            $number["normal2"],
            $number["normal3"],
            $number["normal4"],
            $number["normal5"],
            $number["normal6"],
        ];

        $this->all_number = [
            $number["normal1"],
            $number["normal2"],
            $number["normal3"],
            $number["normal4"],
            $number["normal5"],
            $number["normal6"],
            $number["special1"]
        ];
        // 二全中
        for ($i = 1; $i < 7; $i++) {
            $this->six_hit22[] = $number['normal'.($i)];
        }
        //特码
        $this->scode = $number["special1"];
        //波色
        $this->six_scolor = [
            "blue"=>[3,4,9,10,14,15,20,25,26,31,36,37,41,42,47,48],
            "green"=>[5,6,11,16,17,21,22,27,28,32,33,38,39,43,44,49],
            "red"=>[1,2,7,8,12,13,18,19,23,24,29,30,34,35,40,45,46],
        ];
        //全尾
        $this->six_tail = [
            $number["normal1"] % 10,
            $number["normal2"] % 10,
            $number["normal3"] % 10,
            $number["normal4"] % 10,
            $number["normal5"] % 10,
            $number["normal6"] % 10,
            $number["special1"] % 10,
        ];
        //计算龙虎
        $this->six_versus12 = false;
        if($number["normal1"] > $number["normal2"]){
            $this->six_versus12 = true;
        }
        $this->six_versus13 = false;
        if($number["normal1"] > $number["normal3"]){
            $this->six_versus13 = true;
        }
        $this->six_versus14 = false;
        if($number["normal1"] > $number["normal4"]){
            $this->six_versus14 = true;
        }
        $this->six_versus15 = false;
        if($number["normal1"] > $number["normal5"]){
            $this->six_versus15 = true;
        }
        $this->six_versus16 = false;
        if($number["normal1"] > $number["normal6"]){
            $this->six_versus16 = true;
        }
        $this->six_versus23 = false;
        if($number["normal2"] > $number["normal3"]){
            $this->six_versus23 = true;
        }
        $this->six_versus24 = false;
        if($number["normal2"] > $number["normal4"]){
            $this->six_versus24 = true;
        }
        $this->six_versus25 = false;
        if($number["normal2"] > $number["normal5"]){
            $this->six_versus25 = true;
        }
        $this->six_versus26 = false;
        if($number["normal2"] > $number["normal6"]){
            $this->six_versus26 = true;
        }
        $this->six_versus34 = false;
        if($number["normal3"] > $number["normal4"]){
            $this->six_versus34 = true;
        }
        $this->six_versus35 = false;
        if($number["normal3"] > $number["normal5"]){
            $this->six_versus35 = true;
        }
        $this->six_versus36 = false;
        if($number["normal3"] > $number["normal6"]){
            $this->six_versus36 = true;
        }
        $this->six_versus45 = false;
        if($number["normal4"] > $number["normal5"]){
            $this->six_versus45 = true;
        }
        $this->six_versus46 = false;
        if($number["normal4"] > $number["normal6"]){
            $this->six_versus46 = true;
        }
        $this->six_versus56 = false;
        if($number["normal5"] > $number["normal6"]){
            $this->six_versus56 = true;
        }
        //总和两面
        $this->six_sumhalf = $number["normal1"]+$number["normal2"]+$number["normal3"]+$number["normal4"]+$number["normal5"]+$number["normal6"]+$number["special1"];
        //获取生肖
        $zodiacList = Calender::getZodiacList($number['open_time']);
        $this->zodiac = [
            "rat" => array_keys($zodiacList,"rat"),
            "ox" => array_keys($zodiacList,"ox"),
            "tiger" => array_keys($zodiacList,"tiger"),
            "rabbit" => array_keys($zodiacList,"rabbit"),
            "dragon" => array_keys($zodiacList,"dragon"),
            "snake" => array_keys($zodiacList,"snake"),
            "horse" => array_keys($zodiacList,"horse"),
            "sheep" => array_keys($zodiacList,"sheep"),
            "monkey" => array_keys($zodiacList,"monkey"),
            "chicken" => array_keys($zodiacList,"chicken"),
            "dog" => array_keys($zodiacList,"dog"),
            "pig" => array_keys($zodiacList,"pig"),
        ];
        //获取开奖的7生肖并去重
        $this->sx_list = $zodiacList;
        $this->open_sx = [
            $zodiacList[$number['normal1']],
            $zodiacList[$number['normal2']],
            $zodiacList[$number['normal3']],
            $zodiacList[$number['normal4']],
            $zodiacList[$number['normal5']],
            $zodiacList[$number['normal6']],
            $zodiacList[$number['special1']],
        ];
        //获取家禽
        $this->six_pet = array_merge($this->zodiac["ox"],$this->zodiac["horse"],$this->zodiac["sheep"],$this->zodiac["chicken"],$this->zodiac["dog"],$this->zodiac["pig"]);
        //野兽
        $this->six_wild = array_merge($this->zodiac["rat"],$this->zodiac["tiger"],$this->zodiac["rabbit"],$this->zodiac["dragon"],$this->zodiac["snake"],$this->zodiac["monkey"]);
    }

    //二全中(两个号码为一注)
    public function six_hit22(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],2);

        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                //取单注号码
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                //取赔率
                $rate[] = $this->getRate("six_hit22",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($this->normal_number , $notes))==2) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //二中特(两个号码为一注)
    public function six_hit2s(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],2);

        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $oneNumber = substr($n, strripos($n, "_") + 1);
                $aa = explode("#",$oneNumber);
                $notes[] = intval($aa[0]);
                $rate[] = $this->getRate("six_hit2s",$n,$rule['rebate_rate']);
            }

            if (count(array_intersect($this->normal_number,$notes)) == 2) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule['price'] * $rates;
            } elseif (in_array($this->scode,$notes) && count(array_intersect($this->normal_number,$notes)) == 1) {
                $result = 2;
                $scode = sprintf("%02d", $this->scode);
                $bonus += $rule['price'] * $this->getRate("six_hit2s","six_hit2s_" . $scode . "#s",$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //三中二（三个号码为一注）
    public function six_hit32(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],3);
        foreach ($numbers as $num) {
            $notes = [];
            foreach ($num as $n) {
                $oneNumber = intval(substr($n,strripos($n,"_") + 1));
                $aa = explode("#",$oneNumber);
                $notes[] = intval($aa[0]);
            }
            if(count(array_intersect($this->normal_number , $notes))==2){
                $rate = [];
                foreach (array_intersect($this->normal_number , $notes) as $item){
                    $var = sprintf("%02d", $item);
                    $rate[] = $this->getRate("six_hit32","six_hit32_".$var."#2",$rule['rebate_rate']);
                }
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }elseif(count(array_intersect($this->normal_number , $notes))==3){
                $rate = [];
                foreach (array_intersect($this->normal_number , $notes) as $item){
                    $var = sprintf("%02d", $item);
                    $rate[] = $this->getRate("six_hit32","six_hit32_".$var."#3",$rule['rebate_rate']);
                }
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }

        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //三全中(三个号码为一注)
    public function six_hit33(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],3);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_hit33",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($this->normal_number,$notes)) == 3) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule['price'] * $rates;
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //特串(两个号码为一注)
    public function six_hits2(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],2);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_hits2",$n,$rule['rebate_rate']);
            }

            if (count(array_intersect($this->all_number,$notes)) == 2 && in_array($this->scode,$notes)) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule['price'] * $rates;
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //十不中(十个号码为一注)
    public function six_miss10(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],10);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_miss10",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($this->all_number,$notes)) == 0) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //五不中(五个号码为一注)
    public function six_miss5(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],5);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_miss5",$n,$rule['rebate_rate']);
            }

            if (count(array_intersect($this->all_number , $notes))==0) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //六不中(六个号码为一注)
    public function six_miss6(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],6);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_miss6",$n,$rule['rebate_rate']);
            }

            if (count(array_intersect($this->all_number , $notes))==0) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //七不中(七个号码为一注)
    public function six_miss7(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],7);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_miss7",$n,$rule['rebate_rate']);
            }

            if (count(array_intersect($this->all_number , $notes))==0) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //八不中(八个号码为一注)
    public function six_miss8(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],8);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_miss8",$n,$rule['rebate_rate']);
            }

            if (count(array_intersect($this->all_number , $notes))==0) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //九不中(九个号码为一注)
    public function six_miss9(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],9);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n,strripos($n,"_")+1));
                $rate[] = $this->getRate("six_miss9",$n,$rule['rebate_rate']);
            }

            if (count(array_intersect($this->all_number , $notes))==0) {
                $result = 2;
                $rates = min($rate);
                $bonus += $rule["price"]*$rates;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正1特(1个)
    public function six_n1code(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->number["normal1"]){
                $result = 2;
                $bonus += $rule["price"]*$this->getRate("six_n1code",$n,$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正1波色
    public function six_n1color(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "blue"){
                if(in_array($this->number["normal1"],$this->six_scolor["blue"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n1color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "green"){
                if(in_array($this->number["normal1"],$this->six_scolor["green"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n1color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "red"){
                if(in_array($this->number["normal1"],$this->six_scolor["red"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n1color",$n,$rule['rebate_rate']);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正1两面
    public function six_n1half(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($this->number["normal1"] == 49){
                $result = 3;
                $bonus = 0;
                $revert += $rule["price"];
                $rebate = 0;
            }else{
                if($num == "big"){ //大
                    if($this->number["normal1"] >= 25){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "small"){ //小
                    if($this->number["normal1"] <= 24){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "odd"){ //单
                    if($this->number["normal1"] % 2 ==1){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "even"){ //双
                    if($this->number["normal1"] % 2 ==0){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixodd'){  //合单
                    if(($this->number["normal1"] % 10 + $this->number["normal1"] /10 % 10) % 2 == 1){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixeven'){ //合双
                    if(($this->number["normal1"] % 10 + $this->number["normal1"]  /10 % 10) % 2 == 0){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailbig'){ //尾大
                    if($this->number["normal1"] % 10 >= 5){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailsmall'){  //尾小
                    if($this->number["normal1"] % 10 <= 4){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n1half",$n,$rule['rebate_rate']);
                    }
                }
            }

        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //正2特
    public function six_n2code(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->number["normal2"]){
                $result = 2;
                $bonus += $rule["price"]*$this->getRate("six_n2code",$n,$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正2波色
    public function six_n2color(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "blue"){
                if(in_array($this->number["normal2"],$this->six_scolor["blue"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n2color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "green"){
                if(in_array($this->number["normal2"],$this->six_scolor["green"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n2color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "red"){
                if(in_array($this->number["normal2"],$this->six_scolor["red"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n2color",$n,$rule['rebate_rate']);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正2两面
    public function six_n2half(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($this->number["normal2"] == 49){
                $result = 3;
                $bonus = 0;
                $revert += $rule["price"];
                $rebate = 0;
            }else{
                if($num == "big"){ //大
                    if($this->number["normal2"] >= 25){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "small"){ //小
                    if($this->number["normal2"] <= 24){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "odd"){ //单
                    if($this->number["normal2"] % 2 ==1){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "even"){ //双
                    if($this->number["normal2"] % 2 ==0){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixodd'){  //合单
                    if(($this->number["normal2"] % 10 + $this->number["normal2"] / 10 % 10) % 2 == 1){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixeven'){ //合双
                    if(($this->number["normal2"] % 10 + $this->number["normal2"] / 10 % 10) % 2 == 0){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailbig'){ //尾大
                    if($this->number["normal2"] % 10 >= 5){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailsmall'){  //尾小
                    if($this->number["normal2"] % 10 <= 4){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n2half",$n,$rule['rebate_rate']);
                    }
                }
            }

        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //正3特
    public function six_n3code(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->number["normal3"]){
                $result = 2;
                $bonus += $rule["price"]*$this->getRate("six_n3code",$n,$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正3波色
    public function six_n3color(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "blue"){
                if(in_array($this->number["normal3"],$this->six_scolor["blue"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n3color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "green"){
                if(in_array($this->number["normal3"],$this->six_scolor["green"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n3color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "red"){
                if(in_array($this->number["normal3"],$this->six_scolor["red"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n3color",$n,$rule['rebate_rate']);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正3两面
    public function six_n3half(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($this->number["normal3"] == 49){
                $result = 3;
                $bonus = 0;
                $revert += $rule["price"];
                $rebate = 0;
            }else{
                if($num == "big"){ //大
                    if($this->number["normal3"] >= 25){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "small"){ //小
                    if($this->number["normal3"] <= 24){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "odd"){ //单
                    if($this->number["normal3"] % 2 ==1){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "even"){ //双
                    if($this->number["normal3"] % 2 ==0){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixodd'){  //合单
                    if(($this->number["normal3"] % 10 + $this->number["normal3"] / 10 % 10) % 2 == 1){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixeven'){ //合双
                    if(($this->number["normal3"] % 10 + $this->number["normal3"] / 10 % 10) % 2 == 0){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailbig'){ //尾大
                    if($this->number["normal3"] % 10 >= 5){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailsmall'){  //尾小
                    if($this->number["normal3"] % 10 <= 4){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n3half",$n,$rule['rebate_rate']);
                    }
                }
            }

        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //正4特
    public function six_n4code(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->number["normal4"]){
                $result = 2;
                $bonus += $rule["price"]*$this->getRate("six_n4code",$n,$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正4波色
    public function six_n4color(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "blue"){
                if(in_array($this->number["normal4"],$this->six_scolor["blue"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n4color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "green"){
                if(in_array($this->number["normal4"],$this->six_scolor["green"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n4color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "red"){
                if(in_array($this->number["normal4"],$this->six_scolor["red"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n4color",$n,$rule['rebate_rate']);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正4两面
    public function six_n4half(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($this->number["normal4"] == 49){
                $result = 3;
                $bonus = 0;
                $revert += $rule["price"];
                $rebate = 0;
            }else{
                if($num == "big"){ //大
                    if($this->number["normal4"] >= 25){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "small"){ //小
                    if($this->number["normal4"] <= 24){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "odd"){ //单
                    if($this->number["normal4"] % 2 ==1){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "even"){ //双
                    if($this->number["normal4"] % 2 ==0){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixodd'){  //合单
                    if(($this->number["normal4"] % 10 + $this->number["normal4"] / 10 % 10) % 2 == 1){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixeven'){ //合双
                    if(($this->number["normal4"] % 10 + $this->number["normal4"] / 10 % 10) % 2 == 0){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailbig'){ //尾大
                    if($this->number["normal4"] % 10 >= 5){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailsmall'){  //尾小
                    if($this->number["normal4"] % 10 <= 4){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n4half",$n,$rule['rebate_rate']);
                    }
                }
            }

        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 :$rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //正5特
    public function six_n5code(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->number["normal5"]){
                $result = 2;
                $bonus += $rule["price"]*$this->getRate("six_n5code",$n,$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正5波色
    public function six_n5color(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "blue"){
                if(in_array($this->number["normal5"],$this->six_scolor["blue"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n5color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "green"){
                if(in_array($this->number["normal5"],$this->six_scolor["green"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n5color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "red"){
                if(in_array($this->number["normal5"],$this->six_scolor["red"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n5color",$n,$rule['rebate_rate']);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正5两面
    public function six_n5half(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($this->number["normal5"] == 49){
                $result = 3;
                $bonus = 0;
                $revert += $rule["price"];
                $rebate = 0;
            }else{
                if($num == "big"){ //大
                    if($this->number["normal5"] >= 25){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "small"){ //小
                    if($this->number["normal5"] <= 24){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "odd"){ //单
                    if($this->number["normal5"] % 2 ==1){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "even"){ //双
                    if($this->number["normal5"] % 2 ==0){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixodd'){  //合单
                    if(($this->number["normal5"] % 10 + $this->number["normal5"] / 10 % 10) % 2 == 1){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixeven'){ //合双
                    if(($this->number["normal5"] % 10 + $this->number["normal5"] / 10 % 10) % 2 == 0){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailbig'){ //尾大
                    if($this->number["normal5"] % 10 >= 5){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailsmall'){  //尾小
                    if($this->number["normal5"] % 10 <= 4){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n5half",$n,$rule['rebate_rate']);
                    }
                }
            }

        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0  :$rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //正6特
    public function six_n6code(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->number["normal6"]){
                $result = 2;
                $bonus += $rule["price"]*$this->getRate("six_n6code",$n,$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //正6波色
    public function six_n6color(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "blue"){
                if(in_array($this->number["normal6"],$this->six_scolor["blue"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n6color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "green"){
                if(in_array($this->number["normal6"],$this->six_scolor["green"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n6color",$n,$rule['rebate_rate']);
                }
            }
            if($num == "red"){
                if(in_array($this->number["normal6"],$this->six_scolor["red"])){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_n6color",$n,$rule['rebate_rate']);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }
                     
    //正6两面
    public function six_n6half(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($this->number["normal6"] == 49){
                $result = 3;
                $bonus = 0;
                $revert += $rule["price"];
                $rebate = 0;
            }else{
                if($num == "big"){ //大
                    if($this->number["normal6"] >= 25){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "small"){ //小
                    if($this->number["normal6"] <= 24){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "odd"){ //单
                    if($this->number["normal6"] % 2 ==1){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == "even"){ //双
                    if($this->number["normal6"] % 2 ==0){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixodd'){  //合单
                    if(($this->number["normal6"] % 10 + $this->number["normal6"] / 10 % 10) % 2 == 1){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'mixeven'){ //合双
                    if(($this->number["normal6"] % 10 + $this->number["normal6"] / 10 % 10) % 2 == 0){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailbig'){ //尾大
                    if($this->number["normal6"] % 10 >= 5){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
                if($num == 'tailsmall'){  //尾小
                    if($this->number["normal6"] % 10 <= 4){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("six_n6half",$n,$rule['rebate_rate']);
                    }
                }
            }

        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //正码
    public function six_normal(array $rule):array
    {

        $result=1;
        $bonus=0;
        foreach ($rule["number"] as $n){
            $num = intval(substr($n, strripos($n, "_") + 1));
            if(in_array($num,$this->normal_number)){
                $result = 2;
                $bonus += $rule["price"] * $this->getRate("six_normal",$n,$rule['rebate_rate']);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];

    }

    //特码
    public function six_scode(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->scode){
                $result = 2;
                $bonus += $rule["price"] * $this->getRate("six_scode",$n,$rule['rebate_rate']);
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }
    
    //波色
    public function six_scolor(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "blue"){
               if(in_array($this->scode,$this->six_scolor["blue"])){
                   $result = 2;
                   $bonus += $rule["price"] * $this->getRate("six_scolor",$n,$rule['rebate_rate']);
               }
            }
            if($num == "green"){
                if(in_array($this->scode,$this->six_scolor["green"])){
                    $result = 2;
                    $bonus += $rule["price"] * $this->getRate("six_scolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "red"){
                if(in_array($this->scode,$this->six_scolor["red"])){
                    $result = 2;
                    $bonus += $rule["price"] * $this->getRate("six_scolor",$n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }
    
    //特码两面
    public function six_shalf(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate =  substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        foreach ($rule["number"] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if($this->scode == 49){
                $result = 3;
                $bonus = 0;
                $revert += $rule["price"];
                $rebate = 0;
            }else{
                if($num =="big"){
                    if($this->scode >= 25){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_shalf",$n,$rule['rebate_rate']);
                    }
                }
                if($num =="small"){
                    if($this->scode <= 24){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_shalf",$n,$rule['rebate_rate']);
                    }
                }
                if($num =="odd"){
                    if($this->scode % 2 ==1){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_shalf",$n,$rule['rebate_rate']);
                        $revert = 0;
                    }
                }
                if($num =="even"){
                    if($this->scode % 2 == 0){
                        $result = 2;
                        $bonus += $rule["price"] * $this->getRate("six_shalf",$n,$rule['rebate_rate']);
                    }
                }
                if($num =="mixeven"){   //合双
                    if(($this->scode%10 + $this->scode/10%10) % 2 == 0){
                        $result = 2;
                        $bonus += $rule["price"]*$this->getRate("six_shalf",$n,$rule['rebate_rate']);
                    }
                }
                if($num =="mixodd"){    //合单
                    if(($this->scode%10 + $this->scode/10%10) % 2 == 1){
                        $result = 2;
                        $bonus += $rule["price"]*$this->getRate("six_shalf",$n,$rule['rebate_rate']);
                    }
                }
                if($num =="pet"){     //家禽
                   if(in_array($this->scode,$this->six_pet)){
                       $result = 2;
                       $bonus += $rule["price"]*$this->getRate("six_shalf",$n,$rule['rebate_rate']) ;
                   }
                }
                if($num =="wild"){    //野兽
                    if(in_array($this->scode,$this->six_wild)){
                        $result = 2;
                        $bonus += $rule["price"]*$this->getRate("six_shalf",$n,$rule['rebate_rate']) ;
                    }
                }
                if($num =="tailbig"){
                    if($this->scode%10 >= 5){
                        $result = 2;
                        $bonus += $rule["price"]*$this->getRate("six_shalf",$n,$rule['rebate_rate']);
                    }
                }
                if($num =="tailsmall"){
                    if($this->scode%10 <= 4){
                        $result = 2;
                        $bonus += $rule["price"]*$this->getRate("six_shalf",$n,$rule['rebate_rate']);
                    }
                }
            }


        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }
    
    //半波
    public function six_shalfolor(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if($num == "bluebig"){
                if(in_array($this->scode,$this->six_scolor["blue"]) && $this->scode >= 25){
                     $result = 2;
                     $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "bluesmall"){
                if(in_array($this->scode,$this->six_scolor["blue"]) && $this->scode <= 24){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "bluemixeven"){  //蓝和双
                if(in_array($this->scode,$this->six_scolor["blue"]) && ($this->scode%10 + $this->scode/10%10) % 2 == 0){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "bluemixodd"){
                if(in_array($this->scode,$this->six_scolor["blue"]) && ($this->scode%10 + $this->scode/10%10) % 2 == 1){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "blueodd"){
                if(in_array($this->scode,$this->six_scolor["blue"]) && $this->scode % 2 == 1){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "blueeven"){
                if(in_array($this->scode,$this->six_scolor["blue"]) && $this->scode % 2 == 0){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "greenbig"){
                if(in_array($this->scode,$this->six_scolor["green"]) && $this->scode >= 25){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "greensmall"){
                if(in_array($this->scode,$this->six_scolor["green"]) && $this->scode <= 24){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }else{
                    $result = 1;
                    $bonus = 0;
                }
            }
            if($num == "greenmixeven"){
                if(in_array($this->scode,$this->six_scolor["green"]) && ($this->scode%10 + $this->scode/10%10) % 2 == 0){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "greenmixodd"){
                if(in_array($this->scode,$this->six_scolor["green"]) && ($this->scode%10 + $this->scode/10%10) % 2 == 1){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "greeneven"){
                if(in_array($this->scode,$this->six_scolor["green"]) && $this->scode % 2 == 0){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "greenodd"){
                if(in_array($this->scode,$this->six_scolor["green"]) && $this->scode % 2 == 1){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "redbig"){
                if(in_array($this->scode,$this->six_scolor["red"]) && $this->scode >= 25){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "redsmall"){
                if(in_array($this->scode,$this->six_scolor["red"]) && $this->scode <= 24){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "redodd"){
                if(in_array($this->scode,$this->six_scolor["red"]) && $this->scode % 2 == 1){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "redeven"){
                if(in_array($this->scode,$this->six_scolor["red"]) && $this->scode % 2 == 0){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "redmixeven"){
                if(in_array($this->scode,$this->six_scolor["red"]) && ($this->scode%10 + $this->scode/10%10) % 2 == 0){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
            if($num == "redmixodd"){
                if(in_array($this->scode,$this->six_scolor["red"]) && ($this->scode%10 + $this->scode/10%10) % 2 == 1){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_shalfolor",$n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //特码区号
    public function six_sregion(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == 10){
                if($this->scode <= 10 && $this->scode >=1){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_sregion",$n,$rule['rebate_rate']);
                }
            }
            if($num == 20){
                if($this->scode <= 20 && $this->scode >= 11){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_sregion",$n,$rule['rebate_rate']);
                }
            }
            if($num == 30){
                if($this->scode <= 30 && $this->scode >= 21){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_sregion",$n,$rule['rebate_rate']);
                }
            }
            if($num == 40){
                if($this->scode <= 40 && $this->scode >= 31){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_sregion",$n,$rule['rebate_rate']);
                }
            }
            if($num == 49){
                if($this->scode <= 49 && $this->scode >= 41){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("six_sregion",$n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //特尾
    public function six_stail(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == $this->scode%10){
                $result = 2;
                $bonus += $rule["price"]*$this->getRate("six_stail",$n,$rule['rebate_rate']);
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //特肖
    public function six_szodiac(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = intval(substr($n, strripos($n, "_") + 1));
            if($num == 1){
                $nums = [$num, $num+12, $num+24, $num+36, $num+48];
            }else{
                $nums = [$num, $num+12, $num+24, $num+36];
            }
            if(in_array($this->scode,$nums)){
                $result = 2;
                $bonus += $rule["price"] * $this->getRate("six_szodiac",$n,$rule['rebate_rate']);
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //特码六肖中一
    public function six_szodiac6hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        if ($this->scode == 49) {
            $result = 3;
            $bonus = 0;
            $revert += $rule["price"];
            $rebate = 0;
        } else {
            $numbers = $this->combination($rule['number'],6);
            foreach ($numbers as $num) {
                $notes = [];
                $reta = [];
                foreach ($num as $n) {
                    $note = intval(substr($n,strripos($n,"_") + 1));
                    $notes = array_merge($notes,[$note,$note+12,$note+24,$note+36]);
                    $reta[] = $this->getRate("six_szodiac6hit",$n,$rule['rebate_rate']);
                }

                if (in_array($this->scode,$notes)) {
                    $result = 2;
                    $bonus += $rule['price'] * min($reta);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //特码六肖不中
    public function six_szodiac6miss(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate =  substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        if ($this->scode == 49) {
            $result = 3;
            $bonus = 0;
            $revert += $rule['price'];
            $rebate = 0;
        } else {
            $numbers = $this->combination($rule['number'],6);
            foreach ($numbers as $num) {
                $notes = [];
                $reta = [];
                foreach ($num as $n) {
                    $note = intval(substr($n,strripos($n,"_") + 1));
                    $notes = array_merge($notes,[$note,$note+12,$note+24,$note+36]);
                    $reta[] = $this->getRate("six_szodiac6miss",$n,$rule['rebate_rate']);
                }

                if (!in_array($this->scode,$notes)) {
                    $result = 2;
                    $bonus += $rule['price'] * min($reta);
                }
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $result == 3 ? 0 : $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => $rebate,
            'revert' => $revert,
        ];
    }

    //全尾
    public function six_tail(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            $bonu =  $this->getRate("six_tail",$n,$rule['rebate_rate']);
            if(in_array($num,$this->six_tail)){
                $result = 2;
                $bonus += $rule["price"] * $bonu;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //二尾连中(两个号码为一注)
    public function six_tail2hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],2);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("six_tail2hit",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($notes,$this->six_tail)) == 2) {
                $result = 2;
                $rebate = min($rate);
                $bonus += $rule["price"]*$rebate;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //二尾连不中(两个号码为一注)
    public function six_tail2miss(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],2);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("six_tail2miss",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($notes,$this->six_tail)) == 0) {
                $result = 2;
                $rebate = min($rate);
                $bonus += $rule["price"]*$rebate;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //三尾连中(三个号码为一注)
    public function six_tail3hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],3);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("six_tail3hit",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($notes,$this->six_tail)) == 3) {
                $result = 2;
                $rebate = min($rate);
                $bonus += $rule["price"]*$rebate;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //三尾连不中(三个号码为一注)
    public function six_tail3miss(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],3);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("six_tail3miss",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($notes,$this->six_tail)) == 0) {
                $result = 2;
                $rebate = min($rate);
                $bonus += $rule["price"]*$rebate;
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //四尾连中(四个号码为一注)
    public function six_tail4hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],4);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("six_tail4hit",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($notes,$this->six_tail)) == 4) {
                $result = 2;
                $rebate = min($rate);
                $bonus += $rule["price"]*$rebate;
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //四尾连不中(四个号码为一注)
    public function six_tail4miss(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],4);
        foreach ($numbers as $num) {
            $notes = [];
            $rate = [];
            foreach ($num as $n) {
                $notes[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("six_tail4miss",$n,$rule['rebate_rate']);
            }
            if (count(array_intersect($notes,$this->six_tail)) == 0) {
                $result = 2;
                $rebate = min($rate);
                $bonus += $rule["price"]*$rebate;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //1v2龙虎
    public function six_versus12(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus12){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus12", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus12){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus12", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //1v3龙虎
    public function six_versus13(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus13){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus13", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus13){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus13", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //1v4龙虎
    public function six_versus14(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus14){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus14", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus14){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus14", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //1v5龙虎
    public function six_versus15(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus15){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus15", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus15){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus15", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //1v6龙虎
    public function six_versus16(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus16){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus16", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus16){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus16", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //2v3龙虎
    public function six_versus23(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus23){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus23", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus23){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus23", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //2v4龙虎
    public function six_versus24(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus24){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus24", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus24){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus24", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //2v5龙虎
    public function six_versus25(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus25){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus25", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus25){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus25", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //2v6龙虎
    public function six_versus26(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus26){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus26", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus26){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus26", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //3v4龙虎
    public function six_versus34(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus34){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus34", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus34){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus34", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //3v5龙虎
    public function six_versus35(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus35){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus35", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus35){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus35", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //3v6龙虎
    public function six_versus36(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus36){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus36", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus36){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus36", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //4v5龙虎
    public function six_versus45(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus45){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus45", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus45){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus45", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //4v6虎
    public function six_versus46(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus46){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus46", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus46){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus46", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //5v6龙虎
    public function six_versus56(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->six_versus56){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus56", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->six_versus56){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_versus56", $n,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //平特一肖(一个生肖一注)
    public function six_zodiac1hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n) {
            $num = intval(substr($n, strripos($n, "_") + 1));
            $bonusTranslate[] = $this->getRate("six_zodiac1hit",$n,$rule['rebate_rate']);
            $sx = $this->sx_list[$num];
            if (in_array($sx, $this->open_sx)) {
                $result = 2;
                $rebate = min($bonusTranslate);
                $bonus += $rule["price"]*$rebate;
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //二肖连中(两个号码一注)
    public function six_zodiac2hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],2);
        foreach ($numbers as $item) {
            $results = [];
            $bonu = [];
            foreach ($item as $n){
                $num = intval(substr($n, strripos($n, "_") + 1));
                if($num == 1){
                    $nums = [$num, $num+12, $num+24, $num+36, $num+48];
                }else{
                    $nums = [$num, $num+12, $num+24, $num+36];
                }
                $bonu[] = $this->getRate("six_zodiac2hit",$n,$rule['rebate_rate']);
                if(count(array_intersect($nums,$this->all_number)) >= 1){
                    $results[] = "true";
                }else{
                    $results[] = "false";
                }
            }
            if(!in_array("false",$results)){
                $result = 2;
                $bonus += $rule["price"]*min($bonu);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //二肖连不中(两个生肖一注)
    public function six_zodiac2miss(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],2);
        foreach ($numbers as $num) {
            $results = [];
            $bonu = [];
            foreach ($num as $n) {
                $ns = intval(substr($n, strripos($n, "_") + 1));
                if($ns == 1){
                    $nums = [$ns, $ns+12, $ns+24, $ns+36, $ns+48];
                }else{
                    $nums = [$ns, $ns+12, $ns+24, $ns+36];
                }
                $bonu[] = $this->getRate("six_zodiac2miss",$n,$rule['rebate_rate']);
                if(count(array_intersect($nums,$this->all_number)) == 0){
                    $results[] = "true";
                }else{
                    $results[] = "false";
                }
            }
            if(!in_array("false",$results)){
                $result = 2;
                $bonus += $rule["price"]*min($bonu);
            }

        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //三肖连中(三个生肖为一注)
    public function six_zodiac3hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],3);
        foreach ($numbers as $item) {
            $results = [];
            $bonu = [];
            foreach ($item as $n){
                $num = intval(substr($n, strripos($n, "_") + 1));
                if($num == 1){
                    $nums = [$num, $num+12, $num+24, $num+36, $num+48];
                }else{
                    $nums = [$num, $num+12, $num+24, $num+36];
                }
                $bonu[] = $this->getRate("six_zodiac3hit",$n,$rule['rebate_rate']);
                if(count(array_intersect($nums,$this->all_number)) >= 1){
                    $results[] = "true";
                }else{
                    $results[] = "false";
                }
            }
            if(!in_array("false",$results)){
                $result = 2;
                $bonus += $rule["price"]*min($bonu);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //三肖连不中(三个生肖为一注)
    public function six_zodiac3miss(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],3);
        foreach ($numbers as $item) {
            $results = [];
            $bonu = [];
            foreach ($item as $n){
                $num = intval(substr($n, strripos($n, "_") + 1));
                if($num == 1){
                    $nums = [$num, $num+12, $num+24, $num+36, $num+48];
                }else{
                    $nums = [$num, $num+12, $num+24, $num+36];
                }
                $bonu[] = $this->getRate("six_zodiac3miss",$n,$rule['rebate_rate']);
                if(count(array_intersect($nums,$this->all_number)) == 0){
                    $results[] = "true";
                }else{
                    $results[] = "false";
                }
            }
            if(!in_array("false",$results)){
                $result = 2;
                $bonus += $rule["price"]*min($bonu);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //四肖连中(四个生肖为一注)
    public function six_zodiac4hit(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],4);
        foreach ($numbers as $item) {
            $results = [];
            $bonu = [];
            foreach ($item as $n){
                $num = intval(substr($n, strripos($n, "_") + 1));
                if($num == 1){
                    $nums = [$num, $num+12, $num+24, $num+36, $num+48];
                }else{
                    $nums = [$num, $num+12, $num+24, $num+36];
                }
                $bonu[] = $this->getRate("six_zodiac4hit",$n,$rule['rebate_rate']);
                if(count(array_intersect($nums,$this->all_number)) >= 1){
                    $results[] = "true";
                }else{
                    $results[] = "false";
                }
            }
            if(!in_array("false",$results)){
                $result = 2;
                $bonus += $rule["price"]*min($bonu);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }

    //四肖连不中(四个生肖为一注)
    public function six_zodiac4miss(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        $numbers = $this->combination($rule['number'],4);
        foreach ($numbers as $item) {
            $results = [];
            $bonu = [];
            foreach ($item as $n){
                $num = intval(substr($n, strripos($n, "_") + 1));
                if($num == 1){
                    $nums = [$num, $num+12, $num+24, $num+36, $num+48];
                }else{
                    $nums = [$num, $num+12, $num+24, $num+36];
                }
                $bonu[] = $this->getRate("six_zodiac4miss",$n,$rule['rebate_rate']);
                if(count(array_intersect($nums,$this->all_number)) == 0){
                    $results[] = "true";
                }else{
                    $results[] = "false";
                }
            }
            if(!in_array("false",$results)){
                $result = 2;
                $bonus += $rule["price"]*min($bonu);
            }
        }

        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }
    //总和两面
    public function six_sumhalf(array $rule):array
    {
         $result = 1;
         $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if ($this->six_sumhalf >= 175) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_sumhalf", $n,$rule['rebate_rate']);
                }
            }
            if ($num == "small") {
                if ($this->six_sumhalf <= 174) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_sumhalf", $n,$rule['rebate_rate']);
                }
            }
            if ($num == "odd") {
                if ($this->six_sumhalf % 2 == 1) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_sumhalf", $n,$rule['rebate_rate']);
                }
            }
            if ($num == "even") {
                if ($this->six_sumhalf % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_sumhalf", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tailbig"){
                if($this->six_sumhalf%10 >= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_sumhalf", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tailsmall"){
                if($this->six_sumhalf%10 <= 4){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("six_sumhalf", $n,$rule['rebate_rate']);
                }
            }

        }



        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }
}