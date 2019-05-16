<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-支付管理-银行卡入款通道列表
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/PayManage/PayGateEdit {"route_id":2}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class PayGateEdit extends CheckLogin
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

        $cache = $config->cache_site;
        $data = $context->getData();
        $mysql = $config->data_staff;
        $route_id = $data['route_id'];
        if (!is_numeric($route_id)) {
            $context->reply(['status' => 204, 'msg' => '参数类型错误']);

            return;
        }
        $sql = 'SELECT route_id,min_money,max_money,acceptable,passage_name,way_key,layer_id_list FROM deposit_route_gateway_intact WHERE route_id=:route_id';
        $param = [':route_id' => $route_id];
        $info = array();
        foreach ($mysql->query($sql, $param) as $row) {
            $info = $row;
        }
        $lists['layer_id_list'] = [];
        if (!empty($info)) {
            $lists['route_id'] = $info['route_id'];
            $lists['account_name'] = $info['passage_name'];
            $lists['min_money'] = $info['min_money'];
            $lists['max_money'] = $info['max_money'];
            $lists['acceptable'] = $info['acceptable'];
            $lists['way_key'] = $info['way_key'];
            $layer = explode(',', $info['layer_id_list']);
            foreach ($layer as $item) {
                $lists['layer_id_list'][] .= $item;
            }
        }
        //查找入款通道信息
        $sql = 'SELECT passage_id,account_name FROM deposit_passage_gate_intact';
        $mysql = $config->data_staff;
        foreach ($mysql->query($sql) as $rows) {
            $list[] = $rows;
        }
        //会员层级列表
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
        //三方入款支付方式
        $gate_list = json_decode($cache->hget('PayWayList', 'payWayList'));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'passage' => $list, 'user_layer' => $user_layer, 'agent_layer' => $agent_layer, 'info' => $lists, 'gate_list' => $gate_list]);
    }
}
