<?php
/**
 * LotteryReportNew.php
 *
 * @description   报表查询-彩票报表
 * @Author  Luis
 * @date  2019-04-07
 * @links  ReportQuery/LotteryReportNew {"date":"yesterday"}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
namespace Site\Websocket\ReportQuery;
use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class LotteryReportNew extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $staffId = $context->getInfo('StaffId');
        $staffGrade = $context->getInfo('StaffGrade');
        $MasterId = $context->getInfo("MasterId");
        $mysql = $config->data_report;
        $mysqlStaff = $config->data_staff;
        $cache = $config->cache_site;
        if ($MasterId != 0) {
            $staffId =$MasterId;
        }
        //检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array("report_lottery",$auth)) {
            $context->reply(["status"=>202,"msg"=>"你还没有操作权限"]);
            return;
        }
        $date = isset($data['date']) ? $data['date'] : '';
        $start_time = empty($data['start_time'])?"":date('Ymd',strtotime($data['start_time']));
        $end_time = empty($data['end_time'])?"":date('Ymd',strtotime($data['end_time']));
        if (empty($date) && (empty($start_time) && empty($end_time)))
        {
            $date = 'today';
        }
        if(!empty($start_time) && !empty($end_time)){
            $date = 'others';
        }
        $allList = [];
        $agentParam = [];
        switch ($staffGrade)
        {
            case 0:
                $agent = "";
                break;
            case 1:
                $agent = " and major_id = :staffId ";
                $agentParam[':staffId'] = $staffId;
                break;
            case 2:
                $agent = " and minor_id = :staffId ";
                $agentParam[':staffId'] = $staffId;
                break;
            case 3:
                $agent = " and agent_id = :staffId ";
                $agentParam[':staffId'] = $staffId;
                break;
        }

        $game_sql = "select model_key,game_key from lottery_game";
        $allGame = iterator_to_array($mysqlStaff->query($game_sql));
        foreach ($allGame as $key=>$val){
            $allList[$val["model_key"]]["bet_amount"] = isset($allList[$val["model_key"]]["bet_amount"]) ? $allList[$val["model_key"]]["bet_amount"] : 0;
            $allList[$val["model_key"]]["bet_count"] = isset($allList[$val["model_key"]]["bet_count"]) ? $allList[$val["model_key"]]["bet_count"] : 0;
            $allList[$val["model_key"]]["bonus_amount"] = isset($allList[$val["model_key"]]["bonus_amount"]) ? $allList[$val["model_key"]]["bonus_amount"] : 0;
            $allList[$val["model_key"]]["profit_amount"] = isset($allList[$val["model_key"]]["profit_amount"]) ? $allList[$val["model_key"]]["profit_amount"] : 0;
            $allList[$val["model_key"]]["bet_rate"] = isset($allList[$val["model_key"]]["bet_rate"]) ? $allList[$val["model_key"]]["bet_rate"] : 0;
            $allList[$val["model_key"]]["bet_count_rate"] = isset($allList[$val["model_key"]]["bet_count_rate"]) ? $allList[$val["model_key"]]["bet_count_rate"] : 0;
            switch ($date){
                case "yesterday":
                    $time = intval(date("Ymd",strtotime("yesterday")));
                    $sql = "select  sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount ".
                        " from daily_staff_lottery where daily= :time ".$agent;
                    $agentParam[':time'] = $time;
                    foreach ($mysql->query($sql, $agentParam) as $row){
                        $totalData = $row;
                    }
                    $sql =  "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount,".
                        "game_key,model_key ".
                        "from daily_staff_lottery where  daily=:time ".$agent." and game_key=:game_key group by game_key,model_key";
                    $gameData = [];
                    $agentParam[':game_key'] = $val["game_key"];
                    foreach ($mysql->query($sql,$agentParam) as $rows){
                        $gameData = $rows;
                    }
                    unset($agentParam[':game_key']);
                    break;
                case "thisWeek":
                    $time = intval(date("oW",strtotime("today")));
                    $sql = "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount -rebate_amount) as profit_amount ".
                        "from weekly_staff_lottery where  weekly= :time".$agent;
                    $agentParam[':time'] = $time;
                    foreach ($mysql->query($sql,$agentParam) as $row){
                        $totalData = $row;
                    }

                    $sql =  "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount,".
                        "game_key,model_key ".
                        "from weekly_staff_lottery where  weekly= :time ".$agent." and game_key=:game_key group by game_key,model_key";
                    $gameData = [];
                    $agentParam[':game_key'] = $val["game_key"];
                    foreach ($mysql->query($sql,$agentParam) as $rows){
                        $gameData = $rows;
                    }
                    unset($agentParam[':game_key']);
                    break;
                case "lastWeek":
                    $time = intval(date("oW",strtotime("-1 week")));
                    $sql = "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount ".
                        "from weekly_staff_lottery where  weekly= :time".$agent;
                    $agentParam[':time'] = $time;
                    foreach ($mysql->query($sql,$agentParam) as $row){
                        $totalData = $row;
                    }

                    $sql =  "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount,".
                        "game_key,model_key ".
                        "from weekly_staff_lottery where  weekly= :time ".$agent." and game_key=:game_key group by game_key,model_key";
                    $gameData = [];
                    $agentParam[':game_key'] = $val["game_key"];
                    foreach ($mysql->query($sql,$agentParam) as $rows){
                        $gameData = $rows;
                    }
                    unset($agentParam[':game_key']);
                    break;
                case "thisMonth":
                    $time = intval(date("Ym",strtotime("today")));
                    $sql = "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount ".
                        "from monthly_staff_lottery where  monthly= :time".$agent;
                    $agentParam[':time'] = $time;
                    foreach ($mysql->query($sql,$agentParam) as $row){
                        $totalData = $row;
                    }

                    $sql =  "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount,".
                        "game_key,model_key ".
                        "from monthly_staff_lottery where  monthly= :time ".$agent." and game_key=:game_key group by game_key,model_key";
                    $gameData = [];
                    $agentParam[':game_key'] = $val["game_key"];
                    foreach ($mysql->query($sql,$agentParam) as $rows){
                        $gameData = $rows;
                    }
                    unset($agentParam[':game_key']);
                    break;
                case "lastMonth":
                    $time = intval(date("Ym",strtotime("-1 month")));
                    $sql = "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount ".
                        "from monthly_staff_lottery where  monthly= :time".$agent;
                    $agentParam[':time'] = $time;
                    foreach ($mysql->query($sql,$agentParam) as $row){
                        $totalData = $row;
                    }
                    $sql =  "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount,".
                        "game_key,model_key ".
                        "from monthly_staff_lottery where  monthly= :time ".$agent." and game_key=:game_key group by game_key,model_key";
                    $gameData = [];
                    $agentParam[':game_key'] = $val["game_key"];
                    foreach ($mysql->query($sql,$agentParam) as $rows){
                        $gameData = $rows;
                    }
                    unset($agentParam[':game_key']);
                    break;
                case "others":
//                $time = intval(date("Ymd",strtotime("yesterday")));
                    $sql = "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount ".
                        "from daily_staff_lottery where  daily between :start_time and :end_time".$agent;
                    $agentParam[':start_time'] = $start_time;
                    $agentParam[':end_time'] = $end_time;
                    foreach ($mysql->query($sql,$agentParam) as $row){
                        $totalData = $row;
                    }
                    $sql =  "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount,".
                        "game_key,model_key ".
                        "from daily_staff_lottery where  daily between :start_time and :end_time and game_key=:game_key ".$agent." group by game_key,model_key";
                    $agentParam[':start_time'] = $start_time;
                    $agentParam[':end_time'] = $end_time;
                    $agentParam[':game_key'] = $val["game_key"];
                    $gameData = [];
                    foreach ($mysql->query($sql,$agentParam) as $rows){
                        $gameData = $rows;
                    }
                    unset($agentParam[':game_key']);
                    break;
                default:
                    $time = intval(date("Ymd",strtotime("today")));
                    $sql = "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount ".
                        "from daily_staff_lottery where  daily= :time".$agent;
                    $agentParam[':time'] = $time;
                    foreach ($mysql->query($sql,$agentParam) as $row){
                        $totalData = $row;
                    }
                    $sql =  "select sum(wager_count) as bet_count,sum(wager_amount) as bet_amount,sum(bonus_amount) as bonus_amount,".
                        "sum(wager_amount - bonus_amount - rebate_amount) as profit_amount,".
                        "game_key,model_key ".
                        "from daily_staff_lottery where  daily=:time".$agent." and game_key=:game_key group by game_key,model_key";
                    $agentParam[':game_key'] = $val["game_key"];
                    $gameData = [];
                    foreach ($mysql->query($sql,$agentParam) as $rows){
                        $gameData = $rows;
                    }
                    unset($agentParam[':game_key']);
                    break;
            }
            if (!empty($gameData)){
                $gameList["game_key"] = $val["game_key"];
                $gameList["bet_amount"] = $this->intercept_num($gameData["bet_amount"]);
                $gameList["bet_count"] = $gameData["bet_count"];
                $gameList["bonus_amount"] = $this->intercept_num($gameData["bonus_amount"]);
                $gameList["profit_amount"] = $this->intercept_num($gameData["profit_amount"]);
                $gameList["bet_rate"] = sprintf('%.4f', substr($gameData["bet_amount"]/ $totalData["bet_amount"], 0, strpos($gameData["bet_amount"]/ $totalData["bet_amount"], '.') + 5));
                $gameList["bet_count_rate"] = sprintf('%.4f', substr($gameData["bet_count"]/ $totalData["bet_count"], 0, strpos($gameData["bet_count"]/ $totalData["bet_count"], '.') + 5));
                if($val["model_key"] == "dice"){
                    $allList[$val["model_key"]]["bet_amount"] += $this->intercept_num($gameData['bet_amount']);
                    $allList[$val["model_key"]]["bet_count"] += $gameData['bet_count'];
                    $allList[$val["model_key"]]["bonus_amount"] += $this->intercept_num($gameData['bonus_amount']);
                    $allList[$val["model_key"]]["profit_amount"] += $this->intercept_num($gameData['profit_amount']);
                    $allList[$val["model_key"]]["bet_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], 0, strpos($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], '.') + 5));
                    $allList[$val["model_key"]]["bet_count_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], 0, strpos($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], '.') + 5));
                }
                if($val["model_key"] == "eleven"){
                    $allList[$val["model_key"]]["bet_amount"] += $this->intercept_num($gameData['bet_amount']);
                    $allList[$val["model_key"]]["bet_count"] += $gameData['bet_count'];
                    $allList[$val["model_key"]]["bonus_amount"] += $this->intercept_num($gameData['bonus_amount']);
                    $allList[$val["model_key"]]["profit_amount"] += $this->intercept_num($gameData['profit_amount']);
                    $allList[$val["model_key"]]["bet_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], 0, strpos($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], '.') + 5));
                    $allList[$val["model_key"]]["bet_count_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], 0, strpos($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], '.') + 5));
                }
                if($val["model_key"] == "ladder"){
                    $allList[$val["model_key"]]["bet_amount"] += $this->intercept_num($gameData['bet_amount']);
                    $allList[$val["model_key"]]["bet_count"] += $gameData['bet_count'];
                    $allList[$val["model_key"]]["bonus_amount"] += $this->intercept_num($gameData['bonus_amount']);
                    $allList[$val["model_key"]]["profit_amount"] += $this->intercept_num($gameData['profit_amount']);
                    $allList[$val["model_key"]]["bet_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], 0, strpos($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], '.') + 5));
                    $allList[$val["model_key"]]["bet_count_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], 0, strpos($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], '.') + 5));
                }
                if($val["model_key"] == "lucky"){
                    $allList[$val["model_key"]]["bet_amount"] += $this->intercept_num($gameData['bet_amount']);
                    $allList[$val["model_key"]]["bet_count"] += $gameData['bet_count'];
                    $allList[$val["model_key"]]["bonus_amount"] += $this->intercept_num($gameData['bonus_amount']);
                    $allList[$val["model_key"]]["profit_amount"] += $this->intercept_num($gameData['profit_amount']);
                    $allList[$val["model_key"]]["bet_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], 0, strpos($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], '.') + 5));
                    $allList[$val["model_key"]]["bet_count_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], 0, strpos($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], '.') + 5));
                }
                if($val["model_key"] == "racer"){
                    $allList[$val["model_key"]]["bet_amount"] += $this->intercept_num($gameData['bet_amount']);
                    $allList[$val["model_key"]]["bet_count"] += $gameData['bet_count'];
                    $allList[$val["model_key"]]["bonus_amount"] += $this->intercept_num($gameData['bonus_amount']);
                    $allList[$val["model_key"]]["profit_amount"] += $this->intercept_num($gameData['profit_amount']);
                    $allList[$val["model_key"]]["bet_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], 0, strpos($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], '.') + 5));
                    $allList[$val["model_key"]]["bet_count_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], 0, strpos($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], '.') + 5));
                }
                if($val["model_key"] == "six"){
                    $allList[$val["model_key"]]["bet_amount"] += empty($gameData['bet_amount']) ? '0.00' : $this->intercept_num($gameData['bet_amount']);
                    $allList[$val["model_key"]]["bet_count"] += empty($gameData['bet_count']) ? 0 : $gameData['bet_count'];
                    $allList[$val["model_key"]]["bonus_amount"] += empty($gameData['bonus_amount']) ? '0.00' : $this->intercept_num($gameData['bonus_amount']);
                    $allList[$val["model_key"]]["profit_amount"] += empty($gameData['profit_amount']) ? '0.00' : $this->intercept_num($gameData['profit_amount']);
                    $allList[$val["model_key"]]["bet_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], 0, strpos($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], '.') + 5));
                    $allList[$val["model_key"]]["bet_count_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], 0, strpos($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], '.') + 5));
                }
                if($val["model_key"] == "tiktok"){
                    $allList[$val["model_key"]]["bet_amount"] += $this->intercept_num($gameData['bet_amount']);
                    $allList[$val["model_key"]]["bet_count"] += $gameData['bet_count'];
                    $allList[$val["model_key"]]["bonus_amount"] += $this->intercept_num($gameData['bonus_amount']);
                    $allList[$val["model_key"]]["profit_amount"] += $this->intercept_num($gameData['profit_amount']);
                    $allList[$val["model_key"]]["bet_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], 0, strpos($allList[$val["model_key"]]["bet_amount"]/$totalData["bet_amount"], '.') + 5));
                    $allList[$val["model_key"]]["bet_count_rate"] = sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], 0, strpos($allList[$val["model_key"]]["bet_count"]/$totalData["bet_count"], '.') + 5));

                }
            }else{
                $gameList["game_key"] = $val["game_key"];
                $gameList["bet_amount"] = '0.00';
                $gameList["bet_count"] = 0;
                $gameList["bonus_amount"] = '0.00';
                $gameList["profit_amount"] = '0.00';
                $gameList["bet_rate"] = 0;
                $gameList["bet_count_rate"] = 0;
            }
            $allList[$val["model_key"]]["model_key"] = $val["model_key"];
            $allList[$val["model_key"]]["bet_amount"] = empty($allList[$val["model_key"]]["bet_amount"]) ? '0.00' : $this->intercept_num($allList[$val["model_key"]]["bet_amount"]) ;
            $allList[$val["model_key"]]["bet_count"] = empty($allList[$val["model_key"]]["bet_count"]) ? 0 : $allList[$val["model_key"]]["bet_count"] ;
            $allList[$val["model_key"]]["bonus_amount"] = empty($allList[$val["model_key"]]["bonus_amount"]) ? '0.00' : $this->intercept_num($allList[$val["model_key"]]["bonus_amount"]) ;
            $allList[$val["model_key"]]["profit_amount"] = empty($allList[$val["model_key"]]["profit_amount"]) ? '0.00' : $this->intercept_num($allList[$val["model_key"]]["profit_amount"]) ;
            $allList[$val["model_key"]]["bet_rate"] = empty($allList[$val["model_key"]]["bet_rate"]) ? '0.00' : sprintf('%.4f', substr($allList[$val["model_key"]]["bet_rate"] , 0, strpos($allList[$val["model_key"]]["bet_rate"] , '.') + 5));
            $allList[$val["model_key"]]["bet_count_rate"] = empty($allList[$val["model_key"]]["bet_count_rate"]) ? '0.00' : sprintf('%.4f', substr($allList[$val["model_key"]]["bet_count_rate"] , 0, strpos($allList[$val["model_key"]]["bet_count_rate"] , '.') + 5));
            $allList[$val["model_key"]]["list"][] = $gameList;
        }
        $context->reply([
            "status"=>200,
            "msg"=>"获取数据成功",
            "data"=>[
                "bet_amount"=> empty($totalData["bet_amount"]) ? '0.00' : $this->intercept_num($totalData["bet_amount"]),
                "bet_count"=> empty($totalData["bet_count"]) ? 0 : $totalData["bet_count"],
                "bonus_amount"=> empty($totalData["bonus_amount"]) ? '0.00' : $this->intercept_num($totalData["bonus_amount"]),
                "profit_amount"=> empty($totalData["profit_amount"]) ? '0.00' : $this->intercept_num($totalData["profit_amount"]),
                "list"=>$allList,
            ]
        ]);
    }
}