<?php

/**
 * Class ResultList
 * $description 开奖结果列表类
 * @author Parker
 * @date 2019-02-15
 * @link Websocket: Lottery/LotteryResult/ResultList {"game_key":"dice_fast","period":"20190513429"}
 * @param string $game_key 彩种Key
 * @param string $period 期号
 * @modifyAuthor Kayden
 * @modifyDate 2019-04-12
 */

namespace Site\Websocket\Lottery\LotteryResult;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

class ResultList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        // 判断权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('game_number', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);
            return;
        }

        // 接收参数
        $data = $context->getData();
        $list = array();

        // 搜索条件
        $game_key = isset($data['game_key']) ? $data['game_key'] : '';
        $period = isset($data['period']) ? $data['period'] : '';
        $paramSearch = [];
        if (!empty($game_key)) {
            if (empty($context->getInfo($game_key))) {
                $context->reply(['status' => 202, 'msg' => '彩票名称错误', 'data' => $game_key]);
                return;
            }
            $paramSearch[':game_key'] = $game_key;
            $game_key = ' AND game_key = :game_key';
        } else {
            $game_key = '';
        }
        if (!empty($period)) {
            $paramSearch[':period'] = $period;
            $period = ' AND period = :period';
        } else {
            $period = '';
        }

        //查询彩票列表
        $mysqlReport = $config->data_report;
        $public_mysql = $config->data_public;
        $lottery_list = [];
        $game_list = [];
        $model_list = json_decode($context->getInfo('ModelList'), true);
        $game_sql = 'SELECT game_key FROM lottery_game WHERE  model_key=:model_key';
        foreach ($model_list as $k => $v) {
            $game_list[$k]['model_name'] = $v['model_name'];
            $game_param = [':model_key' => $v['model_key']];
            foreach ($public_mysql->query($game_sql, $game_param) as $value) {
                $lottery_list[] = [
                    'game_key' => $value['game_key'],
                    'game_name' => $context->getInfo($value['game_key']),
                ];
            }
            $game_list[$k]['game_list'] = $lottery_list;
            unset($lottery_list);
        }
        $ladder_games = [];
        $ladder_game_sql = "select game_key from lottery_game_intact where model_key='ladder'";
        $subsidy_game_data = iterator_to_array($public_mysql->query($ladder_game_sql));
        foreach ($subsidy_game_data as $game) {
            $ladder_games[] = $game['game_key'];
        }
        $lists = array();
        $sql = 'Select * From `lottery_number_intact` Where 1 ' . $game_key . $period . ' Order By `open_time` Desc Limit 1000';

        try {
            $list = [];
            $lists = iterator_to_array($public_mysql->query($sql, $paramSearch));
            if (!empty($lists)) {
                // 游戏名称，开盘时间，封盘时间
                $redis = $config->cache_site;
                $z = 0;
                foreach ($lists as $val) {
                    $list[$z]['game_name'] = $redis->hget('LotteryName', $val['game_key']);
                    $list[$z]['period'] = $val['period'];
                    $list[$z]['open_time'] = date('Y-m-d H:i:s', $val['open_time']);
                    $list[$z]['start_time'] = date('Y-m-d H:i:s', $val['start_time']);
                    $list[$z]['stop_time'] = date('Y-m-d H:i:s', $val['stop_time']);
                    $normal = '';
                    if (in_array($val['game_key'], $ladder_games)) {
                        $normal = $val['normal1'] == 1 ? '左' : '右';
                        $normal .= ' '.$val['special1'];
                        if (($val['normal1'] + $val['special1']) % 2 == 0) {
                            $normal .= ' '.'双';
                        } else {
                            $normal .= ' '.'单';
                        }
                    } else {
                        for ($i = 1; $i <= 12; ++$i) {
                            if ($val['normal'.$i] != -1) {
                                $normal .= $val['normal'.$i].',';
                            }
                        }
                        for ($j = 1; $j <= 2; ++$j) {
                            if ($val['special'.$j] != -1) {
                                $normal .= $val['special'.$j];
                            }
                        }
                    }

                    $list[$z]['num'] = rtrim($normal, ',');
                    $z++;
                }
            }
            $context->reply([
                'status' => 200,
                'msg' => '数据获取成功',
                'game_list' => $game_list,
                'list' => $list
            ]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '列表获取失败']);
            throw new \PDOException('Sql Run Error '.$e);
        }
    }
}
