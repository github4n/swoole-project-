<?php

namespace Site\Websocket\Cash\Simple;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 *
 * @description  现金系统-便捷入款
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/Simple/SimplePassage {"user_key":"user123"}
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class SimplePassage extends CheckLogin
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
        if (!in_array('money_simple', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }

        $data = $context->getData();
        $user_key = $data['user_key'];
        if (empty($user_key)) {
            $context->reply(['status' => 203, 'msg' => '请输入会员账户']);

            return;
        }
        $mysqlReport = $config->data_report;
        $mysql = $config->data_staff;
        $masterId = $context->getInfo('MasterId');
        $staff_id = $context->getInfo('StaffId');

        $sql = 'SELECT user_id,user_name,money as user_money,layer_id FROM user_cumulate WHERE user_key=:user_key';
        $param = [':user_key' => $user_key];
        try {
            foreach ($mysqlReport->query($sql, $param) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }
        if (empty($info)) {
            $context->reply(['status' => 204, 'msg' => '搜索不到该账号']);

            return;
        }

        if ($masterId != 0) {
            $sql = 'select layer_id_list from staff_info_intact where staff_id=:staff_id';
            foreach ($mysql->query($sql, [':staff_id' => $staff_id]) as $row) {
                $staff_info = $row;
            }
            if (!in_array($info['layer_id'], json_decode($staff_info['layer_id_list'], true))) {
                $context->reply(['status' => 230, 'msg' => '账号没有搜索该层级会员的权限']);

                return;
            }
        }
        //查找入款通道信息
        $passage_list = [];
        $sql = 'SELECT route_id,passage_id,passage_name,layer_id_list,risk_control,passage_acceptable,acceptable,min_money,max_money,cumulate FROM deposit_route_simple_intact ';
        $list = iterator_to_array($mysql->query($sql));
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $passage_list[$key]['route_id'] = $val['route_id'];
                $passage_list[$key]['passage_name'] = $val['passage_name'];
                $passage_list[$key]['min_money'] = $val['min_money'];
                $passage_list[$key]['max_money'] = $val['max_money'];
                $passage_list[$key]['status'] = 1;   //正常通道
                if (!in_array($info['layer_id'], explode(',', $val['layer_id_list']))) {
                    $passage_list[$key]['status'] = 2;   //不在层级范围
                }
                if ($val['acceptable'] == 0 || $val['passage_acceptable'] == 0) {
                    $passage_list[$key]['status'] = 3;   //已停用
                }
                if ($val['cumulate'] >= $val['risk_control']) {
                    $passage_list[$key]['status'] = 4;   //超出风控金额
                }
            }
        }
        $context->reply(['status' => 200, 'msg' => '查找成功', 'data' => ['user_info' => $info, 'passage_info' => $passage_list]]);
    }
}
