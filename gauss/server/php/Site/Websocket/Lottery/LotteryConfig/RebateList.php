<?php

/**
 * Class RebateList
 * @description 彩票返点列表类
 * @author Rose
 * @date 2018-12-07
 * @link Websocket: Lottery/LotteryConfig/RebateList
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-08
 */

namespace Site\Websocket\Lottery\LotteryConfig;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

class RebateList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('game_lottery_rebate', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);
            return;
        }

        $mysql = $config->data_staff;
        $list = [];
        $sql = 'SELECT game_key,rebate_max FROM lottery_game WHERE acceptable = 1';
        foreach ($mysql->query($sql) as $row) {
            $list[] = $row;
        }
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['game_key'] = $val['game_key'];
                $list[$key]['game_name'] = $context->getInfo($val['game_key']);
                $list[$key]['rebate_max'] = $val['rebate_max'];
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $list,
        ]);

        //记录日志
        $staff_mysql = $config->data_staff;
        $operate_sql = 'INSERT INTO operate_log (staff_id,operate_key,detail,client_ip) VALUES (:staff_id,:operate_key,:detail,:client_ip)';
        $operate_param = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':operate_key' => 'game_lottery_rebate',
            ':detail' => '查看彩票返点列表',
            ':client_ip' => ip2long($context->getClientAddr()),
        ];
        $staff_mysql->execute($operate_sql, $operate_param);
    }
}