<?php

namespace Plat\Websocket\Website\GameCommission;

use Lib\Config;
use Lib\Websocket\Context;
use Plat\Websocket\CheckLogin;

/**
 * CommissionSite class.
 *
 * @description   游戏提成比例设置
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/GameCommission/CommissionSite
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class CommissionSite extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_tax_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $mysqlAdmin = $config->data_admin;
        $mysqlAnalysis = $config->data_analysis;
        $site_name = isset($data['site_name']) ? $data['site_name'] : '';
        if (!empty($site_name)) {
            $sql = 'select site_key,site_name from site where site_name=:site_name';
            $param = [':site_name' => $site_name];
        } else {
            $sql = 'select site_key,site_name from site';
            $param = [];
        }

        $monthly = intval(date('Ym', strtotime('today')));
        // $sql = 'select site_key,site_name from site'.$site_name;
        $site_list = [];
        $site = iterator_to_array($mysqlAdmin->query($sql, $param));
        if (!empty($site)) {
            foreach ($site as $key => $val) {
                $sql = 'select bet_lottery,bet_video,bet_game,bet_sports,bet_cards,bonus_lottery,bonus_video,bonus_game,'.
                    'bonus_sports,bonus_cards from monthly_site where  monthly=:monthly and site_key=:site_key';
                $info = [];
                foreach ($mysqlAnalysis->query($sql, [':monthly' => $monthly, ':site_key' => $val['site_key']]) as $row) {
                    $info = $row;
                }
                $site_list[$key]['site_key'] = $val['site_key'];
                $site_list[$key]['site_name'] = $val['site_name'];
                $site_list[$key]['lottery_bet'] = $this->intercept_num($info['bet_lottery']);
                $site_list[$key]['lottery_profit'] = $this->intercept_num($info['bet_lottery'] - $info['bonus_lottery']);
                $site_list[$key]['game_bet'] = $this->intercept_num($info['bet_game']);
                $site_list[$key]['game_profit'] = $this->intercept_num($info['bet_game'] - $info['bonus_game']);
                $site_list[$key]['sports_bet'] = $this->intercept_num($info['bet_sports']);
                $site_list[$key]['sports_profit'] = $this->intercept_num($info['bet_sports'] - $info['bonus_sports']);
                $site_list[$key]['cards_bet'] = $this->intercept_num($info['bet_cards']);
                $site_list[$key]['cards_profit'] = $this->intercept_num($info['bet_cards'] - $info['bonus_cards']);
                $site_list[$key]['video_bet'] = $this->intercept_num($info['bet_video']);
                $site_list[$key]['video_profit'] = $this->intercept_num($info['bet_video'] - $info['bonus_video']);
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $site_list,
        ]);
    }
}
