<?php

/**
 * Class RebateSave
 * @description 保存返水比例
 * @author Kayden
 * @date 2019-04-30
 * @link Websocket: Rebate/RebateSetting/RebateSave {"layer_id":1,"is_automatic":"0","issue_time":"17:18","game_list":[]}
 * @param string $layer_id 层级Id
 * @param string $is_automatic 是否自动派发
 * @param string $issue_time 自动派发时间
 * @param array $game_list 彩种返水的设置列表参数
 * @returnDate {}
 */

namespace Site\Websocket\Rebate\RebateSetting;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

class RebateSave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        // 验证用户操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if(!in_array('subsidy_setting', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);
            return;
        }
        
        $mysqlUser = $config->data_user;
        // 接收参数
        $param = $context->getData();
        $gameList = $param['game_list']; // 返水数据详情
        $is = $param['is_automatic']; // 是否自动派发，0：是，1：否
        $time = $param['issue_time']; // 自动派发时间
        $layerId = $param['layer_id']; // 修改的层级

        // 检查参数是否正确
        if(!is_numeric($is)) {
            $context->reply(['status' => 500, 'msg' => '是否自动派发参数错误']);
            return;
        }
        if(!is_numeric($layerId)) {
            $context->reply(['status' => 500, 'msg' => '要修改层级参数错误']);
            return;
        }
        if(!is_array($gameList)) {
            $context->reply(['status' => 500, 'msg' => '返水数据参数错误']);
            return;
        }

        // 层级是否自动派发
        if($is == 0) {
            // 自动派发则更新数据表数据
            $time = str_replace(':', '', $time);
            $array[0] = [
                'layer_id' => $layerId,
                'auto_deliver' => 0,
                'deliver_time' => $time
            ];
            $mysqlUser->subsidy_setting->load($array, [], 'replace');
        } else {
            // 非自动派发则删除数据表数据
            $sql = 'Delete From `subsidy_setting` Where `layer_id` = :layerId';
            $param = [':layerId' => $layerId];
            $mysqlUser->execute($sql, $param);
        }

        // 返水详细数据处理
        $array = [];
        foreach($gameList as $v) {
            // 设置参数检测
            if(!is_numeric($v['bet']) || $v['bet'] < 0) {
                $context->reply(['status' => 500, 'msg' => '打码量类型错误' . $v['game_key']]);
                return;
            }
            if(!is_numeric($v['max_subsidy']) || $v['max_subsidy'] < 0) {
                $context->reply(['status' => 500, 'msg' => '返水上限类型错误']);
                return;
            }
            if(!is_numeric($v['subsidy_rate']) || $v['subsidy_rate'] > 100 || $v['subsidy_rate'] < 0) {
                $context->reply(['status' => 500, 'msg' => '返水比例类型错误或比例不得超过100%']);
                return;
            }

            // 若数据正确则更新数据
            $v['min_bet'] = $v['bet'];
            unset($v['bet']);
            $array[] = $v + ['layer_id' => $layerId];
        }

        // 删除该层级无用的数据
        $sql = 'Delete From `subsidy_game_setting` Where `layer_id` = :layerId';
        $mysqlUser->execute($sql, [':layerId' => $layerId]);
        $mysqlUser->subsidy_game_setting->load($array, [], 'replace');

        // 记录日志
        $sql = 'Insert Into `operate_log` Set `staff_id` = :staffId, `operate_key` = :operateKey, `detail` = :detail, `client_ip` = :clientIp';
        $param = [
            ':staffId' => $context->getInfo('StaffId'),
            ':operateKey' => 'subsidy_setting',
            ':detail' => '修改会员层级Id为' . $layerId . '的返水比例',
            ':clientIp' => ip2long($context->getClientAddr())
        ];
        $config->data_staff->execute($sql, $param);

        // 返回的数据
        $context->reply(['status' => 200, 'msg' => '修改返水数据成功']);
        return;
    }
}
