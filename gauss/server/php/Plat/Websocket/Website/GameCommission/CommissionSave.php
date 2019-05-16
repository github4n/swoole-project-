<?php

namespace Plat\Websocket\Website\GameCommission;

use Lib\Config;
use Lib\Websocket\Context;
use Plat\Websocket\CheckLogin;

/**
 * CommissionSave class.
 *
 * @description   接收修改游戏提成比例的修改的信息
 * @Author  avery
 * @date  2019-04-23
 * @links  Website/GameCommission/CommissionSave {"site_list":["site1"],"rent":"500000","tax_list":{"cards":[{"range_max":"1000000","tax_rate":"10"},{"range_max":"2000000","tax_rate":"9"},{"range_max":"5000000","tax_rate":"8"},{"range_max":"10000000","tax_rate":"7.5"},{"range_max":"20000000","tax_rate":"7"},{"range_max":"50000000","tax_rate":"6.5"},{"range_max":"100000000","tax_rate":"6"}],"game":[{"range_max":"1000000","tax_rate":"10"},{"range_max":"2000000","tax_rate":"9"},{"range_max":"5000000","tax_rate":"8"},{"range_max":"10000000","tax_rate":"7.5"},{"range_max":"20000000","tax_rate":"7"},{"range_max":"50000000","tax_rate":"6.5"},{"range_max":"100000000","tax_rate":"6"}],"lottery":[{"range_max":"1000000","tax_rate":"10"},{"range_max":"2000000","tax_rate":"9"},{"range_max":"5000000","tax_rate":"8"},{"range_max":"10000000","tax_rate":"7.5"},{"range_max":"20000000","tax_rate":"7"},{"range_max":"50000000","tax_rate":"6.5"},{"range_max":"100000000","tax_rate":"6"}],"sports":[{"range_max":"1000000","tax_rate":"10"},{"range_max":"2000000","tax_rate":"9"},{"range_max":"5000000","tax_rate":"8"},{"range_max":"10000000","tax_rate":"7.5"},{"range_max":"20000000","tax_rate":"7"},{"range_max":"50000000","tax_rate":"6.5"},{"range_max":"100000000","tax_rate":"6"}],"video":[{"range_max":"1000000","tax_rate":"10"},{"range_max":"2000000","tax_rate":"9"},{"range_max":"5000000","tax_rate":"8"},{"range_max":"10000000","tax_rate":"7.5"},{"range_max":"20000000","tax_rate":"7"},{"range_max":"50000000","tax_rate":"6.5"},{"range_max":"100000000","tax_rate":"6"}]}}
 * 参数：site_key:站点,type:game_list:游戏数据数组,game_key:游戏名称,bet_rate:有效投注比例 profit_rate：损益比例
 *
 * @modifyAuthor   avery
 * @modifyTime  2019-04-23
 */
class CommissionSave extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('adminAuth'));
        if (!in_array('site_tax_update', $auth)) {
            $context->reply(['status' => 201, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysqlAdmin = $config->data_admin;
        $site_list = isset($data['site_list']) ? $data['site_list'] : '';
        $tax_list = isset($data['tax_list']) ? $data['tax_list'] : '';
        $rent = isset($data['rent']) ? $data['rent'] : '';
        if (empty($site_list)) {
            $context->reply(['status' => 205, 'msg' => '站点的关键字不能为空']);

            return;
        }
        if (!is_array($site_list)) {
            $context->reply(['status' => 206, 'msg' => '站点的关键字格式不正确']);
        }
        if (empty($tax_list)) {
            $context->reply(['status' => 207, 'msg' => '提交的数据不能为空']);

            return;
        }
        if (!is_array($tax_list)) {
            $context->reply(['status' => 208, 'msg' => '提交的数据格式错误']);

            return;
        }
        if (!is_numeric($rent)) {
            $context->reply(['status' => 209, 'msg' => '服务费的格式不正确']);

            return;
        }
        foreach ($site_list as $site_key) {
            foreach ($tax_list as $key => $categoryData) {
                $category = $key;
                $sql = 'select category from site_tax_config where site_key=:site_key and category=:category ';
                foreach ($mysqlAdmin->query($sql, [':category' => $category, ':site_key' => $site_key]) as $row) {
                    $info = $row;
                }
                if (empty($info)) {
                    $context->reply(['status' => 215, 'msg' => '提交的数据有误,请检查']);

                    return;
                }
                $sql = 'delete from site_tax_config where site_key=:site_key and category=:category ';
                $mysqlAdmin->execute($sql, [':category' => $category, ':site_key' => $site_key]);
                foreach ($categoryData as $item) {
                    $range_max = $item['range_max'];
                    $tax_rate = $item['tax_rate'];
                    if (!is_numeric($range_max)) {
                        $context->reply(['status' => 220, 'msg' => '损益额度范围上限	类型错误']);

                        return;
                    }
                    if ($range_max > 999999999999999 || $range_max < 0) {
                        $context->reply(['status' => 220, 'msg' => '请输入正确额度范围']);

                        return;
                    }
                    if (!is_numeric($tax_rate)) {
                        $context->reply(['status' => 221, 'msg' => '提成比例类型错误']);

                        return;
                    }
                    if ($tax_rate > 100 || $tax_rate < 0) {
                        $context->reply(['status' => 222, 'msg' => '请输入正确的比例']);

                        return;
                    }

                    $betData[] = [
                        'range_max' => $range_max,
                        'tax_rate' => $tax_rate,
                        'category' => $category,
                        'site_key' => $site_key,
                    ];
                }
            }
            //记录修改日志
            $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $params = [
                ':admin_id' => $context->getInfo('adminId'),
                ':operate_key' => 'site_tax_update',
                ':detail' => '修改了站点.'.$site_key.'的损益累计提成比例和服务费.'.$rent,
            ];
            $mysqlAdmin->execute($sql, $params);
            $mysqlAdmin->site_tax_config->load($betData, ['site_key' => $site_key], 'replace');
            $mysqlAdmin->site_rent_config->load([['month_rent' => $rent]], ['site_key' => $site_key], 'replace');
        }
        $context->reply(['status' => 200, 'msg' => '修改成功']);
    }
}
