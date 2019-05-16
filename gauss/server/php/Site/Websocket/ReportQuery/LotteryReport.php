<?php
/**
 * LotteryReport.php
 *
 * @description   报表查询-彩票报表
 * @Author  Luis
 * @date  2019-04-07
 * @links  ReportQuery/LotteryReport {"date":"yesterday"}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
namespace Site\Websocket\ReportQuery;
use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class LotteryReport extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $staffId = $context->getInfo('StaffId');
        $staffGrade = $context->getInfo('StaffGrade');
        //检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array("report_lottery",$auth)) {
            $context->reply(["status"=>202,"msg"=>"你还没有操作权限"]);
            return;
        }
        switch ($staffGrade)
        {
            case 0:
                $id = null;
                break;
            case 1:
                $id = ' AND major_id='.$staffId.' ';
                break;
            case 2:
                $id = ' AND minor_id='.$staffId.' ';
                break;
            case 3:
                $id = ' AND agent_id='.$staffId.' ';
                break;
        }
        $lotterysql = "select model_key,game_key from lottery_game";
        $mysql = $config -> data_staff;
        $reportmysql = $config -> data_report;
        $cache = $config->cache_site;
        $lottery_list = iterator_to_array($mysql->query($lotterysql));
        $allList = [];
        foreach ($lottery_list as $k => $v){
            if ($data['date'] == "today"){
                $day = intval(date('Ymd',strtotime("today")));
                $sql = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM daily_staff_lottery WHERE daily=:daily ".$id;
                $sqls = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM daily_staff_lottery WHERE daily=:daily AND game_key = :game_key".$id;
                $param = [":daily" => $day];
                $params = [":daily" => $day,":game_key"=>$v["game_key"]];
            } else if ($data['date'] == "yesterday"){
                $day = intval(date('Ymd',strtotime("yesterday")));
                $sql = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM daily_staff_lottery WHERE daily=:daily".$id;
                $sqls = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM daily_staff_lottery WHERE daily=:daily AND game_key = :game_key".$id;
                $param = [":daily" => $day];
                $params = [":daily" => $day,":game_key"=>$v["game_key"]];
            } else if ($data['date'] == "thisweek"){
                $day = intval(date('oW',strtotime("today")));
                $sql = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM weekly_staff_lottery WHERE weekly=:weekly".$id;
                $sqls = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM weekly_staff_lottery WHERE weekly=:weekly AND game_key = :game_key".$id;
                $param = [":weekly" => $day];
                $params = [":weekly" => $day,":game_key"=>$v["game_key"]];
            } else if ($data['date'] == "lastweek"){
                $day = intval(date("oW",strtotime("-2 week Monday"))) ;
                $sql = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM weekly_staff_lottery WHERE weekly=:daily".$id;
                $sqls = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM weekly_staff_lottery WHERE weekly=:daily AND game_key = :game_key".$id;
                $param = [":weekly" => $day];
                $params = [":weekly" => $day,":game_key"=>$v["game_key"]];
            } else if ($data['date'] == "thismonth"){
                $day = intval(date("Ym",strtotime("today"))) ;
                $sql = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM monthly_staff_lottery WHERE monthly=:monthly".$id;
                $sqls = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM monthly_staff_lottery WHERE monthly=:monthly AND game_key = :game_key".$id;
                $param = [":monthly" => $day];
                $params = [":monthly" => $day,":game_key"=>$v["game_key"]];
            } else if ($data['date'] == "lastmonth"){
                $day = intval(date("Ym",strtotime('last month')));
                $sql = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM monthly_staff_lottery WHERE monthly=:monthly".$id;
                $sqls = "SELECT sum(bet_count) as bet_count,sum(bet_amount) as bet_amount,sum(bonus_amount) as bonus_amount,SUM(subsidy_amount) AS subsidy_amount,sum(bet_amount-bonus_amount-subsidy_amount) as profit_amount  FROM monthly_staff_lottery WHERE monthly=:monthly AND game_key = :game_key".$id;
                $param = [":monthly" => $day];
                $params = [":monthly" => $day,":game_key"=>$v["game_key"]];
            } else {
                $context->reply(["status"=>204,"msg"=>"时间格式不正确"]);
                return;
            }

            $list = [];
            foreach($reportmysql -> query($sql,$param) as $rows){
                $list["bet_amount"] = empty($rows["bet_amount"]) ? 0 : $rows["bet_amount"];
                $list["bet_count"] = empty($rows["bet_count"]) ? 0 : $rows["bet_count"];
                $list["subsidy_amount"] = empty($rows["subsidy_amount"]) ? 0 : $rows["subsidy_amount"];
                $list["bonus_amount"] = empty($rows["bonus_amount"]) ? 0 : $rows["bonus_amount"];
                $list["profit_amount"] = empty($rows["profit_amount"]) ? 0 : $rows["profit_amount"];
            }
            $lotteryInfo = [];
            foreach($reportmysql -> query($sqls,$params) as $item){
                $lotteryInfo = $item;
            }
            if (!empty($lotteryInfo)){
                $gameList["game_key"] = $v["game_key"];
                $gameList["game_name"] = $cache->hget("AllGame",$v["game_key"]);
                $gameList["bet_amount"] = $lotteryInfo["bet_amount"];
                $gameList["bet_count"] = $lotteryInfo["bet_count"];
                $gameList["bonus_amount"] = $lotteryInfo["bonus_amount"];
                $gameList["profit_amount"] = $lotteryInfo["profit_amount"];
                $gameList["bet_rate"] = $lotteryInfo["bet_amount"]/ $list["bet_amount"];
                $gameList["bet_count_rate"] = $lotteryInfo["bet_count"]/ $list["bet_count"];
                if($v["model_key"] == "dice"){
                    $allList[$v["model_key"]]["bet_amount"] += $lotteryInfo['bet_amount'];
                    $allList[$v["model_key"]]["bet_count"] += $lotteryInfo['bet_count'];
                    $allList[$v["model_key"]]["bonus_amount"] += $lotteryInfo['bonus_amount'];
                    $allList[$v["model_key"]]["profit_amount"] += $lotteryInfo['profit_amount'];
                    $allList[$v["model_key"]]["bet_rate"] = $allList[$v["model_key"]]["bet_amount"]/$list["bet_amount"];
                    $allList[$v["model_key"]]["bet_count_rate"] = $allList[$v["model_key"]]["bet_count"]/$list["bet_count"];
                }
                if($v["model_key"] == "eleven"){
                    $allList[$v["model_key"]]["bet_amount"] += $lotteryInfo['bet_amount'];
                    $allList[$v["model_key"]]["bet_count"] += $lotteryInfo['bet_count'];
                    $allList[$v["model_key"]]["bonus_amount"] += $lotteryInfo['bonus_amount'];
                    $allList[$v["model_key"]]["profit_amount"] += $lotteryInfo['profit_amount'];
                    $allList[$v["model_key"]]["bet_rate"] = $allList[$v["model_key"]]["bet_amount"]/$list["bet_amount"];
                    $allList[$v["model_key"]]["bet_count_rate"] = $allList[$v["model_key"]]["bet_count"]/$list["bet_count"];
                }
                if($v["model_key"] == "ladder"){
                    $allList[$v["model_key"]]["bet_amount"] += $lotteryInfo['bet_amount'];
                    $allList[$v["model_key"]]["bet_count"] += $lotteryInfo['bet_count'];
                    $allList[$v["model_key"]]["bonus_amount"] += $lotteryInfo['bonus_amount'];
                    $allList[$v["model_key"]]["profit_amount"] += $lotteryInfo['profit_amount'];
                    $allList[$v["model_key"]]["bet_rate"] = $allList[$v["model_key"]]["bet_amount"]/$list["bet_amount"];
                    $allList[$v["model_key"]]["bet_count_rate"] = $allList[$v["model_key"]]["bet_count"]/$list["bet_count"];
                }
                if($v["model_key"] == "lucky"){
                    $allList[$v["model_key"]]["bet_amount"] += $lotteryInfo['bet_amount'];
                    $allList[$v["model_key"]]["bet_count"] += $lotteryInfo['bet_count'];
                    $allList[$v["model_key"]]["bonus_amount"] += $lotteryInfo['bonus_amount'];
                    $allList[$v["model_key"]]["profit_amount"] += $lotteryInfo['profit_amount'];
                    $allList[$v["model_key"]]["bet_rate"] = $allList[$v["model_key"]]["bet_amount"]/$list["bet_amount"];
                    $allList[$v["model_key"]]["bet_count_rate"] = $allList[$v["model_key"]]["bet_count"]/$list["bet_count"];
                }
                if($v["model_key"] == "racer"){
                    $allList[$v["model_key"]]["bet_amount"] += $lotteryInfo['bet_amount'];
                    $allList[$v["model_key"]]["bet_count"] += $lotteryInfo['bet_count'];
                    $allList[$v["model_key"]]["bonus_amount"] += $lotteryInfo['bonus_amount'];
                    $allList[$v["model_key"]]["profit_amount"] += $lotteryInfo['profit_amount'];
                    $allList[$v["model_key"]]["bet_rate"] = $allList[$v["model_key"]]["bet_amount"]/$list["bet_amount"];
                    $allList[$v["model_key"]]["bet_count_rate"] = $allList[$v["model_key"]]["bet_count"]/$list["bet_count"];
                }
                if($v["model_key"] == "six"){
                    $allList[$v["model_key"]]["bet_amount"] += empty($lotteryInfo['bet_amount']) ? 0 : $lotteryInfo['bet_amount'];
                    $allList[$v["model_key"]]["bet_count"] += empty($lotteryInfo['bet_count']) ? 0 : $lotteryInfo['bet_count'];
                    $allList[$v["model_key"]]["bonus_amount"] += empty($lotteryInfo['bonus_amount']) ? 0 : $lotteryInfo['bonus_amount'];
                    $allList[$v["model_key"]]["profit_amount"] += empty($lotteryInfo['profit_amount']) ? 0 : $lotteryInfo['profit_amount'];
                    $allList[$v["model_key"]]["bet_rate"] = $allList[$v["model_key"]]["bet_amount"]/$list["bet_amount"];
                    $allList[$v["model_key"]]["bet_count_rate"] = $allList[$v["model_key"]]["bet_count"]/$list["bet_count"];
                }
                if($v["model_key"] == "tiktok"){
                    $allList[$v["model_key"]]["bet_amount"] += $lotteryInfo['bet_amount'];
                    $allList[$v["model_key"]]["bet_count"] += $lotteryInfo['bet_count'];
                    $allList[$v["model_key"]]["bonus_amount"] += $lotteryInfo['bonus_amount'];
                    $allList[$v["model_key"]]["profit_amount"] += $lotteryInfo['profit_amount'];
                    $allList[$v["model_key"]]["bet_rate"] = $allList[$v["model_key"]]["bet_amount"]/$list["bet_amount"];
                    $allList[$v["model_key"]]["bet_count_rate"] = $allList[$v["model_key"]]["bet_count"]/$list["bet_count"];

                }
            }else{
                $gameList["game_key"] = $v["game_key"];
                $gameList["game_name"] = $cache->hget("AllGame",$v["game_key"]);
                $gameList["bet_amount"] = "0";
                $gameList["bet_count"] = "0";
                $gameList["bonus_amount"] = "0";
                $gameList["profit_amount"] = "0";
                $gameList["bet_rate"] = "0";
                $gameList["bet_count_rate"] = "0";
            }
            $allList[$v["model_key"]]["model_name"] = $cache->hget("Model",$v["model_key"]);;
            $allList[$v["model_key"]]["bet_amount"] = empty($allList[$v["model_key"]]["bet_amount"]) ? 0 : $allList[$v["model_key"]]["bet_amount"] ;
            $allList[$v["model_key"]]["bet_count"] = empty($allList[$v["model_key"]]["bet_count"]) ? 0 : $allList[$v["model_key"]]["bet_count"] ;
            $allList[$v["model_key"]]["bonus_amount"] = empty($allList[$v["model_key"]]["bonus_amount"]) ? 0 : $allList[$v["model_key"]]["bonus_amount"] ;
            $allList[$v["model_key"]]["profit_amount"] = empty($allList[$v["model_key"]]["profit_amount"]) ? 0 : $allList[$v["model_key"]]["profit_amount"] ;
            $allList[$v["model_key"]]["bet_rate"] = empty($allList[$v["model_key"]]["bet_rate"]) ? 0 : $allList[$v["model_key"]]["bet_rate"] ;
            $allList[$v["model_key"]]["bet_count_rate"] = empty($allList[$v["model_key"]]["bet_count_rate"]) ? 0 : $allList[$v["model_key"]]["bet_count_rate"] ;
            $allList[$v["model_key"]]["list"][] = $gameList;

        }
        $context->reply([
            "status"=>200,
            "msg"=>"获取成功",
            "today"=>$list,
            "list"=>$allList,
        ]);
    }
}