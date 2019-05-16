<?php

namespace Plat\Websocket\LotteryTicket\LotteryRateSetting;

use Plat\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/**
 * PlayRateSetting class.
 *
 * @description   玩法赔率设置列表
 * @Author  avery
 * @date  2019-04-08
 * @links  LotteryTicket/LotteryRateSetting/PlayRateSetting {"status":"0","site_name":""}
 *  game_key:彩种
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class PlayRateSetting extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('lottery_win_select', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        //0213
        $data = $context->getData();
        $admin_mysql = $config->data_admin;
        $analysis_mysql = $config->data_analysis;
        $site_name = !empty($data['site_name']) ? $data['site_name'] : '';
        $status = is_numeric($data['status']) ? $data['status'] : '';
        $month_time = date('Ym', strtotime(date('Y-m', time()).'-01 00:00:00')); //本月

        $last_data = [];

        $site_sql = 'SELECT site_key,site_name from site where 1=1';
        $site_param = [];
        if (is_numeric($status)) {
            if (!in_array($status, [0, 1, 2, 3])) {
                $context->reply(['status' => 202, 'msg' => '状态选择错误']);

                return;
            }
        }

        if ($status !== '' && !empty($site_name)) {
            $site_sql = 'SELECT site_key,site_name from site where status=:status and site_name=:site_name';
            $site_param = [':status' => $status, ':site_name' => $site_name];
        }

        if (is_numeric($status)) {
            $site_sql = 'SELECT site_key,site_name from site where status=:status';
            $site_param = [':status' => intval($status)];
        }

        if (!empty($site_name)) {
            $site_sql = 'SELECT site_key,site_name from site where site_name=:site_name';
            $site_param = [':site_name' => $site_name];
        }
        $site_list = iterator_to_array($admin_mysql->query($site_sql, $site_param));

        if (!empty($site_list)) {
            foreach ($site_list as $value) {
                $keyTranslation = $value['site_key'];
                $monthly_site_param = [':month_time' => $month_time, ':site_key' => $keyTranslation];
                if (!empty($site_name)) {
                    $monthly_site_sql = 'SELECT site_key, site_name,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all from monthly_site where monthly=:month_time and site_name=:site_name and site_key=:site_key';
                    $monthly_site_param[':site_name'] = $site_name;
                } else {
                    $monthly_site_sql = 'SELECT site_key, site_name,sum(bet_all) as bet_all,sum(bonus_all) as bonus_all from monthly_site where monthly=:month_time and site_key=:site_key';
                }

                $site_bet_data = iterator_to_array($analysis_mysql->query($monthly_site_sql, $monthly_site_param));
                if (!empty($site_bet_data)) {
                    $list = [
                        'site_key' => $keyTranslation,
                        'site_name' => $site_bet_data[0]['site_name'],
                        'bet_all' => $site_bet_data[0]['bet_all'] ? $site_bet_data[0]['bet_all'] : 0,
                        'bonus_all' => $site_bet_data[0]['bonus_all'] ?: 0,
                        'monthIncome' => $site_bet_data[0]['bet_all'] - $site_bet_data[0]['bonus_all'],
                    ];
                    $list['bet_all'] = $this->intercept_num($list['bet_all']);
                    $list['bonus_all'] = $this->intercept_num($list['bonus_all']);
                    $list['monthIncome'] = $this->intercept_num($list['monthIncome']);

                    $last_data[] = $list;
                }
            }
        }

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'site_status' => $status,
            'list' => $last_data,
        ]);
    }
}
