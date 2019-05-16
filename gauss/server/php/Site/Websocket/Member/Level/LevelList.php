<?php

namespace Site\Websocket\Member\Level;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * Undocumented class.
 *
 * @description   会员层级设置-层级列表
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/Level/LevelList
 * 参数：type(1自动升级,2手工升级)
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class LevelList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        /*if ($StaffGrade != 0) {
            $context->reply(['status' => 203,'msg' =>'当前账号没有操作权限']);

            return;
        } */
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_layer_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $limit = ' LIMIT 1000 ';
        //自动升级会员
        $sql = 'SELECT layer_id,layer_name,min_deposit_amount,min_bet_amount FROM layer_info WHERE layer_type=2'.' order by min_deposit_amount asc '.$limit;
        $list = array();
        $layer_list = array();
        try {
            foreach ($mysql->query($sql) as $rows) {
                $list[] = $rows;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '获取失败']);
            throw new \PDOException($e);
        }

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                //会员层级
                $auth_sql = 'SELECT operate_key FROM layer_permit WHERE layer_id=:layer_id';
                $auth_list = iterator_to_array($mysql->query($auth_sql, [':layer_id' => $val['layer_id']]));
                $sql = 'SELECT count(distinct user_id) as user_id FROM user_info_intact WHERE layer_id=:layer_id';
                $param = [':layer_id' => $val['layer_id']];

                $total = iterator_to_array($mysql->query($sql, $param));

                $layer_list[$key]['level_id'] = $val['layer_id'];
                $layer_list[$key]['level_name'] = $val['layer_name'];
                $layer_list[$key]['auth'] = $auth_list;

                $layer_list[$key]['user_num'] = empty($total[0]['user_id']) ? 0 : $total[0]['user_id'];
                $layer_list[$key]['deposit_amount'] = $this->intercept_num($val['min_deposit_amount']);
                $layer_list[$key]['bet_amount'] = $this->intercept_num($val['min_bet_amount']);
            }
        }

        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $layer_list,
        ]);
    }
}
