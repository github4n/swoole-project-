<?php
namespace Site\Task\Lottery\Settle;

/*
 * racer.php
 * @description   赛车/飞艇结算任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/Settle/racer 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class racer extends Base
{
    // 准备结算数据
    private $number, $racer_car1, $racer_car10, $racer_car2, $racer_car3, $racer_car4, $racer_car5,$racer_car6,$racer_car7,$racer_car8,$racer_car9,$racer_halfsum,$racer_sum,$racer_versus1,$racer_versus2,$racer_versus3,$racer_versus4,$racer_versus5;
    protected function loadNumber(array $number)
    {
        $this->number = $number;

        // 计算冠军 (两面冠军)
        $this->racer_car1 = $number['normal1'];
        //计算第十名
        $this->racer_car10 = $number["normal10"];
        //计算亚军
        $this->racer_car2 = $number["normal2"];
        //计算第三球
        $this->racer_car3 = $number["normal3"];
        //计算第四球
        $this->racer_car4 = $number["normal4"];
        //计算第五球
        $this->racer_car5 = $number["normal5"];
        //计算第六球
        $this->racer_car6 = $number["normal6"];
        //计算第七球
        $this->racer_car7 = $number["normal7"];
        //计算第八球
        $this->racer_car8 = $number["normal8"];
        //计算第九球
        $this->racer_car9 = $number["normal9"];
        //冠亚和两面
        $this->racer_halfsum = $number["normal1"]+$number["normal2"];
        //冠亚和
        $this->racer_sum =  $number["normal1"]+$number["normal2"];
        //1v10龙虎
        $this->racer_versus1 = false;
        if($number["normal1"]>$number["normal10"]){
            $this->racer_versus1 = true;
        }
        //2v9龙虎
        $this->racer_versus2 = false;
        if($number["normal2"]>$number["normal9"]){
            $this->racer_versus2 = true;
        }
        //3v8龙虎
        $this->racer_versus3 = false;
        if($number["normal3"]>$number["normal8"]){
            $this->racer_versus3 = true;
        }
        //4v7龙虎
        $this->racer_versus4 = false;
        if($number["normal4"]>$number["normal7"]){
            $this->racer_versus4 = true;
        }
        //5v6龙虎racer_car1
        $this->racer_versus5 = false;
        if($number["normal5"]>$number["normal6"]){
            $this->racer_versus5 = true;
        }

    }

    // 冠军
    public function racer_car1(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = intval(substr($n,strripos($n,"_")+1));
            if ($num == $this->racer_car1) {
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("racer_car1", $n,$rule['rebate_rate']);
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
    //第十名
    public function  racer_car10(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = intval(substr($n,strripos($n,"_")+1));
            if ($num == $this->racer_car10) {
                $result = 2;
                $bonus += $rule['price'] * $this->getRate("racer_car10", $n,$rule['rebate_rate']);
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
    //亚军
     public function  racer_car2(array $rule):array
     {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = intval(substr($n,strripos($n,"_")+1));
             if ($num == $this->racer_car2) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car2", $n,$rule['rebate_rate']);
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
     //第三名
    public function racer_car3(array $rule):array
    {
        $result = 1;
        $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = intval(substr($n,strripos($n,"_")+1));
             if ($num == $this->racer_car3) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car3", $n,$rule['rebate_rate']);
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
    //第四名
    public function racer_car4(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = substr($n, strripos($n, "_") + 1);
             if ($num == $this->racer_car4) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car4", $n,$rule['rebate_rate']);
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
    //第五名
    public function racer_car5(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = substr($n, strripos($n, "_") + 1);
             if ($num == $this->racer_car5) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car5", $n,$rule['rebate_rate']);
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
    //第六名
    public function racer_car6(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = substr($n, strripos($n, "_") + 1);
             if ($num == $this->racer_car6) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car6", $n,$rule['rebate_rate']);
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
    //第七名
    public function racer_car7(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = intval(substr($n, strripos($n, "_") + 1));
             if ($num == $this->racer_car7) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car7", $n,$rule['rebate_rate']);
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
    //第八名
    public function racer_car8(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = intval(substr($n, strripos($n, "_") + 1));
             if ($num == $this->racer_car8) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car8", $n,$rule['rebate_rate']);
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
    //第九名
    public function racer_car9(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = intval(substr($n, strripos($n, "_") + 1));
             if ($num == $this->racer_car9) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_car9", $n,$rule['rebate_rate']);
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
    //冠军两面
    public function racer_half1(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = substr($n, strripos($n, "_") + 1);
             if ($num == "big") {
                 if($this->racer_car1 > 5){
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half1", $n,$rule['rebate_rate']);
                 }
             }
             if($num == "small") {
                 if($this->racer_car1 <= 5){
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half1", $n,$rule['rebate_rate']);
                 }
             }
             if($num == "odd"){
                 if($this->racer_car1%2 == 1){
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half1", $n,$rule['rebate_rate']);
                 }
             }
             if($num == "even"){
                 if($this->racer_car1%2 == 0) {
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half1", $n,$rule['rebate_rate']);
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
    //第十名两面
    public function racer_half10(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car10 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half10", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_car10 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half10", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car10%2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half10", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car10%2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half10", $n,$rule['rebate_rate']);
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
    //亚军两面
     public function racer_half2(array $rule):array
     {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n) {
             $num = substr($n, strripos($n, "_") + 1);
             if ($num == "big") {
                 if($this->racer_car2 > 5){
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half2", $n,$rule['rebate_rate']);
                 }
             }
             if($num == "small") {
                 if($this->racer_car2 <= 5){
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half2", $n,$rule['rebate_rate']);
                 }
             }
             if($num == "odd"){
                 if($this->racer_car2%2 == 1){
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half2", $n,$rule['rebate_rate']);
                 }
             }
             if($num == "even"){
                 if($this->racer_car2%2 == 0) {
                     $result = 2;
                     $bonus += $rule['price'] * $this->getRate("racer_half2", $n,$rule['rebate_rate']);
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
     //第三名两面
    public function racer_half3(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car3 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_car3 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car3 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car3 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half3", $n,$rule['rebate_rate']);
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
    //第四名两面
    public function racer_half4(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car4 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_car4 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car4 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car4 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half4", $n,$rule['rebate_rate']);
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
    //第五名两面
    public function racer_half5(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car5 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half5", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_car5 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half5", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car5 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half5", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car5 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half5", $n,$rule['rebate_rate']);
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
    //第六名两面
    public function racer_half6(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car6 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half6", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_car6 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half6", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car6 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half6", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car6 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half6", $n,$rule['rebate_rate']);
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
    //第七名两面
    public function racer_half7(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car7 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half7", $n,$rule['rebate_rate']);
                } 
            }
            if($num == "small") {
                if($this->racer_car7 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half7", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car7 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half7", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car7 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half7", $n,$rule['rebate_rate']);
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
    //第八名两面
    public function racer_half8(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car8 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half8", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_car8 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half8", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car8 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half8", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car8 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half8", $n,$rule['rebate_rate']);
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
    //第九名两面
    public function racer_half9(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_car9 > 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half9", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_car9 <= 5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half9", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_car9 % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half9", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_car9 % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_half9", $n,$rule['rebate_rate']);
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
    //冠亚和两面
    public function racer_halfsum(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "big") {
                if($this->racer_halfsum > 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_halfsum", $n,$rule['rebate_rate']);
                }
            }
            if($num == "small") {
                if($this->racer_halfsum <= 11){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_halfsum", $n,$rule['rebate_rate']);
                }
            }
            if($num == "odd"){
                if($this->racer_halfsum % 2 == 1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_halfsum", $n,$rule['rebate_rate']);
                }
            }
            if($num == "even"){
                if($this->racer_halfsum % 2 == 0) {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_halfsum", $n,$rule['rebate_rate']);
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
    //冠亚和
    public function racer_sum(array $rule):array
    {
         $result = 1;
         $bonus = 0;
         foreach ($rule['number'] as $n){
             $num = intval(substr($n, strripos($n, "_") + 1));
             if ($num == $this->racer_sum) {
                 $result = 2;
                 $bonus += $rule['price'] * $this->getRate("racer_sum", $n,$rule['rebate_rate']);
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
    //1v10龙虎
    public function racer_versus1(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "dragon") {
                if($this->racer_versus1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus1", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->racer_versus1){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus1", $n,$rule['rebate_rate']);
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
    //2v9龙虎
    public function racer_versus2(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "dragon") {
                if($this->racer_versus2){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus2", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->racer_versus2){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus2", $n,$rule['rebate_rate']);
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
    //3v8龙虎
    public function racer_versus3(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "dragon") {
                if($this->racer_versus3){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus3", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->racer_versus3){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus3", $n,$rule['rebate_rate']);
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
    //4v7龙虎
    public function racer_versus4(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "dragon") {
                if($this->racer_versus4){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus4", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->racer_versus4){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus4", $n,$rule['rebate_rate']);
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
    public function racer_versus5(array $rule):array
    {
        $result = 1;
        $bonus = 0;
        foreach ($rule['number'] as $n){
            $num = substr($n, strripos($n, "_") + 1);
            if ($num == "dragon") {
                if($this->racer_versus5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus5", $n,$rule['rebate_rate']);
                }
            }
            if($num == "tiger") {
                if(!$this->racer_versus5){
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate("racer_versus5", $n,$rule['rebate_rate']);
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
