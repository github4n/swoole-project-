<?php

namespace Site\Task\Guest;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/*
 * Balance.php
 * @description   游客余额任务
 * @Author  nathan
 * @date  2019-05-09
 * @links  Guest/Balance
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-09
 * @param money|余额
 */
class Balance implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        //更新用户金额
        ['money' => $money] = $context->getData();
        $mysql = $config->data_guest;
        $sql = 'UPDATE account SET money=:money';
        $mysql->execute($sql, [':money' => 2000]);
        //检测超过72小时的用户session并删除
        $sql = 'delete from guest_session where lose_time>=:lose_time';
        $mysql->execute($sql, [':lose_time' => time() - 86400 * 3]);
    }
}
