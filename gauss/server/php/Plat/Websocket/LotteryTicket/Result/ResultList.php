<?php

namespace Plat\Websocket\LotteryTicket\Result;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * ResultList class.
 *
 * @description   开奖结果列表
 * @Author  avery
 * @date  2019-04-18
 * @links  LotteryTicket\Result\ResultList {"game_key":"tiktok_cq","period":"20181226066"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class ResultList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'), true);
        if (!in_array('lottery_open', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $param = [];
        $list = array();
        $game_key = isset($data['game_key']) ? $data['game_key'] : '';
        $period = isset($data['period']) ? $data['period'] : '';
        $limit = ' LIMIT 1000 ';
        $game_key_s = '';
        if (!empty($game_key)) {
            if (empty($context->getInfo($game_key))) {
                $context->reply(['status' => 202, 'msg' => '彩票名称错误', 'data' => $game_key]);

                return;
            }
            $game_key_s = ' AND game_key =:game_key';
            $param[':game_key'] = $game_key;
        }
        $period_s = '';
        if (!empty($period)) {
            $period_s = ' AND period =:period';
            $param[':period'] = $period;
        }

        //查询彩票列表
        $public_mysql = $config->data_public;
        $lottery_list = [];
        $game_list = [];
        $model_list = json_decode($context->getInfo('ModelList'), true);
        $game_sql = 'SELECT game_key FROM lottery_game WHERE  model_key=:model_key';
        foreach ($model_list as $k => $v) {
            $game_list[$k]['model_name'] = $v['model_name'];
            $game_param = [':model_key' => $v['model_key']];
            foreach ($public_mysql->query($game_sql, $game_param) as  $value) {
                $lottery_list[] = [
                    'game_key' => $value['game_key'],
                    'game_name' => $context->getInfo($value['game_key']),
                ];
            }
            $game_list[$k]['game_list'] = $lottery_list;
            unset($lottery_list);
        }

        $lists = array();
        $sql = 'SELECT * FROM lottery_number_intact WHERE 1=1 '.$game_key_s.$period_s.' ORDER BY open_time DESC'.$limit;
        try {
            foreach ($public_mysql->query($sql, $param) as $rows) {
                $lists[] = $rows;
            }
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    $list[$key]['game_name'] = $val['game_name'];
                    $list[$key]['period'] = $val['period'];
                    $list[$key]['start_time'] = date('Y-m-d H:i:s', $val['start_time']);
                    $list[$key]['stop_time'] = date('Y-m-d H:i:s', $val['stop_time']);
                    $list[$key]['open_time'] = date('Y-m-d H:i:s', $val['open_time']);
                    $normal = '';
                    for ($i = 1; $i <= 12; ++$i) {
                        if ($val['normal'.$i] != -1) {
                            $normal .= $val['normal'.$i].',';
                        }
                    }
                    for ($j = 1; $j <= 2; ++$j) {
                        if ($val['special'.$j] != -1) {
                            $normal .= $val['special'.$j].',';
                        }
                    }
                    $list[$key]['num'] = $normal;
                }
            }
            $context->reply([
                'status' => 200,
                'msg' => '获取成功',
                'game_list' => $game_list,
                'list' => $list,
            ]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取列表失败']);
            throw new \PDOException('sql run error'.$e);
        }
    }
}
