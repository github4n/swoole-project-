<?php

namespace Plat\Websocket\Index;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/*
 * 站点佣金列表
 * 参数：num:每页显示的数量 page:当前显示的页数 gonum：需要跳转的页数
 * {"num":10,"page":1,"gonum":5}
 *
 *
 * */

class SiteCommissionList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $data = $context->getData();
        $limit = ' LIMIT 1000 ';
        //获取上个月的时间
        $date = date('Y-m', strtotime(date('Y', strtotime(time())).'-'.(date('m', strtotime(time())) - 1)));
        $monthly = intval($date);
        $list = array();
        $mysql = $config->data_analysis;
        $sql = 'SELECT site_key,bet_lottery,bet_video,bet_game,bet_sports,bet_cards FROM monthly_site WHERE monthly=:monthly '.$limit;
        $sqls = 'SELECT site_key FROM monthly_site  WHERE monthly=:monthly';
        $param = [':monthly' => $monthly];
        foreach ($mysql->query($sql, $param) as $row) {
            $list[] = $row;
        }
        $mysqls = $config->data_admin;
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $sql = 'SELECT site_name FROM site WHERE site_key=:site_key';
                $para = [':site_key' => $val['site_key']];
                foreach ($mysqls->query($sql, $para) as $rows) {
                    $list[$key]['site_name'] = $rows['site_name'];
                }
                $sqls = 'SELECT tax_total FROM tax WHERE site_key=:site_key';
                foreach ($mysqls->query($sqls, $para) as $row) {
                    $list[$key]['tax_total'] = $row['tax_total'];
                }
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $list,
        ]);
    }
}
