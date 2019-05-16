<?php

namespace Site\Websocket\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * StockholderBet class.
 *
 * @description   大股东投注额
 * @Author  blake
 * @date  2019-05-08
 * @links  Index/StockholderBet
 * 参数：time(yesterday:昨日,week:本周,mounth:本月)
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class StockholderBet extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        $mysqlReport = $config->data_report;
        if ($StaffGrade != 0) {  //站长
            $context->reply(['status' => 203, 'msg' => '当前登录账号没有访问权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('home_topmajor', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $MonthFirstDay = date('Y/m/01', strtotime('-1 month'));
        $MonthLastDay = date('Y/m/t', strtotime('-1 month'));
        $Month = date('Ym', strtotime($MonthFirstDay));
        $sql = 'SELECT sum(bet_amount) as bet,sum(bonus_amount) as bonus,sum(profit_amount) as profit,major_name,major_id ,group_concat(agent_id) as agent_list FROM monthly_user_lottery WHERE monthly=:monthly GROUP BY major_id,major_name';
        $param = [':monthly' => $Month];
        $major_list = iterator_to_array($mysqlReport->query($sql, $param));
        $majorList = [];
        if (!empty($major_list)) {
            foreach ($major_list as $key => $val) {
                $majorList[$key]['major_name'] = $val['major_name'];
                $majorList[$key]['system_name'] = '大股东';
                $majorList[$key]['bet'] = $val['bet'];
                $majorList[$key]['bonus'] = $val['bonus'];
                $majorList[$key]['profit'] = $val['profit'];
                $majorList[$key]['count_cycle'] = $MonthFirstDay.' 00:00:00--'.$MonthLastDay.' 23:59:59';
                $userSql = 'SELECT count(user_id) as user_id FROM monthly_user_lottery WHERE agent_id in :agent_list AND monthly=:monthly';
                $userParam = [':agent_list' => $val['agent_list'], ':monthly' => $Month];
                $total_user = iterator_to_array($mysqlReport($userSql, $userParam));
                $majorList[$key]['count_user'] = $total_user[0]['user_id'];
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $majorList,
        ]);
        //记录日志
        $mysql = $config->data_staff;
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'home_topmajor',
            ':detail' => '查看大股东投注额',
        ];
        $mysql->execute($sql, $params);
    }
}
