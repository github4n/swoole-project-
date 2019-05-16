<?php

namespace App\Task\ExternalGame;
use Lib\Config;
use Lib\Task\Adapter;
use Lib\Task\Context;
use Lib\Task\IHandler;

class GameAmountTransfer implements IHandler
{
	public function onTask(Context $context, Config $config)
	{
		['data' => $data] = $context->getData();
		//设置了action Key为发送任务请求
        $taskAdapter = new Adapter($config->cache_daemon);
		if (isset($data['action'])) {
			$taskAdapter->plan('NotifySite',['path' => 'ExternalGame/GameAmountTransfer', 'data'=>["data"=>$data]]);
		} else {
			//回调通知
			//接收三方平台返回信息
			$res = isset($data['res']) ? $data['res'] : '';
			$user_id = isset($data['user_id']) ? $data['user_id'] : '';
			$deal_key = isset($data['deal_key']) ? $data['deal_key'] : '';
			$mysql = $config->data_user;
			$sql = "SELECT client_id FROM user_session WHERE user_id='$user_id'";
			$client_id = '';
			foreach ($mysql->query($sql) as $row) {
				$client_id = $row['client_id'];
			}
			//充值成功通知
			if ($res['status'] == 200) {
				$taskAdapter = new Adapter($config->cache_daemon);
				$taskAdapter->plan('User/Balance', ['user_id' => $user_id,'deal_key'=>$deal_key,'id'=>$client_id]);
			}
			$websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
			$websocketAdapter->send($client_id,'ExternalGame/GameAmountTransfer', $res);
		}
	}
}
