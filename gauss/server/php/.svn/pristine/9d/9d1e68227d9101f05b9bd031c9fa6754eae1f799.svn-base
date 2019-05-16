<?php

namespace Plat\Websocket\Report;

use Lib\Config;
use Lib\Websocket\Context;
use Plat\Websocket\CheckLogin;

/**
 * SiteBill class.
 *
 * @description   月结对账报表
 * @Author  rose
 * @date  2019-04-23
 * @links  Report/SiteBill {"site_key":"site1","date":"2019-05"}
 * 参数：site_key:站点key值,date:结算日期
 *
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 *
 * @modifyAuthor   blake
 * @modifyTime  2019-05-14
 */
class SiteBill extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('report_monthly', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $billlist = array();
        $mysqls = $config->data_analysis;
        $mysqlAdmin = $config->data_admin;

        // 计算总的服务费和应收总额tax表 每个月的
        $date = isset($data['date']) ? $data['date'] : '';
        $site_key = isset($data['site_key']) ? $data['site_key'] : '';
        if (empty($site_key)) {
            $context->reply(['status' => 300, 'msg' => '请选择站点']);

            return;
        }
        if (empty($date)) {
            $dateTime = intval(date('Ym', strtotime('today')));
        } else {
            $dateTime = intval(date('Ym', strtotime($date.'-01')));
        }

        // bet :投注总额,bonus: 派彩总额,profit: 损益,tax:提成
        $arr_val = ['bet', 'bonus', 'profit', 'tax'];

        $arr_key = [
            'lottery' => 0,  //  彩票游戏
            'video' => 0,  //  真人视讯
            'game' => 0,  //  电子游戏
            'sports' => 0,  //  体育游戏
            'cards' => 0,  //  棋牌游戏
        ];

        // 月结 数据列表
        $rows_list = [];
        $list = [];
        $site_key = empty($site_key) ? 'site1' : $site_key;

        $sql = 'select month_rent from site_rent_config where site_key=:site_key';
        foreach ($mysqlAdmin->query($sql, [':site_key' => $site_key]) as $row) {
            $month_rent = $row['month_rent'];
        }

        if ($dateTime == intval(date('Ym', strtotime('today')))) {
            $sql = 'select * from monthly_site where site_key=:site_key';
            foreach ($mysqls->query($sql, [':site_key' => $site_key]) as $rows) {
                $list = $rows;
            }

            foreach ($arr_val as $val) {
                foreach ($arr_key as $k => $v) {
                    $key_val = $val.'_'.$k;
                    $lists[$k] = empty($list[$key_val]) ? $v : floatval($list[$key_val]);
                }
                $rows_list[$val] = $lists;
            }
        } else {
            $sql = 'SELECT * FROM monthly_tax WHERE monthly=:monthly AND site_key=:site_key';
            $param = [
                ':monthly' => $dateTime,
                ':site_key' => $site_key,
            ];

            $list = [];
            foreach ($mysqls->query($sql, $param) as $row) {
                $list = $row;
            }

            foreach ($arr_val as $val) {
                foreach ($arr_key as $k => $v) {
                    if ($val == 'bet') {
                        $key_val = 'wager_'.$k;
                    } else {
                        $key_val = $val.'_'.$k;
                    }
                    $lists[$k] = empty($list[$key_val]) ? $v : floatval($list[$key_val]);
                }
                $rows_list[$val] = $lists;
            }
        }
        $rent = $month_rent;  // 服务费
        $total = 0;     // 应收金额
        // 结算提成
        foreach ($rows_list as $key => $value) {
            if ($key == 'profit') {
                foreach ($arr_key as $key => $val) {
                    $rows_list['tax'][$key] = $this->Commission($config, $site_key, $key, $value[$key]);
                    $total += floatval($rows_list['tax'][$key]);
                }
            }
        }
        // 格式化金额
        foreach ($rows_list as $key => $value) {
            foreach ($arr_key as $k => $val) {
                $rows_list[$key][$k] = $this->intercept_num($value[$k]);
            }
        }
        $total = floor(($total + $rent) * 100) / 100;
        $total = $this->intercept_num($total);
        $rent = $this->intercept_num($rent);
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'rent' => $rent,
            'total' => $total,
            'list' => $rows_list,
        ]);
    }

    /**
     * 结算提成.
     *
     * @Author Avery
     * @date   2019-04-30
     *
     * @param object $config
     * @param string $site     站点
     * @param string $category 类型
     * @param float  $amount   金额
     *
     * @return string 提成
     */
    public function Commission($config, $site, $category, $amount)
    {
        $mysqlAdmin = $config->data_admin;

        $amount = floatval($amount);
        $commission = 0;
        if ($amount > 0) {
            // 获取提成比例
            $taxConfArr = [];
            $tax_config_sql = 'SELECT * FROM site_tax_config WHERE site_key=:site_key order by range_max';
            foreach ($mysqlAdmin->query($tax_config_sql, [':site_key' => $site]) as $rows) {
                $list = [
                    'range_max' => $rows['range_max'],
                    'tax_rate' => $rows['tax_rate'],
                ];
                $taxConfArr[$rows['category']][] = $list;
            }

            foreach ($taxConfArr as $key => $value) {
                for ($i = 0; $i < count($value); ++$i) {
                    if ($i == 0) {
                        $value[$i]['range_min'] = '0';
                    } else {
                        $value[$i]['range_min'] = $value[$i - 1]['range_max'];
                    }
                }
                $taxConfArr[$key] = $value;
            }
            $range = [];
            foreach ($taxConfArr[$category] as $key => $value) {
                if ($value['range_min'] < $amount) {
                    $range[] = $value;
                }
            }
            //　结算金额
            $rate = 0;
            if (count($range) <= 1) {
                $rate = $range[0]['tax_rate'] / 100;
                $commission = $amount * $rate;
            } else {
                // 基本提成
                $commission = $range[0]['range_max'] * $range[0]['tax_rate'] / 100;

                // 范围提成
                $new_amount = $amount - $range[0]['range_max'];
                $k = count($range) - 1;
                $rate_a = $range[$k]['tax_rate'] / 100;

                // 提成 =　基本提成 + 范围提成
                $commission = $commission + $new_amount * $rate_a;
            }
        }

        return $commission;
    }
}
