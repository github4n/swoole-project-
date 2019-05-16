<?php

namespace Site\Websocket\ReportQuery;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * MonthlyReport.php
 *
 * @description   报表查询-月结对账报表
 * @Author  Luis
 * @date  2019-04-07
 * @links  ReportQuery/MonthlyReport {"date":"2019-04-01"}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
class MonthlyReport extends CheckLogin {

    public function onReceiveLogined(Context $context, Config $config) {
        $data = $context->getData();
        $date = !empty($data['date']) ? $data['date'] : '';
        $staff_grade = $context->getInfo('StaffGrade');

        //检查权限
        if ($staff_grade != 0) {
            $context->reply(['status' => 201, 'msg' => '当前账号没有权限']);
            return;
        }

        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array("report_tax", $auth)) {
            $context->reply(["status" => 202, "msg" => "你还没有操作权限"]);
        return;
    }

        if (empty($date)) {
            $monthly =  date('Ym', strtotime('today'));
        } else {
            $monthly =  date('Ym', strtotime($date));
        }
        $mysql = $config->data_report;
        if($monthly == intval(date("Ym",strtotime("today")))){
            //本月投注,派彩,损益
            $sql = 'SELECT SUM(wager_amount) AS bet_sum,SUM(bonus_amount) AS bonus_sum,SUM(wager_amount - bonus_amount - rebate_amount ) AS profit_sum FROM monthly_staff_lottery WHERE monthly=:monthly' ;
            $param = [":monthly" => $monthly];
            foreach ($mysql->query($sql,$param) as $row) {
                $statisticData['bet_amount'] = empty($row['bet_sum']) ? '0.00' : $this->intercept_num($row['bet_sum']);
                $statisticData['bonus_amount'] = empty($row['bonus_sum']) ? '0.00' : $this->intercept_num($row['bonus_sum']);
                $statisticData['profit_amount'] = empty($row['profit_sum']) ? '0.00' : $this->intercept_num($row['profit_sum']);
            }

            //外接口数据
            $fg_bet = 0;
            $fg_bonus = 0;
            $fg_profit = 0;
            $ky_bet = 0;
            $ky_bonus = 0;
            $ky_profit = 0;
            $lb_bet = 0;
            $lb_bonus = 0;
            $lb_profit = 0;
            $ag_bet = 0;
            $ag_bonus = 0;
            $ag_profit = 0;
            $sql = 'SELECT SUM(wager_amount) AS bet_sum,SUM(bonus_amount) AS bonus_sum,SUM(wager_amount - bonus_amount ) AS profit_sum FROM monthly_staff_external WHERE interface_key=:interface_key AND monthly=:monthly' ;
            $category_key = ['fg', 'ag', 'lb', 'ky'];
            foreach ($category_key as $row) {
                $param = [':interface_key' => $row,':monthly' => $monthly];
                foreach ($mysql->query($sql, $param) as $item) {
                    if($row == 'fg'){
                        $fg_bet += $item['bet_sum'];
                        $fg_bonus += $item['bonus_sum'];
                        $fg_profit += $item['profit_sum'];
                    }elseif($row == 'ky'){
                        $ky_bet += $item['bet_sum'];
                        $ky_bonus += $item['bonus_sum'];
                        $ky_profit += $item['profit_sum'];
                    }elseif($row=='lb'){
                        $lb_bet += $item['bet_sum'];
                        $lb_bonus += $item['bonus_sum'];
                        $lb_profit += $item['profit_sum'];
                    }elseif($row == 'ag'){
                        $ag_bet += $item['bet_sum'];
                        $ag_bonus += $item['bonus_sum'];
                        $ag_profit += $item['profit_sum'];
                    }

                }
            }
            $statisticData['game_bet_amount'] = $this->intercept_num($fg_bet);
            $statisticData['game_bonus_amount'] = $this->intercept_num($fg_bonus);
            $statisticData['game_profit_amount'] = $this->intercept_num($fg_profit);
            $statisticData['cards_bet_amount'] = $this->intercept_num($ky_bet);
            $statisticData['cards_bonus_amount'] = $this->intercept_num($ky_bonus);
            $statisticData['cards_profit_amount'] = $this->intercept_num($ky_profit);
            $statisticData['sports_bet_amount'] = $this->intercept_num($lb_bet);
            $statisticData['sports_bonus_amount'] = $this->intercept_num($lb_bonus);
            $statisticData['sports_profit_amount'] = $this->intercept_num($lb_profit);
            $statisticData['video_bet_amount'] = $this->intercept_num($ag_bet);
            $statisticData['video_bonus_amount'] = $this->intercept_num($ag_bonus);
            $statisticData['video_profit_amount'] = $this->intercept_num($ag_profit);
            //本月彩票总佣金

            $statisticData['lottery_brokerage'] = 0;


            $statisticData['video_brokerage'] = 0;
            $statisticData['game_brokerage'] = 0;
            $statisticData['sports_brokerage'] = 0;
            $statisticData['cards_brokerage'] = 0;
            $statisticData['total_brokerage'] = 0;
            ;
            $statisticData['tax_rent'] = 0;
            $statisticData['monthly_pay'] = 0;
        }else{
            $sql = "select * from  monthly_tax where monthly=:monthly";
            $param = [":monthly" => $monthly];
            $statisticData = [];
            foreach ($mysql->query($sql,$param) as $row){
                $statisticData["bet_amount"] = $this->intercept_num($row["wager_lottery"]);
                $statisticData["bonus_amount"] = $this->intercept_num($row["bonus_lottery"]);
                $statisticData["cards_bet_amount"] = $this->intercept_num($row["wager_cards"]);
                $statisticData["cards_bonus_amount"] = $this->intercept_num($row["bonus_cards"]);
                $statisticData["cards_brokerage"] = $this->intercept_num($row["tax_cards"]);
                $statisticData["cards_profit_amount"] = $this->intercept_num($row["profit_cards"]);
                $statisticData["game_bet_amount"] = $this->intercept_num($row["wager_game"]);
                $statisticData["game_bonus_amount"] = $this->intercept_num($row["bonus_game"]);
                $statisticData["game_brokerage"] = $this->intercept_num($row["tax_game"]);
                $statisticData["game_profit_amount"] = $this->intercept_num($row["profit_game"]);
                $statisticData["lottery_brokerage"] = $this->intercept_num($row["tax_lottery"]);
                $statisticData["monthly_pay"] = $this->intercept_num($row["tax_rent"]+$row["tax_total"]);
                $statisticData["profit_amount"] = $this->intercept_num($row["profit_lottery"]);
                $statisticData["sports_bet_amount"] = $this->intercept_num($row["wager_sports"]);
                $statisticData["sports_bonus_amount"] = $this->intercept_num($row["bonus_sports"]);
                $statisticData["sports_brokerage"] = $this->intercept_num($row["tax_sports"]);
                $statisticData["sports_profit_amount"] = $this->intercept_num($row["profit_sports"]);
                $statisticData["tax_rent"] = $this->intercept_num($row["tax_rent"]);
                $statisticData["total_brokerage"] = $this->intercept_num($row["tax_total"]);
                $statisticData["video_bet_amount"] = $this->intercept_num($row["wager_video"]);
                $statisticData["video_bonus_amount"] = $this->intercept_num($row["bonus_video"]);
                $statisticData["video_brokerage"] = $this->intercept_num($row["tax_video"]);
                $statisticData["video_profit_amount"] = $this->intercept_num($row["profit_video"]);
            }
        }


        $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => $statisticData]);
    }

}
