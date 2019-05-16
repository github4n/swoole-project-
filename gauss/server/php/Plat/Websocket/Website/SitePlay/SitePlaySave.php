<?php

namespace Plat\Websocket\Website\SitePlay;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * SitePlaySave class.
 *
 * @description   彩票玩法设置
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/SitePlay/SitePlaySave {"site_list":["site1","site1"],"data":{"dice":{"dice_ah":[{"dice_any2":{"acceptable":"off"}},{"dice_any3":{"bet_min":"2","bet_max":"5000"}},{"dice_halfsum":{"bet_min":"2","bet_max":"5000"}},{"dice_merge2":{"bet_min":"2","bet_max":"5000"}},{"dice_merge3":{"bet_min":"2","bet_max":"5000"}},{"dice_pair":{"bet_min":"2","bet_max":"5000"}},{"dice_pairtow":{"bet_min":"2","bet_max":"5000"}},{"dice_serialall":{"bet_min":"2","bet_max":"5000"}},{"dice_sum":{"bet_min":"2","bet_max":"5000"}},{"dice_triple":{"bet_min":"2","bet_max":"5000"}},{"dice_tripleall":{"bet_min":"2","bet_max":"5000"}}]}}}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SitePlaySave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_play_update', $auth)) {
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
            foreach ($data_list as $key => $model) {
                $model_key = $key;
                foreach ($model as $ky => $game) {
                    $game_key = $ky;
                    foreach ($game['play_list'] as $k => $play) {
                        $play_key = $play['play_key'];
                        if ($play['acceptable'] == true) {
                            $switch = 1;
                        } elseif ($play['acceptable'] == false) {
                            $switch = 0;
                        } else {
                            $context->reply(['status' => 220, 'msg' => '开关状态错误']);

                            return;
                        }
                        //验证game_key,model_key,play_key是否正确
                        $infos = [];
                        $sql = 'select bet_min,bet_max from site_play where model_key=:model_key and game_key=:game_key and play_key=:play_key and site_key=:site_key';
                        foreach ($mysqlAdmin->query($sql, [':model_key' => $model_key, ':game_key' => $game_key, ':play_key' => $play_key, ':site_key' => $item]) as $row) {
                            $infos = $row;
                        }
                        if (empty($infos)) {
                            $context->reply(['status' => 215, 'msg' => '提交的数据有误,请检查']);

                            return;
                        }
                        $betData[] = [
                            'bet_min' => $infos['bet_min'],
                            'bet_max' => $infos['bet_max'],
                            'acceptable' => $switch,
                            'model_key' => $model_key,
                            'game_key' => $game_key,
                            'play_key' => $play_key,
                            'site_key' => $item,
                        ];
                    }
                }
            }
            $mysqlAdmin->site_play->load($betData, ['site_key' => $item], 'replace');
            $mysqlStaff = $config->__get('data_'.$item.'_staff');
            $mysqlStaff->lottery_game_play->load($betData, [], 'replace');

            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $sql = 'select game_key from site_play where site_key=:site_key and acceptable=1';
            foreach ($mysqlAdmin->query($sql, [':site_key' => $item]) as $row) {
                $taskAdapter->plan('NotifySite', ['path' => 'Lottery/GamePlay', 'data' => ['game_key' => $row['game_key']]], time(), 4);
            }

            //记录修改日志
            $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $params = [
                ':admin_id' => $context->getInfo('adminId'),
                ':operate_key' => 'site_play_update',
                ':detail' => '修改了站点.'.$item.'的彩票玩法开关',
            ];
            $mysqlAdmin->execute($sql, $params);
        }

        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
