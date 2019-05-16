<?php

namespace Site\Websocket\Cash\Publics;

use Site\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 *
 *
 * @description  现金系统-所有银行信息
 * @Author  Rose
 * @date  2019-05-07
 * @links  Cash/Publics/BankList
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class BankList extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $mysql = $config->data_staff;
        $sql = 'SELECT distinct bank_name FROM deposit_passage_bank_intact';
        $passage_list = iterator_to_array($mysql->query($sql));
        $context->reply(['status' => 200, 'msg' => '获取成功', 'passage_list' => $passage_list]);
    }
}
