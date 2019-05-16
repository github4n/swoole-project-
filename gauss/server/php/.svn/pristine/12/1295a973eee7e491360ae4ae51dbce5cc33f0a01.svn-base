<?php
namespace App\Websocket\Game;

use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;

/**
 * Pc端的棋牌搜索接口
 * Game/CardsSearch {"gameName": "牛牛", "interface": "ky", "category": ""}
 */
class CardsSearch extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
    	['gameName' => $gameName, 'interface' => $interface, 'category' => $category] = $context->getData();
        // 游戏查询条件
        $condition = $interface ? ' And interface_key = "' . $interface . '"' : '';
        $condition .= $category ? ' And category_key = "' . $category . '"' : '';

    	// 查询平台中的三方游戏数据
    	$mysqlPlat = $config->__get('data_public');
    	$sqlPlat = 'Select game_key,game_name From external_game Where game_name Like "%' . $gameName . '%"' . $condition;
        $gamePlat = '';
    	foreach ($mysqlPlat->query($sqlPlat) as $v) {
    		$game[$v['game_key']] = $v;
            $gamePlat .= '"' . $v['game_key'] . '",';
    	}
    	// 平台无数据返回
    	if(!isset($game)) {
    		$context->reply(['status' => 404, 'msg' => '找不到相关游戏']);
    		return;
    	}
        $gamePlat = rtrim($gamePlat, ',');

    	// 查询站点中游戏是否打开
    	$mysqlStaff = $config->__get('data_staff');
    	$sqlStaff = 'Select game_key,acceptable From external_game Where game_key In(' . $gamePlat . ')';
    	foreach($mysqlStaff->query($sqlStaff) as $v) {
    		$game[$v['game_key']]['game_on'] = $v['acceptable'];
    	}
        $game = array_values($game);

    	$context->reply(['status' => 200, 'data' => $game]);
    	return;
    }
}