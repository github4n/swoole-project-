<?php

namespace App\Task\ExternalGame;
use Lib\Config;
use Lib\Task\Adapter;
use Lib\Task\Context;
use Lib\Task\IHandler;

class GameAmountTransferNotify implements IHandler
{
	public function onTask(Context $context, Config $config)
	{
		//回调通知
		['data' => $data] = $context->getData();
		$taskAdapter = new Adapter($config->cache_daemon);
		//接收三方平台返回信息
		$websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
		$client_id = isset($data['data']['client_id']) ? $data['data']['client_id'] : '';
		//校验是充值还是提现的key值
		$withdraw_key = isset($data['return_data']['withdraw_key']) ? $data['return_data']['withdraw_key'] : '';
		$interface_key = isset($data['data']['interface_key']) ? $data['data']['interface_key'] : '';
		$code = isset($data['return_data']['d']['code']) ? $data['return_data']['d']['code'] : '';
		$return_money = isset($data['return_data']['d']['money']) ? $data['return_data']['d']['money'] : '';
		//转出单号
		$export_serial = isset($data['return_data']['export_serial']) ? $data['return_data']['export_serial'] : '';
		//转入单号
		$import_serial = isset($data['return_data']['import_serial']) ? $data['return_data']['import_serial'] : '';
		$deal_key = $data['return_data']['deal_key'];
		$user_id = $data['return_data']['user_id'];
		//获取关联库
		$deal_mysql = $config->__get("data_" . $deal_key);
		$time = time();
		$user_data = ['user_id'=>$user_id,'time'=>$time];
		$return_data = array_merge($data['return_data']['d'],$user_data);
		$json_data = json_encode($return_data);
		$res = [
			'status' => 400,
			'msg' => '交易异常'
		];
		//充值
		if (empty($withdraw_key)) {
			if ($code == 0) {
				//充值成功
				//插入转出成功记录sql
				$sqls = "INSERT IGNORE external_export_success SET
							export_serial='$export_serial',success_time='$time',success_data='$json_data'";
				$deal_mysql->execute($sqls);
				$res = [
					'status' => 200,
					'msg' => '充值成功',
					'data' => [
						'code' => $code,
						'now_wallet_money'=>$return_money
					]
				];
				//$taskAdapter->plan('User/Balance', ['user_id' => $user_id,'deal_key'=>$deal_key,'id'=>$client_id]);
			} else {
				//充值失败-添加失败记录
				$sqls = "INSERT IGNORE external_export_failure SET 
				export_serial='$export_serial',failure_deal_serial='1',failure_time='$time',failure_data='$json_data'";
				$deal_mysql->execute($sqls);
				$res = [
					'status' => 400,
					'msg' => '充值失败',
					'data' => [
						'code' => $code,
						'now_wallet_money'=>$return_money
					]
				];
			}
		}else{
			//提现
			if ($code == 0) {
				//提现到我的钱包成功-添加成功记录
				//插入转出成功记录sql
				$sqls = "INSERT INTO external_import_success SET
							import_serial='$import_serial',success_deal_serial='1',success_time='$time',success_data='$json_data'";
				$deal_mysql->execute($sqls);
				$res = [
					'status' => 200,
					'msg' => '提现成功',
					'data' => [
						'code' => $code,
						'now_wallet_money'=>$return_money
					]
				];
				//$taskAdapter->plan('User/Balance', ['user_id' => $user_id,'deal_key'=>$deal_key,'id'=>$client_id]);
			}else {
				//提现失败-添加失败记录
				$sqls = "INSERT INTO external_import_failure SET 
				import_serial='$import_serial',failure_time='$time',failure_data='$json_data'";
				$deal_mysql->execute($sqls);
				$res = [
					'status' => 400,
					'msg' => '提现失败',
					'data' => [
						'code' => $code,
						'now_wallet_money'=>$return_money
					]
				];
			}
		}
		//推送客户端
		$websocketAdapter->send($client_id,'ExternalGame/GameAmountTransfer', $res);
	}
}
