<?php

namespace Site\Websocket\Member\Level;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * Undocumented class.
 *
 * @description   会员层级-编辑手动升级
 * @Author  blake
 * @date  2019-05-08
 * @links   Member/Level/ManualEdit
 * 参数：level_id:等级的唯一id
 * {"type":1,"level_name":"name","auth":["register","recharge"],"deposit":145621,"bet":45655}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class ManualEdit extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_layer_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $layer_id = $data['level_id'];
        if (empty($layer_id)) {
            $context->reply(['status' => 203, 'msg' => '编辑的会员等级名称不能为空']);

            return;
        }
        if (!is_numeric($layer_id)) {
            $context->reply(['status' => 204, 'msg' => '编辑的会员层级类型不正确']);

            return;
        }
        $sql = 'SELECT layer_name FROM layer_info WHERE layer_id=:layer_id AND layer_type = 1';
        $param = [':layer_id' => $layer_id];
        $info = array();
        foreach ($mysql->query($sql, $param) as $row) {
            $info = $row;
        }
        $sql = 'SELECT operate_key FROM layer_permit WHERE layer_id=:layer_id';
        foreach ($mysql->query($sql, $param) as $row) {
            $auth[] = $row;
        }
        $auths = array();
        if (!empty($auth)) {
            foreach ($auth as $key => $val) {
                $auths[] .= $val['operate_key'].',';
            }
        }
        $info['auth'] = $auths;
        $context->reply([
            'status' => 200,
            'msg' => '信息获取成功',
            'info' => $info,
        ]);
    }
}
