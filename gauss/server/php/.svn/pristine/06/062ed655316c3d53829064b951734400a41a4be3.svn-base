<?php

namespace Site\Websocket\Cash\Publics;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/Publics/SimplePassageList
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class SimplePassageList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $mysql = $config->data_staff;
        $sql = 'SELECT passage_id,passage_name FROM deposit_passage_simple_intact';
        $passage_list = iterator_to_array($mysql->query($sql));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'passage_list' => $passage_list]);
    }
}
