<?php

namespace Plat\Websocket\LotteryTicket\LotteryRateSetting;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * PlayRateSettingSave class.
 *
 * @description   玩法赔率设置编辑
 * @Author  avery
 * @date  2019-04-23
 * @links  LotteryTicket/LotteryRateSetting/PlayRateSettingSave {"site":"site1"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class PlayRateSettingSave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('lottery_win_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $site = !empty($data['site']) ? $data['site'] : '';
        $admin_mysql = $config->data_admin;
        if (empty($site)) {
            $context->reply(['status' => 202, 'msg' => '输入数据为空']);

            return;
        }
        $site_sql = 'SELECT *  from site  where site_key=:site_key ';
        $site_data = iterator_to_array($admin_mysql->query($site_sql, [':site_key' => $site]));
        if (empty($site_data)) {
            $context->reply(['status' => 203, 'msg' => '输入的站点不存在']);

            return;
        }

        $lastResult = [];
        $site_win_sql = 'SELECT model_key,game_key,play_key,win_key,bonus_rate from site_win where site_key =:site_key  group by model_key,game_key,play_key,win_key ';
        $win_data = iterator_to_array($admin_mysql->query($site_win_sql, [':site_key' => $site]));
        if (!empty($win_data)) {
            foreach ($win_data as $v1) {
                $model_key = $v1['model_key'];
                $game_key = $v1['game_key'];
                $play_key = $v1['play_key'];
                $win_key = $v1['win_key'];
                $bonus_rate = $v1['bonus_rate'];
                $lastResult[$model_key][$game_key][$play_key][] = [$win_key => $bonus_rate];
            }
            $context->reply(['status' => 200, 'msg' => $lastResult]);

            return;
        }
    }
}
