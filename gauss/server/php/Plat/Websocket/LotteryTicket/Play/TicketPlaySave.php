<?php

namespace Plat\Websocket\LotteryTicket\Play;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * TicketPlaySave class.
 *
 * @description   保存彩票列表
 * @Author  avery
 * @date  2019-04-18
 * @links  LotteryTicket/Play/TicketPlaySave {"model_key":"dice","game_list":"","play_list":[{"game_key":"dice_fast","play_key":"dice_any2","switch":"off"},{"game_key":"dice_ah","play_key":"dice_any2","switch":"off"}]}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class TicketPlaySave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('ticket_play_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_public;
        $mysqls = $config->data_admin;

        $game_list = $data['game_list'];
        $play_list = $data['play_list'];
        if (empty($game_list) && empty($play_list)) {
            $context->reply(['status' => 209, 'msg' => '提交的数据不能为空']);

            return;
        }
        if (!empty($game_list)) {
            if (!is_array($game_list)) {
                $context->reply(['status' => 204, 'msg' => '参数类型错误']);

                return;
            }
            $game_key_list = array();
            foreach ($game_list as $item) {
                $game_key = $item['game_key'];
                $switch = $item['switch'];
                if ($switch === 'on') {
                    $acceptable = 1;
                } elseif ($switch === 'off') {
                    $acceptable = 0;
                } else {
                    $context->reply(['status' => 205, 'msg' => '开关参数错误']);

                    return;
                }
                $sql = 'UPDATE lottery_game SET acceptable=:acceptable WHERE game_key=:game_key';
                $param = [':acceptable' => $acceptable, ':game_key' => $game_key];
                $game_key_list[] .= $game_key;
                try {
                    $mysql->execute($sql, $param);
                } catch (\PDOException $e) {
                    $context->reply(['status' => 400, 'msg' => '修改失败']);
                    throw new \PDOException($e);
                }
            }
            //记录日志
            $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $param = [
                ':admin_id' => $context->getInfo('adminId'),
                ':operate_key' => 'ticket_play_update',
                ':detail' => '编号为'.$context->getInfo('adminId').'修改了彩种'.json_encode($game_key_list).'的开关',
            ];
            $mysqls->execute($sql, $param);
        }
        if (!empty($play_list)) {
            if (!is_array($play_list)) {
                $context->reply(['status' => 206, 'msg' => '彩票玩法参数错误']);

                return;
            }
            foreach ($play_list as $item) {
                $play_key = $item['play_key'];
                $game_key = $item['game_key'];
                $switch = $item['switch'];
                if ($switch === 'on') {
                    $acceptable = 1;
                } elseif ($switch === 'off') {
                    $acceptable = 0;
                } else {
                    $context->reply(['status' => 205, 'msg' => '开关参数错误']);

                    return;
                }
                $sql = 'UPDATE lottery_game_play SET acceptable=:acceptable WHERE game_key=:game_key AND play_key=:play_key';
                $param = [':acceptable' => $acceptable, ':game_key' => $game_key, ':play_key' => $play_key];
                try {
                    $mysql->execute($sql, $param);
                } catch (\PDOException $e) {
                    $context->reply(['status' => 400, 'msg' => '修改失败']);
                    throw new \PDOException($e);
                }
            }
            //记录日志
            $sqls = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $param = [
                ':admin_id' => $context->getInfo('adminId'),
                ':operate_key' => 'ticket_play_update',
                ':detail' => '编号为修改了玩法'.json_encode($play_list).'的开关',
            ];
            $mysqls->execute($sqls, $param);
        }
        $context->reply(['status' => 200, 'msg' => '保存成功']);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $taskAdapter->plan('Lottery/SiteLottery', [], time() + 30, 3);
    }
}
