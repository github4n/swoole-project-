<?php
namespace Site\Task\ExternalGame;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: GameAmountTransferNotify.php
 * @description   非免转平台的额度转换接口的站点任务
 * @Author  lucy
 * @date  2019-04-08
 * @links  App\Websocket\Fg\Bet.php
 * @returndata
 * @modifyAuthor
 * @modifyTime
 */
class GameAmountTransferNotify implements IHandler
{
	public function onTask(Context $context, Config $config)
	{
		//转发app请求至站点
		['data' => $data] = $context->getData();
		$adapter = $context->getAdapter();
		//通知app路径
		$method = isset($data['data']['method']) ? $data['data']['method'] : '';
		//校验是充值还是提现的key值
		$withdraw_key = isset($data['return_data']['withdraw_key']) ? $data['return_data']['withdraw_key'] : '';
		$code = isset($data['return_data']['d']['code']) ? $data['return_data']['d']['code'] : '';
		$return_money = isset($data['return_data']['d']['money']) ? $data['return_data']['d']['money'] : '';
		//转出单号
		$export_serial = isset($data['return_data']['export_serial']) ? $data['return_data']['export_serial'] : '';
		//转入单号
		$import_serial = isset($data['return_data']['import_serial']) ? $data['return_data']['import_serial'] : '';
		$deal_key = $data['return_data']['deal_key'];
		$user_id = $data['return_data']['user_id'];
		if (empty($deal_key)) {
			$mysqlUser = $config->data_user;
			//$deal_key = mysqlUser
			$sql = "SELECT deal_key FROM user_info WHERE user_id=:user_id";
			foreach ($mysqlUser->query($sql, [':user_id' => $user_id]) as $row) {
				$deal_key = $row['deal_key'];
			}
		}
		$json = $data['return_data']['json'] ?? '';
		$external_key = $json['orderId'];
		//获取关联库
		$deal_mysql = $config->__get("data_" . $deal_key);
		$time = time();
		$user_data = ['user_id' => $user_id, 'time' => $time];
		$return_data = array_merge($data['return_data']['d'], $user_data);
		$json_data = json_encode($return_data);
		if (empty($withdraw_key)) {
			$sql = "update external_export_launch set launch_data=:launch_data,external_key=:external_key where export_serial=:export_serial";
			$params = [
				":launch_data" => json_encode($json),
				":export_serial" => $export_serial,
				":external_key" => $external_key
			];
			try {
				//执行
				$deal_mysql->execute($sql, $params);
			} catch (\PDOException $e) {
				throw new \PDOException($e);
			}
			if ($code == 0) {
				//充值成功
				//插入转出成功记录sql
				$param = [
					':export_serial' => $export_serial,
					':success_time' => $time,
					':success_data' => $json_data
				];
				$sqls = "INSERT INTO external_export_success SET
								export_serial=:export_serial,success_time=:success_time,success_data=:success_data";
				try {
					//执行
					$deal_mysql->execute($sqls, $param);
				} catch (\PDOException $e) {
					throw new \PDOException($e);
				}
				$res = [
					'user_id' => $user_id,
					'deal_key' => $deal_key,
					'res' => [
						'status' => 200,
						'msg' => '充值成功',
						'data' => [
							'code' => $code,
							'now_wallet_money' => $return_money
						]
					]
				];
			} else {
				//充值失败-添加失败记录
				$param = [
					':export_serial' => $export_serial,
					':failure_time' => $time,
					':failure_data' => $json_data
				];
				$sqls = "INSERT INTO external_export_failure SET
				export_serial=:export_serial,failure_deal_serial='1',failure_time=:failure_time,failure_data=:failure_data";
				try {
					//执行
					$deal_mysql->execute($sqls, $param);
				} catch (\PDOException $e) {
					throw new \PDOException($e);
				}
				$res = [
					'user_id' => $user_id,
					'deal_key' => $deal_key,
					'res' => [
						'status' => 400,
						'msg' => '充值失败',
						'data' => [
							'code' => $code,
							'now_wallet_money' => $return_money
						]
					]
				];
			}
		} else {
			//提现
			$sql = "update external_import_launch set launch_data=:launch_data,external_key=:external_key where import_serial=:import_serial";
			$params = [
				":launch_data" => json_encode($json),
				":import_serial" => $import_serial,
				":external_key" => $external_key
			];
			try {
				//执行
				$deal_mysql->execute($sql, $params);
			} catch (\PDOException $e) {
				throw new \PDOException($e);
			}
			if ($code == 0) {
				//提现到我的钱包成功-添加成功记录
				//插入转出成功记录sql
				$param = [
					':import_serial' => $import_serial,
					':success_time' => $time,
					':success_data' => $json_data
				];
				$sqls = "INSERT INTO external_import_success SET import_serial=:import_serial,success_deal_serial='1',success_time=:success_time,success_data=:success_data";
				try {
					//执行
					$deal_mysql->execute($sqls, $param);
				} catch (\PDOException $e) {
					throw new \PDOException($e);
				}
				$res = [
					'user_id' => $user_id,
					'deal_key' => $deal_key,
					'res' => [
						'status' => 200,
						'msg' => '提现成功',
						'data' => [
							'code' => $code,
							'now_wallet_money' => $return_money
						]
					]
				];
			} else {
				//提现失败-添加失败记录
				$param = [
					':import_serial' => $import_serial,
					':failure_time' => $time,
					':failure_data' => $json_data
				];
				$sqls = "INSERT INTO external_import_failure SET
				import_serial=:import_serial,failure_time=:failure_time,failure_data=:failure_data";
				try {
					//执行
					$deal_mysql->execute($sqls, $param);
				} catch (\PDOException $e) {
					throw new \PDOException($e);
				}
				$res = [
					'user_id' => $user_id,
					'deal_key' => $deal_key,
					'res' => [
						'status' => 400,
						'msg' => '提现失败',
						'data' => [
							'code' => $code,
							'now_wallet_money' => $return_money
						]
					]
				];
			}
		}
		$adapter->plan('NotifyApp', ['path' => 'ExternalGame/' . $method, 'data' => ['data' => $res]]);
	}
}
