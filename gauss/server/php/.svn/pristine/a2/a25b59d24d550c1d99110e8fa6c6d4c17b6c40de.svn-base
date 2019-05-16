<?php

namespace Site\Websocket\Member\Level;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * ManualList class.
 *
 * @description   会员层级设置-层级列表
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/Level/ManualList
 * 参数：type(1自动升级,2手工升级)
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class ManualList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        /*if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg'=>'当前账号没有操作权限']);

            return;
        }   */
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_layer_select', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;

        $limit = ' LIMIT 1000 ';
        //手动升级会员
        $sql = 'SELECT layer_id,layer_name FROM layer_info WHERE layer_type=1'.' order by layer_id desc '.$limit;
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
                $auth_sql = 'SELECT operate_key FROM layer_permit WHERE layer_id=:layer_id';
                $auth_list = iterator_to_array($mysql->query($auth_sql, [':layer_id' => $val['layer_id']]));
                $sql = 'SELECT user_id FROM user_info_intact WHERE layer_id=:layer_id';
                $param = [':layer_id' => $val['layer_id']];
                $total = $mysql->execute($sql, $param);
                $layer_list[$key]['auth'] = $auth_list;
                $layer_list[$key]['level_id'] = $val['layer_id'];
                $layer_list[$key]['level_name'] = $val['layer_name'];
                $layer_list[$key]['user_num'] = $total;
            }
        }
        $context->reply([
            'status' => 200,
            'msg' => '获取成功',
            'list' => $layer_list,
        ]);
    }
}
