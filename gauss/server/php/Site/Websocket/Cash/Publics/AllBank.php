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
 * @links  Cash/Publics/AllBank
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 * */

class AllBank extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $bank = ['中国银行', '农业银行', '建设银行', '光大银行', '兴业银行', '中信银行', '招商银行', '民生银行', '交通银行', '广东发展银行', '华夏银行', '工商银行', '平安银行', '邮政储蓄银行', '浦发银行',
        ];
        $context->reply(['status' => 200, 'msg' => '获取成功', 'bank_list' => $bank]);
    }
}
