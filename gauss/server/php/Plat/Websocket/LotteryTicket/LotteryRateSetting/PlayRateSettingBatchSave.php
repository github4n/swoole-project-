<?php

namespace Plat\Websocket\LotteryTicket\LotteryRateSetting;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * PlayRateSettingBatchSave class.
 *
 * @description   彩票赔率设置保存
 * @Author  avery
 * @date  2019-04-18
 * @links  LotteryTicket/LotteryRateSetting/PlayRateSettingBatchSave  {"site_win":{"dice":{"dice_ah":{"dice_any2":[{"dice_any2_1":"10"},{"dice_any2_2":"5"},{"dice_any2_3":"7.056"},{"dice_any2_4":"7.056"},{"dice_any2_5":"7.056"},{"dice_any2_6":"7.056"}]}}},"site":["site1","site2"]}
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class PlayRateSettingBatchSave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('lottery_win_update', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();

        $admin_mysql = $config->data_admin;
        $site = !empty($data['site']) ? $data['site'] : '';
        $site_win = !empty($data['site_win']) ? $data['site_win'] : '';
        if (empty($site) || empty($site_win)) {
            $context->reply(['status' => 202, 'msg' => '站点及赔率均不可为空']);

            return;
        }

        foreach ($site as $site_choise) {
            $site_set = $site_choise;
            $siteStaff_mysql = $config->__get('data_'.$site_set.'_staff');
            $site_sql = 'SELECT * from site  where site_key=:site_key ';
            $site_data = iterator_to_array($admin_mysql->query($site_sql, [':site_key' => $site_set]));
            if (empty($site_data)) {
                $context->reply(['status' => 203, 'msg' => '输入的站点不存在']);

                return;
            }

            if ($site_data[0]['status'] == 0 || $site_data[0]['status'] == 1) {
                $context->reply(['status' => 203, 'msg' => $site_set.'站点未关闭无法修改站点赔率']);

                return;
            }

            $siteWinData = [];
            $lotteryGameWinData = [];
            foreach ($site_win as $key => $value) {
                if (floatval($value['bonus_rate']) <= 0) {
                    $context->reply(['status' => 300, 'msg' => '请输入正确赔率,且赔率不能为0']);

                    return;
                }
                $siteWinData[] = [
                    'site_key' => $site_set,
                    'model_key' => $value['model_key'],
                    'game_key' => $value['game_key'],
                    'play_key' => $value['play_key'],
                    'win_key' => $value['win_key'],
                    'bonus_rate' => $value['bonus_rate'],
                ];
                $lotteryGameWinData[] = [
                    'bonus_rate' => $value['bonus_rate'],
                    'game_key' => $value['game_key'],
                    'play_key' => $value['play_key'],
                    'win_key' => $value['win_key'],
                ];
            }

            try {
                $admin_mysql->site_win->load($siteWinData, [], 'update bonus_rate = values(bonus_rate)');
                $siteStaff_mysql->lottery_game_win->load($lotteryGameWinData, [], 'update bonus_rate = values(bonus_rate)');
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '修改站点'.$site_set.'彩种'.$play_key.':'.'玩法'.$tkey.'的赔率失败']);
                throw new \PDOException($e);
            }

            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $sql = 'select game_key from site_game where site_key=:site_key and acceptable=1';
            foreach ($admin_mysql->query($sql, [':site_key' => $site_choise]) as $row) {
                $taskAdapter->plan('NotifySite', ['path' => 'Lottery/GameWin', 'data' => ['game_key' => $row['game_key']]]);
            }
        }
        //记录修改日志
        $sqlss = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
        $param = [
            ':admin_id' => intval($context->getInfo('adminId')),
            ':operate_key' => 'lottery_win_update',
            ':detail' => '修改了彩票玩法赔率',
        ];
        $admin_mysql->execute($sqlss, $param);
        $context->reply(['status' => 200, 'msg' => '设置成功']);
        $taskAdapter->plan('NotifySite', ['path' => 'Lottery/Step', 'data' => []], time(), 1);
    }
}
