<?php

namespace Site\Websocket\Cash\PayManage;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 * @description  现金系统-支付管理-第三方入款通道列表
 * @Author  Rose
 * @date  2019-04-29
 * @links  Cash/PayManage/BankPassage
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class BankPassage extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $mysql = $config->data_staff;
        $sql = 'SELECT passage_id,passage_name FROM deposit_passage_bank_intact WHERE acceptable = 1';
        $list = iterator_to_array($mysql->query($sql));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'list' => $list]);
    }
}
