<?php
/**
 * Created by PhpStorm.
 * User: nathan
 * Date: 19-3-7
 * Time: 下午6:18
 */
namespace App\Task\ExternalGame\Fg;
use Lib\Config;
use Lib\Task\Adapter;
use Lib\Task\Context;
use Lib\Task\IHandler;
class CreatePlayer implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $data] = $context->getData();
		if (isset($data['action'])) {
            $taskAdapter = new Adapter($config->cache_daemon);
            $taskAdapter->plan('NotifySite',['path' => 'ExternalGame/ExternalGameSend', 'data'=>["data"=>$data]]);
        } else {
            $datas = isset($data['data']) ? $data['data'] : '';
            if (empty($datas)) {
                return;
            }
            if ($data['status'] == 200) {
                $openid = $datas['fg_openid'];
                $member_code = $datas['fg_member_code'];
                $user_id = $datas['user_id'];
                $password = $datas['fg_password'];
                $mysqlUser = $config->data_user;
                $sql = "INSERT INTO user_fungaming SET user_id = '$user_id',fg_openid ='$openid',fg_member_code='$member_code',fg_password='$password'";
                $mysqlUser->execute($sql);
            }
            $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
            $websocketAdapter->send($datas['client_id'],'ExternalGame/CreateGamePlayer', $data);

        }
    }
}