<?php

/**
 * Class RebateList
 * @description 返水设定列表类
 * @author Kayden
 * @date 2019-04-30
 * @link Websocket: Rebate/RebateSetting/RebateList {"layer_id":""}
 * @param string $layer_id 层级Id
 * @returnDate {}
 */

namespace Site\Websocket\Rebate\RebateSetting;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

class RebateList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        // 验证用户权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array('subsidy_setting', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }

        // 获取会员所有层级
        $redis = $config->cache_site;
        $mysqlUser = $config->data_user;
        $layer = json_decode($redis->hget('LayerList', 'allLayer'), true);

        // 添加层级权限控制
        $masterId = $context->getInfo('MasterId');
        if($masterId > 0) {
            $staffId = $context->getInfo('StaffId');
            $mysqlStaff = $config->data_staff;
            $sqlStaff = 'Select `layer_id_list` From `staff_info_intact` Where `staff_id` = :staffId';
            foreach($mysqlStaff->query($sqlStaff, [':staffId' => $staffId]) as $v) {
                $layerList = explode(', ', trim($v['layer_id_list'], '[]'));
            }

            $layerArray = [];
            foreach($layer as $v) {
                if(in_array($v['layer_id'], $layerList)) {
                    $layerArray[] = $v;
                }
            }
            $layer = $layerArray;
        }

        if(empty($layer)) {
            $context->reply([
                'deliver_data' => ['is_automatic' => 1, 'deliver_time' => ''],
                'layer_list' => $layer,
                'list' => [],
                'msg' => '数据获取成功',
                'status' => 200
            ]);
            return;
        }

        // 层级Id
        ['layer_id' => $layerId] = $context->getData();
        $layerId = empty($layerId) ? $layer[0]['layer_id'] : $layerId;

        // 层级是否自动派发
        $sql = 'Select `auto_deliver`,`deliver_time` From `subsidy_setting` Where `layer_id` = :layerId';
        $param = [':layerId' => $layerId];
        $deliver = ['is_automatic' => 1, 'deliver_time' => ''];
        foreach($mysqlUser->query($sql, $param) as $v) {
            // 时间格式拼接
            $length = strlen($v['deliver_time']);
            $today = strtotime('today');
            if ($length > 2) {
                $h = substr($v['deliver_time'], 0, ($length - 2));
                $i = substr($v['deliver_time'], ($length - 2), 2);
                $time = $today + $h * 3600 + $i * 60;
            } else {
                $time = $today + $v['deliver_time'] * 60;
            }
            $deliver = [
                'is_automatic' => intval($v['auto_deliver']),
                'deliver_time' => date('H:i', $time)
            ];
        }

        // 无设定时的返水数据
        $array = [
            'layer_id' => $layerId,
            'max_subsidy' => 0,
            'min_bet' => 0,
            'subsidy_rate' => 0
        ];
        // 获取所有彩种与三方游戏
        $allGame = json_decode($redis->hget('AllLottery', 'AllGame'), true);
        $sql = 'Select `layer_id`,`max_subsidy`,`min_bet`,`subsidy_rate` From `subsidy_game_setting` Where `layer_id` = :layerId And `game_key` = :gameKey Limit 1';
        $list = [
            'lottery' => [],
            'interface' => [
                [
                    'category_key' => 'game',
                    'game_key' => 'fg',
                    'game_name' => 'FG-游戏'
                ],
                [
                    'category_key' => 'cards',
                    'game_key' => 'ky',
                    'game_name' => 'KY-棋牌'
                ],
                
            ]
        ];
        // 三方
        foreach($list['interface'] as $k => $v) {
            $param = [
                ':layerId' => $layerId,
                ':gameKey' => $v['game_key']
            ];
            $interface = iterator_to_array($mysqlUser->query($sql, $param));

            if(empty($interface)) {
                $list['interface'][$k] = $v + $array;
            } else {
                $list['interface'][$k] = $v + $interface[0];
            }
        }
        // 彩种
        foreach($allGame as $v) {    
            if($v['category_key'] == 'lottery') {
                $param = [
                    ':layerId' => $layerId,
                    ':gameKey' => $v['game_key']
                ];
                $lottery = iterator_to_array($mysqlUser->query($sql, $param));

                if(empty($lottery)) {
                    $list['lottery'][] = $v + $array;
                } else {
                    $list['lottery'][] = $v + $lottery[0];
                }
            }
        }

        // 返回数据
        $context->reply([
            'deliver_data' => $deliver,
            'layer_list' => $layer,
            'list' => $list,
            'msg' => '数据获取成功',
            'status' => 200
        ]);
        return;
    }
}
