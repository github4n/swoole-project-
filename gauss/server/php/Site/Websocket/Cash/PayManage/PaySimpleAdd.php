<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-支付管理-快捷支付入款通道添加
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/PayManage/PaySimpleAdd {}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class PaySimpleAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        //验证是否有操作权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('money_deposit_route', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        //查找入款通道信息
        $sql = 'SELECT passage_id,passage_name FROM deposit_passage_simple_intact where acceptable = 1';
        $mysql = $config->data_staff;
        $list = [];
        foreach ($mysql->query($sql) as $rows) {
            $list[] = $rows;
        }

        $cache = $config->cache_site;
        $user_mysql = $config->data_user;
        $staffId = $context->getInfo('StaffId');
        $MasterId = $context->getInfo('MasterId');
        if (!empty($MasterId)) {
            //当前账号管理会员的信息
            $sql = 'SELECT layer_id FROM staff_layer WHERE staff_id=:staff_id';
            $param = [':staff_id' => $staffId];
            foreach ($mysql->query($sql, $param) as $rows) {
                $layer_list[] = $rows;
            }
            $user_layer = array();
            $agent_layer = array();
            if (!empty($layer_list)) {
                foreach ($layer_list as $key => $val) {
                    $sql = 'SELECT layer_id,layer_name,layer_type FROM layer_info WHERE layer_id=:layer_id';
                    $param = [':layer_id' => $val['layer_id']];
                    foreach ($user_mysql->query($sql, $param) as $row) {
                        $user = $row;
                    }
                    if ($user['layer_type'] < 3) {
                        $user_layer[$key]['layer_name'] = $user['layer_name'];
                        $user_layer[$key]['layer_id'] = $user['layer_id'];
                    }
                    if ($user['layer_type'] > 100) {
                        $agent_layer[$key]['layer_name'] = $user['layer_name'];
                        $agent_layer[$key]['layer_id'] = $user['layer_id'];
                    }
                }
            }
            sort($agent_layer, 1);
            sort($user_layer, 1);
        } else {
            //会员层级
            $user_layer = json_decode($cache->hget('LayerList', 'userLayer'));
            //代理层级
            $agent_layer = json_decode($cache->hget('LayerList', 'agentLayer'));
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取信息成功',
            'passage' => $list,
            'user_layer' => $user_layer,
            'agent_layer' => $agent_layer,
        ]);
    }
}
