<?php

/**
 * Class LotteryList
 * @description 彩票推荐列表类
 * @author Rose
 * @date 2018-12-07
 * @link Websocket: Website/Index/LotteryList
 * @modifyAuthor: Kayden
 * @modifyDate: 2019-04-27
 */

namespace Site\Websocket\Website\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

class LotteryList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);
            return;
        }

        // 操作权限检测
        $auth = json_decode($context->getInfo('StaffAuth'), true);
        if (!in_array('web_homepage', $auth)) {
            $context->reply(['status' => 206, 'msg' => '当前账号没有操作权限']);
            return;
        }

        $cache = $config->cache_site;
        $mysql = $config->data_staff;
        $sql = 'Select * From `suggest` Where `game_key` In (Select `game_key` From `external_game` Where `acceptable` = 1) Or `game_key` In (Select `game_key` From `lottery_game` Where `acceptable` = 1) Order By `display_order` Asc';
        $list = [];
        $lists = [];
        $lottery_list = [];
        foreach ($mysql->query($sql) as $rows) {
            $list[] = $rows;
        }
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $lists[$key]['game_key'] = $val['game_key'];
                $lists[$key]['game_name'] = $cache->hget('LotteryName', $val['game_key']);
                $lists[$key]['is_popular'] = $val['is_popular'];
                $lists[$key]['display_order'] = $val['display_order'];
                $lists[$key]['to_home'] = $val['to_home'];
                $lists[$key]['category_key'] = $this->category($val['game_key']);
            }
        }
        if (empty($lists)) {
            $game_list = json_decode($cache->hget('AllLottery', 'AllGame'));
            if (!empty($game_list)) {
                foreach ($game_list as $key => $val) {
                    $lottery['game_key'] = $val->game_key;
                    $lottery['game_name'] = $val->game_name;
                    $lottery['display_order'] = 100;
                    $lottery['is_popular'] = 0;
                    $lottery['to_home'] = 0;
                    $lottery['category_key'] = $this->category($val->game_key);
                    $lottery_list[] = $lottery;
                }
            }
            $lists = $lottery_list;
        }
        $context->reply(['status' => 200, 'msg' => '获取数据成功', 'list' => $lists]);
    }

    /**
     * 根据游戏Key判断游戏种类
     * @author Kayden
     * @date 2019-05-07
     * @param string $gameKey 游戏Key
     * @return string
     */
    protected function category($gameKey) {
        if(strpos($gameKey, 'fg_') === 0) {
            return 'game';
        } else if(strpos($gameKey, 'ag_') === 0) {
            return 'video';
        } else if(strpos($gameKey, 'ky_') === 0) {
            return 'cards';
        } else if(strpos($gameKey, 'lb_') === 0) {
            return 'sports';
        } else {
            return 'lottery';
        }
    }
}
