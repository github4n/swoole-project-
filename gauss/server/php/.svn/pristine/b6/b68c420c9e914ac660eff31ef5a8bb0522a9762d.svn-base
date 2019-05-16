<?php

namespace Plat\Websocket\LotteryTicket\LotteryBetSetting;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/*
 * 保存投注额设置列表
 * @deprecated 保存投注额设置列表
 * @author avery
 * @date 2019-04-18
 * @link  LotteryTicket/LotteryBetSetting/BetSettingSave {"site_list":["site1","site2"],"data":{"dice":{"dice_ah":[{"dice_any2":{"bet_min":"2","bet_max":"5000"}},{"dice_any3":{"bet_min":"2","bet_max":"5000"}},{"dice_halfsum":{"bet_min":"2","bet_max":"5000"}},{"dice_merge2":{"bet_min":"2","bet_max":"5000"}},{"dice_merge3":{"bet_min":"2","bet_max":"5000"}},{"dice_pair":{"bet_min":"2","bet_max":"5000"}},{"dice_pairtow":{"bet_min":"2","bet_max":"5000"}},{"dice_serialall":{"bet_min":"2","bet_max":"5000"}},{"dice_sum":{"bet_min":"2","bet_max":"5000"}},{"dice_triple":{"bet_min":"2","bet_max":"5000"}},{"dice_tripleall":{"bet_min":"2","bet_max":"5000"}}]}}}
 *              0-开放 1-停止交易 2-关闭前台 3-关闭前后
 * @modifyAuthor    Avery
 * @modifyTime   2019-04-18
 * */
class BetSettingSave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('lottery_bet_update', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $mysqlAdmin = $config->data_admin;
        $site_list = isset($data['site_list']) ? $data['site_list'] : '';
        $data_list = isset($data['data']) ? $data['data'] : '';
        if (empty($site_list)) {
            $context->reply(['status' => 205, 'msg' => '站点参数不能为空']);

            return;
        }
        if (!is_array($site_list)) {
            $context->reply(['status' => 206, 'msg' => '站点的格式不正确']);

            return;
        }
        if (empty($data_list)) {
            $context->reply(['status' => 207, 'msg' => '提交的数据不能为空']);

            return;
        }
        if (!is_array($data_list)) {
            $context->reply(['status' => 208, 'msg' => '提交的数据格式不正确']);

            return;
        }
        //验证站点是否存在和是否关闭
        $betData = [];
        //验证站点是否存在和是否关闭
        foreach ($site_list as $item) {
            $info = [];
            $sql = 'select site_key,status,site_name from site where site_key=:site_key';
            foreach ($mysqlAdmin->query($sql, [':site_key' => $item]) as $row) {
                $info = $row;
            }
            if (empty($info)) {
                $context->reply(['status' => 210, 'msg' => '站点关键字错误']);

                return;
            }
            if ($info['status'] == 0 || $info['status'] == 1) {
                $context->reply(['status' => 211, 'msg' => '站点'.$info['site_name'].'未关闭']);

                return;
            }

            foreach ($data_list as $key => $value) {
                $bet_min = $value['bet_min'];
                $bet_max = $value['bet_max'];
                if (!is_numeric($bet_min) || $bet_min < 2) {
                    $context->reply(['status' => 212, 'msg' => '最小投注额类型错误,且值不能小于2']);

                    return;
                }
                if (!is_numeric($bet_max) || $bet_max < 2) {
                    $context->reply(['status' => 213, 'msg' => '最大投注额类型错误,且值不能小于2']);

                    return;
                }
                if ($bet_min > $bet_max) {
                    $context->reply(['status' => 214, 'msg' => '最小投注额不能大于最大投注额']);

                    return;
                }

                $info = [];
                $sql = 'select acceptable from site_play where model_key=:model_key and game_key=:game_key and play_key=:play_key and site_key=:site_key';
                foreach ($mysqlAdmin->query($sql, [':model_key' => $value['model_key'], ':game_key' => $value['game_key'], ':play_key' => $value['play_key'], ':site_key' => $item]) as $row) {
                    $info = $row;
                }
                if (empty($info)) {
                    $context->reply(['status' => 215, 'msg' => '提交的数据有误,请检查']);

                    return;
                }
                $betData[] = [
                    'bet_min' => $bet_min,
                    'bet_max' => $bet_max,
                    'model_key' => $value['model_key'],
                    'game_key' => $value['game_key'],
                    'play_key' => $value['play_key'],
                    'acceptable' => $info['acceptable'],
                    'site_key' => $item,
                ];
            }

            //记录修改日志
            $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $params = [
                ':admin_id' => $context->getInfo('adminId'),
                ':operate_key' => 'lottery_bet_update',
                ':detail' => '修改了站点.'.$item.'的投注额开关',
            ];
            $mysqlAdmin->execute($sql, $params);
            $mysqlAdmin->site_play->load($betData, [], 'update bet_min=values(bet_min),bet_max=values(bet_max),acceptable=values(acceptable)');
            $mysqlStaff = $config->__get('data_'.$item.'_staff');
            $mysqlStaff->lottery_game_play->load($betData, [], 'update bet_min=values(bet_min),bet_max=values(bet_max),acceptable=values(acceptable)');
            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $sql = 'select game_key from site_game where site_key=:site_key and acceptable=1';
            foreach ($mysqlAdmin->query($sql, [':site_key' => $item]) as $row) {
                $taskAdapter->plan('NotifySite', ['path' => 'Lottery/GamePlay', 'data' => ['game_key' => $row['game_key']]]);
            }
        }

        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
