<?php

namespace Site\Task\Lottery\Settle;

/*
 * lucky.php
 * @description   快乐十分结算任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/Settle/lucky 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class lucky extends Base
{
    //准备结算数据
    private $number, $lucky_ball1, $lucky_ball2, $lucky_ball3, $lucky_ball4, $lucky_ball5,$lucky_ball6,$lucky_ball7,$lucky_ball8,$lucky_halfsum,$lucky_link2,$lucky_versus1,$lucky_versus2,$lucky_versus3,$lucky_versus4,$allNumber;
    protected function loadNumber(array $number)
    {
        $this->number = $number;

        //所有号码
        $this->allNumber = [
            $number['normal1'],
            $number['normal2'],
            $number['normal3'],
            $number['normal4'],
            $number['normal5'],
            $number['normal6'],
            $number['normal7'],
            $number['normal8']
        ];
        //第一球
        $this->lucky_ball1 = $number['normal1'];
        //第二球
        $this->lucky_ball2 = $number['normal2'];
        //第三球
        $this->lucky_ball3 = $number['normal3'];
        //第四球
        $this->lucky_ball4 = $number['normal4'];
        //第五球
        $this->lucky_ball5 = $number['normal5'];
        //第六球
        $this->lucky_ball6 = $number['normal6'];
        //第七求
        $this->lucky_ball7 = $number['normal7'];
        //第八求
        $this->lucky_ball8 = $number['normal8'];
        //和值两面
        $this->lucky_halfsum = array_sum($this->allNumber);
        //任选两组
        $this->lucky_link2 = [
            [
                $number['normal1'],
                $number['normal2'],
            ],
            [
                $number['normal2'],
                $number['normal3'],
            ],
            [
                $number['normal3'],
                $number['normal4'],
            ],
            [
                $number['normal4'],
                $number['normal5'],
            ] ,
            [
                $number['normal5'],
                $number['normal6'],
            ] ,
            [
                $number['normal6'],
                $number['normal7']
            ],
            [
                $number['normal7'],
                $number['normal8']
            ]
        ];
        //1v8龙虎
        $this->lucky_versus1 = false;
        if($number['normal1'] > $number['normal8']){
            $this->lucky_versus1 = true;
        }
        //2v7龙虎
        $this->lucky_versus2 = false;
        if($number['normal2'] > $number['normal7']){
            $this->lucky_versus2 = true;
        }
        //3v6龙虎
        $this->lucky_versus3 = false;
        if($number['normal3'] > $number['normal6']){
            $this->lucky_versus3 = true;
        }
        //4v5龙虎
        $this->lucky_versus4 = false;
        if($number['normal4'] > $number['normal5']){
            $this->lucky_versus4 = true;
        }
    }
    //任选二(两个号码为一注)
    public function lucky_any2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $numAll = $this->combination($rule["number"],2);
        foreach ($numAll as $item){
            $num = [];
            $rate = [];
            foreach ($item as $n){
                $num[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("lucky_any2", $n,$rule['rebate_rate']);
            }
            if(count(array_intersect($this->allNumber,$num))==2){
                $result = 2;
                $bonus += $rule['price'] * min($rate);

            }
        }
       
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' =>  substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }
    //任选三
    public function lucky_any3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $numAll = $this->combination($rule["number"],3);
        foreach ($numAll as $item){
            $num = [];
            $rate = [];
            foreach ($item as $n){
                $num[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("lucky_any3", $n,$rule['rebate_rate']);
            }
            if(count(array_intersect($this->allNumber,$num))==3){
                $result = 2;
                $bonus += $rule['price'] * min($rate);

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
    //任选四
    public function lucky_any4(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $numAll = $this->combination($rule["number"],4);
        foreach ($numAll as $item){
            $num = [];
            $rate = [];
            foreach ($item as $n){
                $num[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("lucky_any4", $n,$rule['rebate_rate']);
            }
            if(count(array_intersect($this->allNumber,$num))==4){
                $result = 2;
                $bonus += $rule['price'] * min($rate);

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
    //任选五
    public function lucky_any5(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $numAll = $this->combination($rule["number"],5);
        foreach ($numAll as $item){
            $num = [];
            $rate = [];
            foreach ($item as $n){
                $num[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("lucky_any5", $n,$rule['rebate_rate']);
            }
            if(count(array_intersect($this->allNumber,$num))==5){
                $result = 2;
                $bonus += $rule['price'] * min($rate);

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
    //第一球
    public function lucky_ball1(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = intval(substr($item,strripos($item,"_")+1));
            if($this->lucky_ball1 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball1", $item,$rule['rebate_rate']);
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
    //第二球
    public function lucky_ball2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($this->lucky_ball2 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball2", $item,$rule['rebate_rate']);
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
    //第三球
    public function lucky_ball3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($this->lucky_ball3 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball3", $item,$rule['rebate_rate']);
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
    //第四球
    public function lucky_ball4(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($this->lucky_ball4 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball4", $item,$rule['rebate_rate']);
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
    //第五球
    public function lucky_ball5(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($this->lucky_ball5 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball5", $item,$rule['rebate_rate']);
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
    //第六球
    public function lucky_ball6(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($this->lucky_ball6 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball6", $item,$rule['rebate_rate']);
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
    //第七求
    public function lucky_ball7(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($this->lucky_ball7 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball7", $item,$rule['rebate_rate']);
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
    //第八求
    public function lucky_ball8(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($this->lucky_ball8 == $num){
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("lucky_ball8", $item,$rule['rebate_rate']);
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
    //第一球中发白
    public function lucky_color1(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball1 <=14 && $this->lucky_ball1>=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color1", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball1 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color1", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball1 >=15 && $this->lucky_ball1 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color1", $item,$rule['rebate_rate']);
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
    //第二球中发白
    public function lucky_color2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball2 <=14 && $this->lucky_ball2>=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color2", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball2 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color2", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball2 >=15 && $this->lucky_ball2 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color2", $item,$rule['rebate_rate']);
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
    //第三球发白
    public function lucky_color3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball3 <=14 && $this->lucky_ball3 >=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color3", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball3 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color3", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball3 >=15 && $this->lucky_ball3 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color3", $item,$rule['rebate_rate']);
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
    //第四球发白
    public function lucky_color4(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball4 <=14 && $this->lucky_ball4>=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color4", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball4 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color4", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball4 >=15 && $this->lucky_ball4 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color4", $item,$rule['rebate_rate']);
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
    //第五球发白
    public function lucky_color5(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball5 <=14 && $this->lucky_ball5>=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color5", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball5 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color5", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball5 >=15 && $this->lucky_ball5 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color5", $item,$rule['rebate_rate']);
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
    //第六球发白
    public function lucky_color6(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball6 <=14 && $this->lucky_ball6>=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color6", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball6 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color6", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball6 >=15 && $this->lucky_ball6 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color6", $item,$rule['rebate_rate']);
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
    //第七球发白
    public function lucky_color7(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball7 <=14 && $this->lucky_ball7>=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color7", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball7 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color7", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball7 >=15 && $this->lucky_ball7 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color7", $item,$rule['rebate_rate']);
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
    //第八球中发白
    public function lucky_color8(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $item){
            $num = substr($item,strripos($item,"_")+1);
            if($num == "green"){//发
                if($this->lucky_ball8 <=14 && $this->lucky_ball8 >=8){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color8", $item,$rule['rebate_rate']);
                }
            }
            if($num == "red"){  //中
                if($this->lucky_ball8 <=7){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color8", $item,$rule['rebate_rate']);
                }
            }
            if($num == "white"){   //白
                if($this->lucky_ball8 >=15 && $this->lucky_ball8 <= 20){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_color8", $item,$rule['rebate_rate']);
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
    //第一球两面
    public function lucky_half1(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball1 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half1", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball1 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half1", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball1 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half1", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball1 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half1", $n,$rule['rebate_rate']);
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
    //第二球两面
    public function lucky_half2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball2 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half2", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball2 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half2", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball2 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half2", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball2 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half2", $n,$rule['rebate_rate']);
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
    //第三球两面
    public function lucky_half3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball3 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball3 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball3 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball3 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half3", $n,$rule['rebate_rate']);
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
    //第四球两面
    public function lucky_half4(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball4 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball4 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball4 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball4 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half4", $n,$rule['rebate_rate']);
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
    //第五球两面
    public function lucky_half5(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball5 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half5", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball5 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half5", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball5 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half5", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball5 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half5", $n,$rule['rebate_rate']);
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
    //第六球两面
    public function lucky_half6(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball6 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half6", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball6 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half6", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball6 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half6", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball6 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half6", $n,$rule['rebate_rate']);
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
    //第七球两面
    public function lucky_half7(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball7 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half7", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball7 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half7", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball7 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half7", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball7 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half7", $n,$rule['rebate_rate']);
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
    //第八球两面
    public function lucky_half8(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule["number"] as $n){
            $num = substr($n,strripos($n,"_")+1);
            if ($num == "big") {
                if($this->lucky_ball8 >= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half8", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->lucky_ball8 <= 10){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half8", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->lucky_ball8 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half8", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->lucky_ball8 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_half8", $n,$rule['rebate_rate']);
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
    //和值两面
    public function lucky_halfsum(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $revert = 0;
        $rebate = substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1);
        if ($this->lucky_halfsum == 84) {
            $bonus = 0;
            $revert += $rule["price"];
            $result = 3;
            $rebate = 0;
        } else {
            foreach ($rule["number"] as $n){
                $num = substr($n,strripos($n,"_")+1);
                if ($num == "big") {    //大
                    if($this->lucky_halfsum >= 85 && $this->lucky_halfsum <= 132){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("lucky_halfsum", $n,$rule['rebate_rate']);
                    }
                }
                if($num == "small") {   //小
                    if($this->lucky_halfsum >= 36 && $this->lucky_halfsum <= 83){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("lucky_halfsum", $n,$rule['rebate_rate']);
                    }
                }
                if($num == "odd"){    //单
                    if($this->lucky_halfsum % 2 == 1){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("lucky_halfsum", $n,$rule['rebate_rate']);
                    }
                }
                if($num == "even"){   //双
                    if($this->lucky_halfsum % 2 == 0) {
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("lucky_halfsum", $n,$rule['rebate_rate']);
                    }
                }
                if($num == "tailbig"){   //尾大
                    if($this->lucky_halfsum % 10 >= 5){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("lucky_halfsum", $n,$rule['rebate_rate']);
                    }
                }
                if($num == "tailsmall"){  //尾小
                    if($this->lucky_halfsum % 10 <= 4){
                        $result = 2;
                        $bonus += $rule['price'] * $this->getRate("lucky_halfsum", $n,$rule['rebate_rate']);
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
    //任选两组
    public function lucky_link2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $numAll = $this->combination($rule["number"],2);
        foreach ($numAll as $item){
            $num = [];
            $rate = [];
            foreach ($item as $n){
                $num[] = intval(substr($n, strripos($n, "_") + 1));
                $rate[] = $this->getRate("lucky_link2", $n,$rule['rebate_rate']);
            }
            foreach ($this->lucky_link2 as $items){
                if(count(array_intersect($num,$items)) == 2){
                    $result = 2;
                    $bonus += $rule['price'] * min($rate);
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
    //1v8龙虎
    public function lucky_versus1(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->lucky_versus1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus1", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->lucky_versus1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus1", $n,$rule['rebate_rate']);
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
    //2v7龙虎
    public function lucky_versus2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->lucky_versus2){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus2", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->lucky_versus2){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus2", $n,$rule['rebate_rate']);
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
    public function lucky_versus3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->lucky_versus3){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->lucky_versus3){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus3", $n,$rule['rebate_rate']);
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
    public function lucky_versus4(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n) {
            $num = trim(strrchr($n, '_'),'_');
            if ($num == "dragon") {
                if($this->lucky_versus4){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->lucky_versus4){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("lucky_versus4", $n,$rule['rebate_rate']);
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
    //第一球方位
    public function lucky_wind1(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball1==1 || $this->lucky_ball1==5 || $this->lucky_ball1==9 || $this->lucky_ball1==13 || $this->lucky_ball1==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind1",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball1==2 || $this->lucky_ball1==6 || $this->lucky_ball1==10 || $this->lucky_ball1==14 || $this->lucky_ball1==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind1",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball1==3 || $this->lucky_ball1==7 || $this->lucky_ball1==11 || $this->lucky_ball1==15 || $this->lucky_ball1==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind1",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball1==4 || $this->lucky_ball1==8 || $this->lucky_ball1==12 || $this->lucky_ball1==16 || $this->lucky_ball1==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind1",$n,$rule['rebate_rate']);
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
    //第二球方位
    public function lucky_wind2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball2==1 || $this->lucky_ball2==5 || $this->lucky_ball2==9 || $this->lucky_ball2==13 || $this->lucky_ball2==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind2",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball2==2 || $this->lucky_ball2==6 || $this->lucky_ball2==10 || $this->lucky_ball2==14 || $this->lucky_ball2==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind2",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball2==3 || $this->lucky_ball2==7 || $this->lucky_ball2==11 || $this->lucky_ball2==15 || $this->lucky_ball2==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind2",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball2==4 || $this->lucky_ball2==8 || $this->lucky_ball2==12 || $this->lucky_ball2==16 || $this->lucky_ball2==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind2",$n,$rule['rebate_rate']);
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
    //第三球方位
    public function lucky_wind3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball3==1 || $this->lucky_ball3==5 || $this->lucky_ball3==9 || $this->lucky_ball3==13 || $this->lucky_ball3==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind3",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball3==2 || $this->lucky_ball3==6 || $this->lucky_ball3==10 || $this->lucky_ball3==14 || $this->lucky_ball3==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind3",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball3==3 || $this->lucky_ball3==7 || $this->lucky_ball3==11 || $this->lucky_ball3==15 || $this->lucky_ball3==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind3",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball3==4 || $this->lucky_ball3==8 || $this->lucky_ball3==12 || $this->lucky_ball3==16 || $this->lucky_ball3==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind3",$n,$rule['rebate_rate']);
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
    //第四球方位
    public function lucky_wind4(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball4==1 || $this->lucky_ball4==5 || $this->lucky_ball4==9 || $this->lucky_ball4==13 || $this->lucky_ball4==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind4",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball4==2 || $this->lucky_ball4==6 || $this->lucky_ball4==10 || $this->lucky_ball4==14 || $this->lucky_ball4==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind4",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball4==3 || $this->lucky_ball4==7 || $this->lucky_ball4==11 || $this->lucky_ball4==15 || $this->lucky_ball4==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind4",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball4==4 || $this->lucky_ball4==8 || $this->lucky_ball4==12 || $this->lucky_ball4==16 || $this->lucky_ball4==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind4",$n,$rule['rebate_rate']);
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
    //第五球方位
    public function lucky_wind5(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball5==1 || $this->lucky_ball5==5 || $this->lucky_ball5==9 || $this->lucky_ball5==13 || $this->lucky_ball5==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind5",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball5==2 || $this->lucky_ball5==6 || $this->lucky_ball5==10 || $this->lucky_ball5==14 || $this->lucky_ball5==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind5",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball5==3 || $this->lucky_ball5==7 || $this->lucky_ball5==11 || $this->lucky_ball5==15 || $this->lucky_ball5==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind5",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball5==4 || $this->lucky_ball5==8 || $this->lucky_ball5==12 || $this->lucky_ball5==16 || $this->lucky_ball5==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind5",$n,$rule['rebate_rate']);
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
    //第六球方位
    public function lucky_wind6(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball6==1 || $this->lucky_ball6==5 || $this->lucky_ball6==9 || $this->lucky_ball6==13 || $this->lucky_ball6==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind6",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball6==2 || $this->lucky_ball6==6 || $this->lucky_ball6==10 || $this->lucky_ball6==14 || $this->lucky_ball6==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind6",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball6==3 || $this->lucky_ball6==7 || $this->lucky_ball6==11 || $this->lucky_ball6==15 || $this->lucky_ball6==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind6",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball6==4 || $this->lucky_ball6==8 || $this->lucky_ball6==12 || $this->lucky_ball6==16 || $this->lucky_ball6==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind6",$n,$rule['rebate_rate']);
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
    //第七球方位
    public function lucky_wind7(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball7==1 || $this->lucky_ball7==5 || $this->lucky_ball7==9 || $this->lucky_ball7==13 || $this->lucky_ball7==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind7",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball7==2 || $this->lucky_ball7==6 || $this->lucky_ball7==10 || $this->lucky_ball7==14 || $this->lucky_ball7==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind7",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball7==3 || $this->lucky_ball7==7 || $this->lucky_ball7==11 || $this->lucky_ball7==15 || $this->lucky_ball7==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind7",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball7==4 || $this->lucky_ball7==8 || $this->lucky_ball7==12 || $this->lucky_ball7==16 || $this->lucky_ball7==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind7",$n,$rule['rebate_rate']);
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
    //第八球方位
    public function lucky_wind8(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = trim(strrchr($n, '_'),'_');
            if($num == "east"){  //东
                if($this->lucky_ball8==1 || $this->lucky_ball8==5 || $this->lucky_ball8==9 || $this->lucky_ball8==13 || $this->lucky_ball8==17){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind8",$n,$rule['rebate_rate']);
                }
            }
            if($num == "south"){  //南
                if($this->lucky_ball8==2 || $this->lucky_ball8==6 || $this->lucky_ball8==10 || $this->lucky_ball8==14 || $this->lucky_ball8==18){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind8",$n,$rule['rebate_rate']);
                }
            }
            if($num == "west"){  //西
                if($this->lucky_ball8==3 || $this->lucky_ball8==7 || $this->lucky_ball8==11 || $this->lucky_ball8==15 || $this->lucky_ball8==19){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind8",$n,$rule['rebate_rate']);
                }
            }
            if($num == "north"){  //北
                if($this->lucky_ball8==4 || $this->lucky_ball8==8 || $this->lucky_ball8==12 || $this->lucky_ball8==16 || $this->lucky_ball8==20){
                    $result = 2;
                    $bonus += $rule["price"]*$this->getRate("lucky_wind8",$n,$rule['rebate_rate']);
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