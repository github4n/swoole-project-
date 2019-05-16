<?php

namespace Plat\Websocket\Website\SiteLottery;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * SiteLotterySave class.
 *
 * @description   修改站点彩票开关
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/SiteLottery/SiteLotterySave   {"site":["site1","site2"],"list":[{"model_key":"dice","isoff":"","game_list":[{"game_key":"dice_fast","acceptable":"on"},{"game_key":"dice_five","acceptable":"on"},{"game_key":"dice_js","acceptable":"on"},{"game_key":"dice_three","acceptable":"on"},{"game_key":"dice_ah","acceptable":"on"}]},{"model_key":"eleven","isoff":"","game_list":[{"game_key":"eleven_fast","acceptable":"on"},{"game_key":"eleven_five","acceptable":"on"},{"game_key":"eleven_gd","acceptable":"on"},{"game_key":"eleven_three","acceptable":"on"}]},{"model_key":"ladder","isoff":"","game_list":[{"game_key":"ladder_fast","acceptable":"on"},{"game_key":"ladder_five","acceptable":"on"},{"game_key":"ladder_three","acceptable":"on"}]},{"model_key":"lucky","isoff":"","game_list":[{"game_key":"lucky_gd","acceptable":"on"},{"game_key":"lucky_three","acceptable":"on"},{"game_key":"lucky_five","acceptable":"on"},{"game_key":"lucky_fast","acceptable":"on"},{"game_key":"lucky_cq","acceptable":"on"}]},{"model_key":"racer","isoff":"","game_list":[{"game_key":"racer_bj","acceptable":"on"},{"game_key":"racer_fast","acceptable":"on"},{"game_key":"racer_five","acceptable":"on"},{"game_key":"racer_malta","acceptable":"on"},{"game_key":"racer_three","acceptable":"on"}]},{"model_key":"six","isoff":"","game_list":[{"game_key":"six_fast","acceptable":"on"},{"game_key":"six_five","acceptable":"on"},{"game_key":"six_hk","acceptable":"on"},{"game_key":"six_three","acceptable":"on"}]},{"model_key":"tiktok","isoff":"","game_list":[{"game_key":"tiktok_cq","acceptable":"on"},{"game_key":"tiktok_fast","acceptable":"on"},{"game_key":"tiktok_five","acceptable":"on"},{"game_key":"tiktok_three","acceptable":"on"}]}]}url
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class SiteLotterySave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_lottery_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $admin_mysql = $config->data_admin;
        $site = !empty($data['site']) ? $data['site'] : '';
        $gameSettings = !empty($data['list']) ? $data['list'] : '';
        if (empty($site)) {
            $context->reply(['status' => 203, 'msg' => '站点参数不可为空']);

            return;
        }
        if (empty($gameSettings)) {
            $context->reply(['status' => 203, 'msg' => '彩票设置参数不可为空']);

            return;
        }
        foreach ($site as $site_detail) {
            $site_sql = 'select *  from site  where site_key=:site_key ';
            $site_data = iterator_to_array($admin_mysql->query($site_sql, [':site_key' => $site_detail]));
            if (empty($site_data)) {
                $context->reply(['status' => 203, 'msg' => '输入的站点不存在']);

                return;
            }
            if ($site_data[0]['status'] != 2 && $site_data[0]['status'] != 3) {
                $context->reply(['status' => 203, 'msg' => $site_detail.'站点未关闭无法修改站点彩票开关']);

                return;
            }
            $siteStaff_mysql = $config->__get('data_'.$site_detail.'_staff');
            $siteGameData = [];
            $lotteryGameData = [];
            foreach ($gameSettings as $game_detail) {
                $model_key = $game_detail['model_key'];
                foreach ($game_detail['game_list'] as $gameKeyDetail) {
                    $accept = $gameKeyDetail['acceptable'];
                    $game_key = $gameKeyDetail['game_key'];

                    if ($accept !== true && $accept !== false) {
                        $context->reply(['status' => 203, 'msg' => '开关参数错误']);

                        return;
                    }

                    if ($accept == true) {
                        $accept = 1;
                    } else {
                        $accept = 0;
                    }
                    $edit_site_sql = 'update lottery_game set acceptable=:acceptable  where model_key=:model_key and game_key =:game_key ';
                    $edit_sql = 'update site_game set acceptable=:acceptable  where site_key=:site_key and model_key=:model_key and game_key =:game_key ';
                    try {
                        $admin_mysql->execute($edit_sql, [':acceptable' => $accept, ':site_key' => $site_detail, ':model_key' => $model_key, ':game_key' => $game_key]);
                        $siteStaff_mysql->execute($edit_site_sql, [':acceptable' => $accept, ':model_key' => $model_key, ':game_key' => $game_key]);
                    } catch (\PDOException $e) {
                        $context->reply(['status' => 400, 'msg' => '修改站点'.':'.$site_detail.'彩种'.$model_key.':'.'玩法'.$game_key.'的开关失败']);
                        throw new \PDOException($e);
                    }
                }
            }

            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $sql = 'select game_key from site_game where site_key=:site_key and acceptable=1';
            foreach ($admin_mysql->query($sql, [':site_key' => $site_detail]) as $row) {
                $taskAdapter->plan('NotifySite', ['path' => 'Lottery/Lottery', 'data' => ['game_key' => $row['game_key']]], time(), 3);
            }
            //将关闭的彩票从站点的首页移除

            $sql = 'select game_key from site_game where site_key=:site_key and acceptable=0';
            foreach ($admin_mysql->query($sql, [':site_key' => $site_detail]) as $row) {
                $sql = 'delete from suggest where game_key=:game_key';
                $siteStaff_mysql->execute($sql, [':game_key' => $row['game_key']]);
            }
            $sql = 'select game_key from site_game where site_key=:site_key and acceptable=1';
            foreach ($admin_mysql->query($sql, [':site_key' => $site_detail]) as $row) {
                $sql = "select game_key from suggest where category_key='lottery' and game_key=:game_key";
                $result = $siteStaff_mysql->execute($sql, [':game_key' => $row['game_key']]);
                if ($result == 0) {
                    $sql = 'insert into suggest set game_key=:game_key,category_key=:category_key,display_order=:display_order,is_popular=:is_popular,to_home=:to_home';
                    $param = [
                            ':game_key' => $row['game_key'],
                            ':category_key' => 'lottery',
                            ':display_order' => 101,
                            ':is_popular' => 0,
                            ':to_home' => 0,
                        ];
                    $siteStaff_mysql->execute($sql, $param);
                    $taskAdapter->plan('NotifySite', ['path' => 'Lottery/Lottery', 'data' => []], time(), 3);
                }
            }
        }

        $sqlss = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
        $param = [
            ':admin_id' => $context->getInfo('adminId'),
            ':operate_key' => 'site_lottery_update',
            ':detail' => '修改了站点彩票开关',
        ];
        $admin_mysql->execute($sqlss, $param);
        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
