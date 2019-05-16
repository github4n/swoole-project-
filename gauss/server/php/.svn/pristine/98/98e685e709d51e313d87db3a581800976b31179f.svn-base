<?php

namespace App\Websocket\User\PersonalReport;
use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;
/*
 * 我的--个人报表--彩票报表
 * User/PersonalReport/LotteryReport {"date":"yesterday"}
 * 不传参数为今日报表 today,yesterday,thisWeek,lastWeek,thisMonth,lastMonth
 * */
class LotteryReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $reportData = [];
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }

        $data = $context->getData();
        $date = empty($data['time'])?'today':$data['time'];
        $userId = $context->getInfo('UserId');
        $reportMysql = $config->data_report;
        switch ($date)
        {
            case 'yesterday':
                $time = date('Ymd',strtotime('-1 day'));
                $sql = 'SELECT bonus_amount,wager_amount AS bet_amount,coupon_amount,rebate_amount,subsidy_amount FROM daily_user WHERE user_id=:user_id AND daily=:time';
                $param = [
                    ':user_id' => $userId,
                    ':time' => $time
                ];
                foreach ($reportMysql->query($sql, $param) as $row)
                {
                    $reportData['bonus_amount'] = substr(sprintf("%.3f", $row['bonus_amount']), 0, -1);
                    $reportData['bet_amount'] =  substr(sprintf("%.3f", $row['bet_amount']), 0, -1);
                    $reportData['rebate_amount'] = substr(sprintf("%.3f", $row['rebate_amount']), 0, -1);
                    $reportData['coupon_amount'] = substr(sprintf("%.3f", $row['coupon_amount']), 0, -1);
                    $reportData['subsidy_amount'] = substr(sprintf("%.3f", $row['subsidy_amount']), 0, -1);
                }
                $reportData['bonus_amount'] = isset($reportData['bonus_amount'])?$reportData['bonus_amount']:0;
                $reportData['bet_amount'] = isset($reportData['bet_amount'])?$reportData['bet_amount']:0;
                $reportData['rebate_amount'] = isset($reportData['rebate_amount'])?$reportData['rebate_amount']:0;
                $reportData['coupon_amount'] = isset($reportData['coupon_amount'])?$reportData['coupon_amount']:0;
                $reportData['subsidy_amount'] = isset($reportData['subsidy_amount'])?$reportData['subsidy_amount']:0;

                break;
            case 'thisWeek':
                $time = date('oW',strtotime('this week'));
                $sql = 'SELECT bonus_amount,wager_amount AS bet_amount,coupon_amount,rebate_amount,subsidy_amount FROM weekly_user WHERE  user_id=:user_id AND weekly=:time';
                $param = [
                    ':user_id' => $userId,
                    ':time' => $time
                ];
                foreach ($reportMysql->query($sql, $param) as $row)
                {
                    $reportData['bonus_amount'] = substr(sprintf("%.3f", $row['bonus_amount']), 0, -1);
                    $reportData['bet_amount'] =  substr(sprintf("%.3f", $row['bet_amount']), 0, -1);
                    $reportData['rebate_amount'] = substr(sprintf("%.3f", $row['rebate_amount']), 0, -1);
                    $reportData['coupon_amount'] = substr(sprintf("%.3f", $row['coupon_amount']), 0, -1);
                    $reportData['subsidy_amount'] = substr(sprintf("%.3f", $row['subsidy_amount']), 0, -1);
                }
                $reportData['bonus_amount'] = isset($reportData['bonus_amount'])?$reportData['bonus_amount']:0;
                $reportData['bet_amount'] = isset($reportData['bet_amount'])?$reportData['bet_amount']:0;
                $reportData['rebate_amount'] = isset($reportData['rebate_amount'])?$reportData['rebate_amount']:0;
                $reportData['coupon_amount'] = isset($reportData['coupon_amount'])?$reportData['coupon_amount']:0;
                $reportData['subsidy_amount'] = isset($reportData['subsidy_amount'])?$reportData['subsidy_amount']:0;
                break;
            case 'lastWeek':
                $time = date('oW',strtotime('-1 week'));
                $sql = 'SELECT bonus_amount,wager_amount AS bet_amount,coupon_amount,rebate_amount,subsidy_amount FROM weekly_user WHERE  user_id=:user_id AND weekly=:time';
                $param = [
                    ':user_id' => $userId,
                    ':time' => $time
                ];
                foreach ($reportMysql->query($sql, $param) as $row)
                {
                    $reportData['bonus_amount'] = substr(sprintf("%.3f", $row['bonus_amount']), 0, -1);
                    $reportData['bet_amount'] =  substr(sprintf("%.3f", $row['bet_amount']), 0, -1);
                    $reportData['rebate_amount'] = substr(sprintf("%.3f", $row['rebate_amount']), 0, -1);
                    $reportData['coupon_amount'] = substr(sprintf("%.3f", $row['coupon_amount']), 0, -1);
                    $reportData['subsidy_amount'] = substr(sprintf("%.3f", $row['subsidy_amount']), 0, -1);
                }
                $reportData['bonus_amount'] = isset($reportData['bonus_amount'])?$reportData['bonus_amount']:0;
                $reportData['bet_amount'] = isset($reportData['bet_amount'])?$reportData['bet_amount']:0;
                $reportData['rebate_amount'] = isset($reportData['rebate_amount'])?$reportData['rebate_amount']:0;
                $reportData['coupon_amount'] = isset($reportData['coupon_amount'])?$reportData['coupon_amount']:0;
                $reportData['subsidy_amount'] = isset($reportData['subsidy_amount'])?$reportData['subsidy_amount']:0;
                break;
            case 'thisMonth':
                $time = date('Ym',strtotime('this month'));
                $sql = 'SELECT bonus_amount,wager_amount AS bet_amount,coupon_amount,rebate_amount,subsidy_amount FROM monthly_user WHERE  user_id=:user_id AND monthly=:time';
                $param = [
                    ':user_id' => $userId,
                    ':time' => $time
                ];
                foreach ($reportMysql->query($sql, $param) as $row)
                {
                    $reportData['bonus_amount'] = substr(sprintf("%.3f", $row['bonus_amount']), 0, -1);
                    $reportData['bet_amount'] =  substr(sprintf("%.3f", $row['bet_amount']), 0, -1);
                    $reportData['rebate_amount'] = substr(sprintf("%.3f", $row['rebate_amount']), 0, -1);
                    $reportData['coupon_amount'] = substr(sprintf("%.3f", $row['coupon_amount']), 0, -1);
                    $reportData['subsidy_amount'] = substr(sprintf("%.3f", $row['subsidy_amount']), 0, -1);
                }
                $reportData['bonus_amount'] = isset($reportData['bonus_amount'])?$reportData['bonus_amount']:0;
                $reportData['bet_amount'] = isset($reportData['bet_amount'])?$reportData['bet_amount']:0;
                $reportData['rebate_amount'] = isset($reportData['rebate_amount'])?$reportData['rebate_amount']:0;
                $reportData['coupon_amount'] = isset($reportData['coupon_amount'])?$reportData['coupon_amount']:0;
                $reportData['subsidy_amount'] = isset($reportData['subsidy_amount'])?$reportData['subsidy_amount']:0;
                break;
            case 'lastMonth':
                $time = date('Ymd',strtotime('-1 month'));
                $sql = 'SELECT bonus_amount,wager_amount AS bet_amount,coupon_amount,rebate_amount,subsidy_amount FROM monthly_user WHERE user_id=:user_id AND monthly=:time';
                $param = [
                    ':user_id' => $userId,
                    ':time' => $time
                ];
                foreach ($reportMysql->query($sql, $param) as $row)
                {
                    $reportData['bonus_amount'] = substr(sprintf("%.3f", $row['bonus_amount']), 0, -1);
                    $reportData['bet_amount'] =  substr(sprintf("%.3f", $row['bet_amount']), 0, -1);
                    $reportData['rebate_amount'] = substr(sprintf("%.3f", $row['rebate_amount']), 0, -1);
                    $reportData['coupon_amount'] = substr(sprintf("%.3f", $row['coupon_amount']), 0, -1);
                    $reportData['subsidy_amount'] = substr(sprintf("%.3f", $row['subsidy_amount']), 0, -1);
                }
                $reportData['bonus_amount'] = isset($reportData['bonus_amount'])?$reportData['bonus_amount']:0;
                $reportData['bet_amount'] = isset($reportData['bet_amount'])?$reportData['bet_amount']:0;
                $reportData['rebate_amount'] = isset($reportData['rebate_amount'])?$reportData['rebate_amount']:0;
                $reportData['coupon_amount'] = isset($reportData['coupon_amount'])?$reportData['coupon_amount']:0;
                $reportData['subsidy_amount'] = isset($reportData['subsidy_amount'])?$reportData['subsidy_amount']:0;
                break;
            default:
                $time = date('Ymd',strtotime('today'));
                $sql = 'SELECT bonus_amount,wager_amount AS bet_amount,coupon_amount,rebate_amount,subsidy_amount FROM daily_user WHERE user_id=:user_id AND daily=:time';
                $param = [
                    ':user_id' => $userId,
                    ':time' => $time
                ];
                foreach ($reportMysql->query($sql, $param) as $row)
                {
                    $reportData['bonus_amount'] = substr(sprintf("%.3f", $row['bonus_amount']), 0, -1);
                    $reportData['bet_amount'] =  substr(sprintf("%.3f", $row['bet_amount']), 0, -1);
                    $reportData['rebate_amount'] = substr(sprintf("%.3f", $row['rebate_amount']), 0, -1);
                    $reportData['coupon_amount'] = substr(sprintf("%.3f", $row['coupon_amount']), 0, -1);
                    $reportData['subsidy_amount'] = substr(sprintf("%.3f", $row['subsidy_amount']), 0, -1);
                }
                $reportData['bonus_amount'] = isset($reportData['bonus_amount'])?$reportData['bonus_amount']:0;
                $reportData['bet_amount'] = isset($reportData['bet_amount'])?$reportData['bet_amount']:0;
                $reportData['rebate_amount'] = isset($reportData['rebate_amount'])?$reportData['rebate_amount']:0;
                $reportData['coupon_amount'] = isset($reportData['coupon_amount'])?$reportData['coupon_amount']:0;
                $reportData['subsidy_amount'] = isset($reportData['subsidy_amount'])?$reportData['subsidy_amount']:0;
                break;
        }
        $context->reply(['status' => 200,'msg' => '获取成功', 'data' => $reportData]);
    }
}