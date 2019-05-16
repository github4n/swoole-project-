<?php

namespace Plat\Websocket\Website\SiteLottery;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * SiteLotteryEdit class.
 *
 * @description   站点彩票开关修改页面
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/SiteLottery/SiteLotteryEdit  {"site":"site1"}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SiteLotteryEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_lottery_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $admin_mysql = $config->data_admin;
        $public_mysql = $config->data_public;
        $site = !empty($data['site']) ? $data['site'] : '';
        if (empty($site)) {
            $context->reply(['status' => 203, 'msg' => '参数不可为空']);

            return;
        }

        $lastResult = [];
        $translation = [];
        $game_data = [];
        $game_data_sql = 'select * from site_game where site_key =:site_key order by model_key';
        $lottery_game_sql = 'select * from lottery_game ';
        $public_mlottery_game = iterator_to_array($public_mysql->query($lottery_game_sql));
        foreach ($public_mlottery_game as $value) {
            $game_data += [$value['game_key'] => $value['game_name']];
        }
        $site_data = iterator_to_array($admin_mysql->query($game_data_sql, [':site_key' => $site]));
        if (!empty($site_data)) {
            foreach ($site_data as $siteDetail) {
                $model_key = $siteDetail['model_key'];
                $game_key = $siteDetail['game_key'];
                $acceptable = $siteDetail['acceptable'] == 1 ? true : false;
                $translation = end($lastResult);
                if ($translation['model_key'] == $model_key) {
                    array_pop($lastResult);
                    $translation['game_list'][] = [
                        'game_key' => $game_key,
                        'acceptable' => $acceptable,
                    ];
                } else {
                    $translation = [
                        'model_key' => $model_key,
                        'isoff' => '',
                        'game_list' => [
                            [
                                'game_key' => $game_key,
                                'acceptable' => $acceptable,
                            ],
                        ],
                    ];
                }
                $lastResult[] = $translation;
            }
        }

        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $lastResult]);
    }
}
