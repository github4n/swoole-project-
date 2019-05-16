<?php

namespace Site\Websocket\Member\BetRecord;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;
use Lib\Calender;

/**
 * BetRecordDetail class.
 *
 * @description   会员管理-投注记录-追号详情
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/BetRecord/BetRecordDetail {"user_key":"user003","bet_serial":"190109164939000002","play_key":"six_hit33"}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class BetRecordDetail extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $cache = $config->cache_site;
        $userKey = !empty($data['user_key']) ? $data['user_key'] : '';
        $bet_serial = !empty($data['bet_serial']) ? $data['bet_serial'] : '';
        $play_key = !empty($data['play_key']) ? $data['play_key'] : '';
        if (empty($userKey)) {
            $context->reply(['status' => 201, 'msg' => '用户名为空']);

            return;
        }
        if (empty($bet_serial)) {
            $context->reply(['status' => 201, 'msg' => '单号为空']);

            return;
        }
        if (empty($play_key)) {
            $context->reply(['status' => 201, 'msg' => '玩法为空']);

            return;
        }

        $mysql = $config->data_user;
        $sql = 'SELECT deal_key FROM user_info_intact WHERE user_key=:user_key';
        $param = [':user_key' => $userKey];
        $dealKey = '';
        foreach ($mysql->query($sql, $param) as $row) {
            $dealKey = $row['deal_key'];
        }
        $mysql = $config->__get('data_'.$dealKey);
        $list_sql = 'SELECT period,SUM(bet_launch) AS bet,SUM(bonus) AS bonus,SUM(rebate) AS rebate,SUM(revert) AS revert FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_key=:user_key AND play_key=:play_key GROUP BY period';
        $list_params = [
            ':bet_serial' => $bet_serial,
            ':user_key' => $userKey,
            ':play_key' => $play_key,
        ];
        $periodList = [];
        foreach ($mysql->query($list_sql, $list_params) as $row) {
            $periodList[] = $row;
        }
        $totalList = [
            'bet_amount' => 0,
            'bonus_amount' => 0,
            'rebate_amount' => 0,
            'revert_amount' => 0,
            'profit_amount' => 0,
        ];
        foreach ($periodList as $k => $v) {
            $periodList[$k]['profit'] = floor(($v['bet'] - $v['bonus'] + $v['rebate'] + $v['revert']) * 100) * 0.01;
            $totalList['bet_amount'] += $v['bet'];
            $totalList['bonus_amount'] += $v['bonus'];
            $totalList['rebate_amount'] += $v['rebate'];
            $totalList['revert_amount'] += $v['revert'];
            $totalList['profit_amount'] += $periodList[$k]['profit'];
        }

        //查询投注号码
        $publicMysql = $config->data_public;
        $sql = 'SELECT DISTINCT number,game_key,settle_time FROM bet_unit_intact WHERE bet_serial=:bet_serial AND user_key=:user_key AND play_key=:play_key';
        $number = [];
        $gameKey = '';
        foreach ($mysql->query($sql, $list_params) as $row) {
            $number[] = json_decode($row['number']);
            $gameKey = $row['game_key'];
        }

        $sql = 'SELECT win_name FROM lottery_game_win_intact WHERE game_key=:game_key AND play_key=:play_key AND win_key=:win_key';
        $betNum = [];
        $zodicList = Calender::getZodiacList(time());

        foreach ($number as $value) {
            foreach ($value as $v) {
                if (in_array($play_key, ['six_szodiac', 'six_szodiac6hit', 'six_szodiac6miss', 'six_zodiac1hit', 'six_zodiac2hit', 'six_zodiac2miss', 'six_zodiac3hit', 'six_zodiac3miss', 'six_zodiac4hit', 'six_zodiac4miss'])) {
                    $n = intval(substr($v, strripos($v, '_') + 1));
                    $sx = $zodicList[$n];
                    switch ($sx) {
                case 'rat':
                $winName = '鼠';
                break;
                case 'ox':
                $winName = '牛';
                break;
                case 'tiger':
                $winName = '虎';
                break;
                case 'rabbit':
                $winName = '兔';
                break;
                case 'dragon':
                $winName = '龙';
                break;
                case 'snake':
                $winName = '蛇';
                break;
                case 'horse':
                $winName = '马';
                break;
                case 'sheep':
                $winName = '羊';
                break;
                case 'monkey':
                $winName = '猴';
                break;
                case 'chicken':
                $winName = '鸡';
                break;
                case 'dog':
                $winName = '狗';
                break;
                case 'pig':
                $winName = '猪';
                break;
                default:
                $winName = '';
                break;
            }
                } else {
                    $winName = $cache->hget('WinList', $play_key.'-'.$v);
                }

                $bet_num[] = $winName;
            }
        }
        // $bet_num = array_unique($betNum);
        //查询play_name
        $bet_num = array_unique($bet_num);
        $sql = 'SELECT play_name FROM lottery_game_play_intact WHERE game_key=:game_key AND play_key=:play_key';
        $param = [
            ':game_key' => $gameKey,
            ':play_key' => $play_key,
        ];
        $play_name = '';
        foreach ($publicMysql->query($sql, $param) as $row) {
            $play_name = $row['play_name'];
        }
        $totalData['play_name'] = $play_name;
        $totalData['bet_number'] = array_values($bet_num);

        $context->reply(['status' => 200, 'msg' => '获取成功', 'data' => [
                'play_name' => $play_name,
                'bet_number' => array_values($bet_num),
                'list' => $periodList,
                'total' => $totalList,
    ]]);
    }
}
