<?php

namespace App\Websocket\ExternalGame;

use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

/*
 * 获取三方平台余额
 * @description   app/额度转换和游戏大厅获取平台余额
 * @Author  nathan 
 * @date  2019-05-08
 * @links  ExternalGame/GameBalance 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-08
 * @param interface_key|string 平台Key
 * @return status
 * 200|成功
 * 其余status码请对照交接文档
 */
class GameBalance extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $param = $context->getData();
        $interface_key = isset($param['interface_key']) ? $param['interface_key'] : '';
        $user_id = $context->getInfo('UserId');
        $user_key = $context->getInfo('UserKey');

        $adapter = new \Lib\Task\Adapter($config->cache_daemon);
        if ($interface_key) {
            switch ($interface_key) {
                case 'my':
                    //查询账户余额
                    $mysql = $config->data_user;
                    $sql = 'SELECT deal_key FROM user_info WHERE user_id=:user_id';
                    $param = [':user_id' => $user_id];
                    $dealKey = '';
                    foreach ($mysql->query($sql, $param) as $row) {
                        $dealKey = $row['deal_key'];
                    }
                    $client_id = $context->clientId();
                    $adapter->plan('User/Balance', ['user_id' => $user_id, 'deal_key' => $dealKey, 'id' => $client_id]);
                    break;
                case 'ag':
                    break;
                case 'lb':
                    break;
                case 'ky':
                    $params = [
                        'account' => $user_key,
                        's' => 1,
                        'action' => 'getScore',
                        'method' => 'GameBalance',
                        'user_id' => $user_id,
                        'interface_key' => 'ky'
                    ];
                    $adapter->plan('NotifySite', ['path' => 'ExternalGame/ExternalGameSend', 'data' => ["data" => $params]], time(), 1);

                    break;
            }
        }
    }
}
