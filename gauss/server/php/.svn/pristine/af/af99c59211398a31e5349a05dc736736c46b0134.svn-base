<?php

namespace Site\Websocket\Member\Level;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * LevelAdd class.
 *
 * @description   会员层级-会员权限
 * @Author  rose
 * @date  19-4-8
 * @links  Member/Level/LevelAuth
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class LevelAuth extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $mysql = $config->data_user;
        $sql = 'SELECT operate_key,operate_name FROM operate WHERE require_permit=3 OR require_permit=1';
        $user_layer = iterator_to_array($mysql->query($sql));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $user_layer]);
    }
}
