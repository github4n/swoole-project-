<?php
namespace Site\Task\Lottery\Settle;

/*
 * tiktok.php
 * @description   时时彩结算任务
 * @Author  nathan 
 * @date  2019-05-09
 * @links  Lottery/Settle/tiktok 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 */
class tiktok extends Base
{
    // 准备结算数据
    private $number, $any, $half, $poker, $sum, $sum123,$sum234,$sum345,$versus,$bit;
    protected function loadNumber(array $number)
    {
        $this->number = $number;
        // 计算数字是否出现，为不定位做准备
        $this->any = [];
        //两面球
        $this->bit = [];
        for ($i = 1; $i < 6; $i++) {
            $n = $number['normal'.($i)];
            $this->any[$n] = true;
            $this->bit['tiktok_bit'.$i.'_'.$n] = true;
        }
        $this->half = []; // 计算两面结果
        for($i = 1;$i < 6;$i++)
        {
            $n = $number['normal'.$i];
            if ($n>=5)
            {
                $this->half['tiktok_half'.$i.'_'.'big'] = true;
            }else
            {
                $this->half['tiktok_half'.$i.'_'.'small'] = true;
            }
            if ($n % 2 == 0)
            {
                $this->half['tiktok_half'.$i.'_'.'even'] = true;
            }else
            {
                $this->half['tiktok_half'.$i.'_'.'odd'] = true;
            }

        }

        // 计算前中后三、梭哈结果
        $this->poker = [];
        $this->sum123 = [];
        $this->sum234= [];
        $this->sum345 = [];
        for ($i=1;$i<4;$i++)
        {

            //前三
            if($i==1)
            {
                $j=$i+1;
                $k=$i+2;
                $tiktok_sum123 = $number['normal'.$i]+$number['normal'.$j]+$number['normal'.$k];
                $this->sum123['tiktok_sum123'.'_'.$tiktok_sum123] = true;
                $three = array($number['normal'.$i],$number['normal'.$j],$number['normal'.$k]);
                sort($three);

                if ($three[0] == $three[2])
                {
                    $this->poker['tiktok_poker123_triple'] = true;
                }else if ($three[0] == $three[1] || $three[1] == $three[2])
                {
                    $this->poker['tiktok_poker123_pair'] = true;
                }else if (!in_array(0,$three) && $three[0]+1==$three[1] && $three[1]+1==$three[2])
                {
                    $this->poker['tiktok_poker123_serial'] = true;
                }else if (in_array(0, $three) && (($three[0] == 0 && $three[1] == 8 && $three[2] ==9) || ($three[0] == 0 && $three[1] == 1 && $three[2] ==9) || ($three[0] == 0 && $three[1] == 1 && $three[2] ==2)))
                {
                    $this->poker['tiktok_poker123_serial'] = true;
                }else if (($three[0]+1 == $three[1] && $three[1]+1 != $three[2]) || ($three[0]+1!=$three[1] && $three[1]+1 == $three[2]))
                {
                    $this->poker['tiktok_poker123_abut'] = true;
                }else if($three[0]==0 && $three[2]==9 || $three[0]==0 && $three[1]==1)
                {
                    $this->poker['tiktok_poker123_abut'] = true;
                }else
                {
                    $this->poker['tiktok_poker123_mixed'] = true;
                }
            }
            //中三
            if ($i==2)
            {
                $j=$i+1;
                $k=$i+2;
                $tiktok_sum234 = $number['normal'.$i]+$number['normal'.$j]+$number['normal'.$k];
                $this->sum234['tiktok_sum234'.'_'.$tiktok_sum234] = true;
                $three = array($number['normal'.$i],$number['normal'.$j],$number['normal'.$k]);
                sort($three);
                if ($three[0] == $three[2])
                {
                    $this->poker['tiktok_poker234_triple'] = true;
                }else if ($three[0] == $three[1] || $three[1] == $three[2])
                {
                    $this->poker['tiktok_poker234_pair'] = true;
                }else if (!in_array(0,$three) && $three[0]+1==$three[1] && $three[1]+1==$three[2])
                {
                    $this->poker['tiktok_poker234_serial'] = true;
                }else if (in_array(0, $three) && (($three[0] == 0 && $three[1] == 8 && $three[2] ==9) || ($three[0] == 0 && $three[1] == 1 && $three[2] ==9) || ($three[0] == 0 && $three[1] == 1 && $three[2] ==2)))
                {
                    $this->poker['tiktok_poker234_serial'] = true;
                }else if (($three[0]+1 == $three[1] && $three[1]+1 != $three[2]) || ($three[0]+1!=$three[1] && $three[1]+1 == $three[2]))
                {
                    $this->poker['tiktok_poker234_abut'] = true;
                }else if($three[0]==0 && $three[2]==9 || $three[0]==0 && $three[1]==1)
                {
                    $this->poker['tiktok_poker234_abut'] = true;
                }else
                {
                    $this->poker['tiktok_poker234_mixed'] = true;
                }
            }
            //后三
            if ($i==3)
            {
                $j=$i+1;
                $k=$i+2;
                $tiktok_sum345 = $number['normal'.$i]+$number['normal'.$j]+$number['normal'.$k];
                $this->sum345['tiktok_sum345'.'_'.$tiktok_sum345] = true;
                $three = array($number['normal'.$i],$number['normal'.$j],$number['normal'.$k]);
                sort($three);
                if ($three[0] == $three[2])
                {
                    $this->poker['tiktok_poker345_triple'] = true;
                }else if ($three[0] == $three[1] || $three[1] == $three[2])
                {
                    $this->poker['tiktok_poker345_pair'] = true;
                }else if (!in_array(0,$three) && $three[0]+1==$three[1] && $three[1]+1==$three[2])
                {
                    $this->poker['tiktok_poker345_serial'] = true;
                }else if (in_array(0, $three) && (($three[0] == 0 && $three[1] == 8 && $three[2] ==9) || ($three[0] == 0 && $three[1] == 1 && $three[2] ==9) || ($three[0] == 0 && $three[1] == 1 && $three[2] ==2)))
                {
                    $this->poker['tiktok_poker345_serial'] = true;
                }else if (($three[0]+1 == $three[1] && $three[1]+1 != $three[2]) || ($three[0]+1!=$three[1] && $three[1]+1 == $three[2]))
                {
                    $this->poker['tiktok_poker345_abut'] = true;
                }else if($three[0]==0 && $three[2]==9 || $three[0]==0 && $three[1]==1)
                {
                    $this->poker['tiktok_poker345_abut'] = true;
                }else
                {
                    $this->poker['tiktok_poker345_mixed'] = true;
                }
            }
        }
        //梭哈
        $poker5 = array();
        for ($i = 1; $i < 6; $i++) {
            $poker5[$i] = $number['normal' . $i];
        }
        $a = sort($poker5);
        $poker5_sum = array_sum($poker5);
        $avg = $poker5_sum / 5;
        if($a)
        {
            if ($poker5[0] == $poker5[4]) {
                $this->poker['tiktok_poker5_quint'] = true;
            } else if ($poker5[0] != $poker5[1] && $poker5[1] == $poker5[2] && $poker5[1] == $poker5[3] && $poker5[1] == $poker5[4]) {
                $this->poker['tiktok_poker5_bomb'] = true;
            } else if ($poker5[3] != $poker5[4] && $poker5[3] == $poker5[2] && $poker5[3] == $poker5[1] && $poker5[3] == $poker5[0]) {
                $this->poker['tiktok_poker5_bomb'] = true;
            } else if (!in_array(0, $poker5) && count(array_unique($poker5)) == 5 && $avg==$poker5[2] && $poker5[0]+1 == $poker5[1] && $poker5[1]+1 == $poker5[2] && $poker5[2]+1 == $poker5[3] && $poker5[3]+1 == $poker5[4]) {
                $this->poker['tiktok_poker5_serial'] = true;
            } else if (in_array(0, $poker5) && (($poker5==[0,6,7,8,9]) || ($poker5==[0,1,7,8,9]) || ($poker5==[0,1,2,8,9]) || ($poker5==[0,1,2,3,9]) || ($poker5==[0,1,2,3,4]))) {
                $this->poker['tiktok_poker5_serial'] = true;
            }else if (($poker5[0] == $poker5[1] && $poker5[0] == $poker5[2] && $poker5[2] != $poker5[3] && $poker5[3] == $poker5[4]) || ($poker5[0] == $poker5[1] && $poker5[1] != $poker5[2] && $poker5[2] == $poker5[3] && $poker5[2] == $poker5[4]))
            {
                $this->poker['tiktok_poker5_gourd'] = true;
            }else if (($poker5[0] == $poker5[1] && $poker5[0] == $poker5[2] && $poker5[2] != $poker5[3] && $poker5[2] != $poker5[4] && $poker5[3] != $poker5[4]) || ($poker5[0] != $poker5[1] && $poker5[0] != $poker5[2] && $poker5[1] != $poker5[2] && $poker5[2] == $poker5[3] && $poker5[2] == $poker5[4]) || ($poker5[0] != $poker5[1] && $poker5[1] == $poker5[2] && $poker5[1] == $poker5[3] && $poker5[1] != $poker5[4] && $poker5[0] != $poker5[4]))
            {
                $this->poker['tiktok_poker5_triple'] = true;
            }else if($poker5[0] != $poker5[1] && $poker5[1] == $poker5[2] && $poker5[2] != $poker5[3] && $poker5[3] == $poker5[4])
            {
                $this->poker['tiktok_poker5_pair2'] = true;
            }else if ($poker5[0] == $poker5[1] && $poker5[1] != $poker5[2] && $poker5[2] != $poker5[3] && $poker5[3] == $poker5[4])
            {
                $this->poker['tiktok_poker5_pair2'] = true;
            }else if ($poker5[0] == $poker5[1] && $poker5[1] != $poker5[2] && $poker5[2] == $poker5[3] && $poker5[3] != $poker5[4])
            {
                $this->poker['tiktok_poker5_pair2'] = true;
            }else if (count(array_unique($poker5)) == 4)
            {
                $this->poker['tiktok_poker5_pair1'] = true;
            }else
            {
                $this->poker['tiktok_poker5_mixed'] = true;
            }

        }

        //和值
        $this->sum = [];
        $tiktok_sum = 0;
        for ($i=1;$i<6;$i++)
        {
            $tiktok_sum += $number['normal'.$i];
        }
        if ($tiktok_sum >= 23)
        {
            $this->sum['tiktok_sum_big'] = true;
        }else if ($tiktok_sum <= 22)
        {
            $this->sum['tiktok_sum_small'] = true;
        }

        if($tiktok_sum % 2 == 0)
        {
            $this->sum['tiktok_sum_even'] = true;
        }else if ($tiktok_sum % 2 == 1)
        {
            $this->sum['tiktok_sum_odd'] = true;
        }

        //龙虎
        $this->versus = [];
        for ($i=1;$i<6;$i++)
        {
            for ($j=$i+1;$j<6;$j++)
            {

                if ($number['normal'.$i] > $number['normal'.$j])
                {
                    $this->versus['tiktok_versus'.$i.$j.'_'.'dragon'] = true;
                }else if ($number['normal'.$i] < $number['normal'.$j])
                {
                    $this->versus['tiktok_versus'.$i.$j.'_'.'tiger'] = true;
                }else
                {
                    $this->versus['tiktok_versus'.$i.$j.'_'.'tie'] = true;
                }
            }
        }

    }

    // 计算一码不定位
    public function tiktok_any1(array $rule): array
    {
        $result = 1;
        $bonus = 0.0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $j) {;
            foreach ($j as $k => $v)
            {
                $num = substr($v,12);
                if (isset($this->any[$num]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_any1', $v,$rule['rebate_rate']) ;
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => sprintf("%.2f",substr(sprintf("%.3f", $bonus), 0, -2)),
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0,
        ];
    }
    //计算二码不定位
    public function tiktok_any2(array $rule): array
    {
        $result = 1;
        $bonus = 0.0;
        $bet_num = $this->combination($rule['number'],2);
        foreach ($bet_num as $j)
        {
           foreach ($j as $k=>$v)
           {
               //$len = strlen($rule['play_key']);
               $bet_number = substr($v,12);
               $rate[] = $this->getRate('tiktok_any2',$v,$rule['rebate_rate']);
               if (isset($this->any[$bet_number]))
               {
                   $flag = true;
               }else
               {
                   $flag = false;
                   break;
               }
           }
            sort($rate);
            if ($flag)
            {
                $result = 2;
                $bonus += $rule['price'] * $rate[0];
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
    //计算三码不定位
    public function tiktok_any3(array $rule): array
    {
        $result = 1;
        $bonus = 0.0;
        $bet_num = $this->combination($rule['number'],3);
        foreach ($bet_num as $j)
        {
           foreach ($j as $k => $v)
           {
               $bet_number = substr($v,12);
               $rate[] = $this->getRate('tiktok_any3',$v,$rule['rebate_rate']);
               if (isset($this->any[$bet_number]))
               {
                   $flag = true;
               }else
               {
                   $flag = false;
                   break;
               }
           }
           sort($rate);
           if ($flag)
           {
               $result = 2;
               $bonus += $rule['price'] * $rate[0];
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

    //计算万位两面
    public function tiktok_half1(array $rule ) :array
    {
        $result = 1;
        $bonus = 0.0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n )
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->half[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_half1', $v,$rule['rebate_rate']);
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

    //计算千位两面
    public function tiktok_half2(array $rule ) :array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n )
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->half[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_half2', $v,$rule['rebate_rate']);
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

    //计算百位两面
    public function tiktok_half3(array $rule ) :array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n )
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->half[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_half3', $v,$rule['rebate_rate']);
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

    //计算十位两面
    public function tiktok_half4(array $rule ) :array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n )
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->half[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_half4', $v,$rule['rebate_rate']);
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

    //计算个位两面
    public function tiktok_half5(array $rule ) :array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n )
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->half[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_half5', $v,$rule['rebate_rate']);
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


    //前三
    public function  tiktok_poker123(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->poker[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_poker123', $v,$rule['rebate_rate']);
                }
            }

        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' => 0
        ];
    }
    //中三
    public function  tiktok_poker234(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->poker[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_poker234', $v,$rule['rebate_rate']);
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
    //后三
    public function  tiktok_poker345(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->poker[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_poker345', $v,$rule['rebate_rate']);
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
    //梭哈
    public function  tiktok_poker5(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->poker[$v]))
                {
                    $result = 2;
                    $bonus += $rule['price'] * $this->getRate('tiktok_poker5', $v,$rule['rebate_rate']);
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
    //和值
    public function  tiktok_sum(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->sum[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_sum', $v,$rule['rebate_rate']);
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
    //前和值
    public function  tiktok_sum123(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->sum123[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_sum123', $v,$rule['rebate_rate']);
                }
            }
        }
        return [
            'rule_id' => $rule['rule_id'],
            'result' => $result,
            'bet' => $rule['bet_launch'],
            'bonus' => $bonus,
            'rebate' => substr(sprintf("%.3f", $rule['bet_launch'] * $rule['rebate_rate'] / 100),0,-1),
            'revert' =>0,
        ];
    }
    //中和值
    public function  tiktok_sum234(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->sum234[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_sum234', $v,$rule['rebate_rate']);
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
    //后和值
    public function  tiktok_sum345(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->sum345[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_sum345', $v,$rule['rebate_rate']);
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
    //1v2龙虎
    public function  tiktok_versus12(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus12', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus13(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus13', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus14(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus14', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus15(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus15', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus23(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus23', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus24(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus24', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus25(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus25', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus34(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus34', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus35(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus35', $v,$rule['rebate_rate']);
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
    public function  tiktok_versus45(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->versus[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_versus45', $v,$rule['rebate_rate']);
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

    //万位
    public function  tiktok_bit1(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
           foreach ($n as $k => $v)
           {
               if (isset($this->bit[$v]))
               {
                   $result = 2;
                   $bonus +=  $rule['price'] * $this->getRate('tiktok_bit1', $v,$rule['rebate_rate']);
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

    //千位
    public function  tiktok_bit2(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->bit[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_bit2', $v,$rule['rebate_rate']);
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

    //百位
    public function  tiktok_bit3(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->bit[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_bit3', $v,$rule['rebate_rate']);
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

    //十位
    public function  tiktok_bit4(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->bit[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_bit4', $v,$rule['rebate_rate']);
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

    //个位
    public function  tiktok_bit5(array $rule): array
    {
        $result = 1;
        $bonus = 0;
        $bet_num = $this->combination($rule['number'],1);
        foreach ($bet_num as $n)
        {
            foreach ($n as $k => $v)
            {
                if (isset($this->bit[$v]))
                {
                    $result = 2;
                    $bonus +=  $rule['price'] * $this->getRate('tiktok_bit5', $v,$rule['rebate_rate']);
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
