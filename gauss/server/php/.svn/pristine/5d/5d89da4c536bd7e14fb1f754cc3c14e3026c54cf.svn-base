<?php

namespace Site\Task\User;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @description   用户账户余额
 * @Author  Rose
 * @date  2019-05-08
 * @links  User/Balance
 * @modifyAuthor   Rose
 * @modifyTime  2019-05-08
 */
class Balance implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['user_list' => $user_list] = $context->getData();
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $userMysql = $config->data_user;
        $sql = 'select user_id,client_id from user_session where user_id in :user_list';
        $all_user = iterator_to_array($userMysql->query($sql, [':user_list' => $user_list]));
        if (!empty($all_user)) {
            foreach ($all_user as $key => $val) {
                $sql = 'select deal_key from user_info_intact where user_id =:user_id';
                foreach ($userMysql->query($sql, [':user_id' => $val['user_id']]) as $row) {
                    $taskAdapter->plan('NotifyApp', ['path' => 'User/Balance', 'data' => ['user_id' => $val['user_id'], 'deal_key' => $row['deal_key'], 'id' => $val['client_id']]]);
                }
            }
        }
    }
}
