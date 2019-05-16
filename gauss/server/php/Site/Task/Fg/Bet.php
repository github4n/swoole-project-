<?php
namespace Site\Task\Fg;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: Bet.php
 * @description   fg游戏
 * @Author  lucy
 * @date  2019-04-01
 * @links  plat/Http/Fg/Bet
 * @returndata
 * @modifyAuthor
 * @modifyTime
 */
class Bet implements IHandler
{
	public function onTask(Context $context, Config $config)
	{
		$data = $context->getData();
		$username = $data['username'];
		$betAmount = $data['betAmount'];
		$betId = $data['betId'];
		$clientId = $data['clientId'];
		$adapter = $context->getAdapter();

		//判断用户是否存在
		$mysql = $config->data_user;
		$sql = "SELECT user_id FROM user_fungaming WHERE fg_member_code=:username ";
		$user_id = '';
		foreach ($mysql->query($sql, ['username' => $username]) as $v) {
			$user_id = $v['user_id'];
		}
		if (!$user_id) {
			$res = array(
				'data' => array(
					'state' => 105,
					'message' => '用户不存在'
				),
				'clientId' => $clientId
			);
			$adapter->plan('NotifyPlat', ['path' => 'Fg/Bet', 'data' => $res]);
			fwrite(STDERR,  Date('[Y-m-d H:i:s]') . '----fg充值失败: 用户不存在' . "\n");
			return;
		}

		//获取用户数据
		$sql = "SELECT user_key,account_name,deal_key,layer_id FROM user_info_intact WHERE user_id=:user_id";
		foreach ($mysql->query($sql, [':user_id' => $user_id]) as $v) {
			$user_key = $v['user_key'];
			$account_name = $v['account_name'];
			$deal_key = $v['deal_key'];
			$layer_id = $v['layer_id'];
		}
		//获取关联库
		$deal_mysql = $config->__get("data_" . $deal_key);
		//判断金额
		$sql = "SELECT money FROM account WHERE user_id=:user_id";
		$money = 0;
		foreach ($deal_mysql->query($sql, [':user_id' => $user_id]) as $v) {
			$money = $v['money'];
		}
		if ($betAmount > $money) {
			$res = array(
				'data' => array(
					'state' => 100,
					'message' => '玩家余额不足'
				),
				'clientId' => $clientId
			);
			$adapter->plan('NotifyPlat', ['path' => 'Fg/Bet', 'data' => $res]);
			fwrite(STDERR,  Date('[Y-m-d H:i:s]') . '----fg充值失败: 玩家余额不足' . "\n");
			return;
		}
		//判断注单号是否存在
		$lauch_data = json_encode(['betId' => $betId, 'betAmount' => $betAmount]);
		$sql = "SELECT export_serial FROM external_export_launch WHERE external_type = :fg AND launch_data->'$.betId' =:betId AND launch_data->'$.betAmount' =:betAmount ";
		$fungaming = [];
		$param = [
			':fg' => 'fg',
			':betId' => $betId,
			':betAmount' => $betAmount
		];
		foreach ($deal_mysql->query($sql, $param) as $v) {
			$fungaming = $v;
		}
		$Common = new Common();
		if (!empty($fungaming)) {
			$data = [
				'username' => $username,
				'balance' => floor($money * 100),
				'walletTime' => $Common->utc_time()
			];
			$res = array(
				'data' => array(
					'state' => 102,
					'message' => '注单已存在',
					'data' => $data
				),
				'clientId' => $clientId
			);
			$adapter->plan('NotifyPlat', ['path' => 'Fg/Bet', 'data' => $res]);
			fwrite(STDERR,  Date('[Y-m-d H:i:s]') . '----fg充值失败: 注单已存在' . "\n");
			return;
		}
		$time = time();
		//新增转出记录sql
		$sql = "INSERT INTO external_export_launch SET
				user_id=:user_id,user_key=:user_key,account_name=:account_name,
				layer_id=:layer_id,external_type=:external_type,external_key=:external_key,launch_money=:launch_money,
				launch_time=:launch_time,launch_data=:launch_data";
		$param = [
			":user_id" => $user_id,
			":user_key" => $user_key,
			":account_name" => !empty($account_name) ? $account_name : 0,
			":layer_id" => $layer_id,
			":external_type" => 'fg',
			":external_key" => $betId,
			":launch_money" => $betAmount,
			":launch_time" => $time,
			":launch_data" => $lauch_data
		];
		try {
			$deal_mysql->execute($sql, $param);
			$get_export_serial_sql = "SELECT serial_last('external_export') as export_serial";
			foreach ($deal_mysql->query($get_export_serial_sql) as $v) {
				$export_serial = $v['export_serial'];
			}
		} catch (\PDOException $e) {
			$adapter->plan('NotifyPlat', ['path' => 'Fg/Bet', 'clientId' => $clientId, 'data' => ['state' => 2, 'message' => '获取失败']]);
			throw new \PDOException($e);
		}
		$param = [
			':export_serial' => $export_serial,
			':success_time' => $time,
			':lauch_data' => $lauch_data
		];

		$data = [
			'username' => $username,
			'balance' => floor(($money - $betAmount) * 100),
			'walletTime' => $Common->utc_time()
		];
		$res = array(
			'data' => array(
				'state' => 0,
				'message' => 'OK',
				'data' => $data
			),
			'clientId' => $clientId,
			'betId'   => $betId,
			'success_param' => $param
		);
		$adapter->plan('NotifyPlat', ['path' => 'Fg/Bet', 'data' => $res], time(), 1);
		return;
	}
}
