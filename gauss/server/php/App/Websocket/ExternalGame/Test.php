<?php
namespace App\Websocket\ExternalGame;
use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

/*
 * 额度转换
 * ExternalGame/GameAmountTransfer {"wallet":"my","plat":"ky","money":"500","my_money":"500","ky_money":"500","ag_money":"500","type":0}
 * wallet: 选择钱包
 * plat:   转入平台
 * type；  类型 0-额度转换 1-一键归户
 */
class GameAmountTransfer extends CheckLogin
{
	public function onReceiveLogined(Context $context, Config $config)
	{
		$param = $context->getData();
		$my_money = isset($param['my_money']) ? $param['my_money'] : '';
		$ky_money = isset($param['ky_money']) ? $param['ky_money'] : '';
		$ag_money = isset($param['ag_money']) ? $param['ag_money'] : '';
		$type = isset($param['type']) ? $param['type'] : 0;
		$adapter = new \Lib\Task\Adapter($config->cache_daemon);
		$user_id = $context->getInfo('UserId');
		$user_key = $context->getInfo('UserKey');
		$mysqlUser = $config->data_user;
		$sql = "SELECT account_name,layer_id,deal_key FROM user_info_intact WHERE user_id='$user_id'";
		foreach ($mysqlUser->query($sql) as $v) {
			$account_name = $v['account_name'];
			$layer_id = $v['layer_id'];
			$deal_key = $v['deal_key'];
		}
		$deal_mysql = $config->__get("data_" . $deal_key);
		$account_name = !empty($account_name) ? $account_name : 0;
		$time = time();
		if ($type == 0) {
			$wallet = isset($param['wallet']) ? $param['wallet'] : '';
			$plat = isset($param['plat']) ? $param['plat'] : '';
			if (empty($wallet) || empty($plat)) {
				$context->reply(['status' => 400,'msg' => '请选择钱包或转入平台类型']);
				return;
			}
			if ($wallet == $plat) {
				$context->reply(['status' => 400,'msg' => '请勿选择相同的类型']);
				return;
			}
			$money = isset($param['money']) ? $param['money'] : '';
			if (empty($money) || $money <= 0) {
				$context->reply(['status' => 400,'msg' => '充值金额异常']);
				return;
			}
			$launch_data = json_encode(['money'=>$money]);
			$external_type = $plat;
			if ($external_type == 'my') {
				$external_type = $wallet;
			}

		}

		//额度转换
		if ($type == 0) {
			switch ($wallet) {
				//充值
				case 'my':
					switch ($plat) {
						//充值到开元棋牌
						case 'ky':
							//判断选择钱包内的金额
							if ($money > $my_money) {
								$context->reply(['status' => 400,'msg' => '余额不足']);
								return;
							}
							//插入转出注单sql
							$sql = "INSERT INTO external_export_launch SET 
								user_id=:user_id,user_key=:user_key,account_name=:account_name,layer_id=:layer_id,external_type=:external_type,
								launch_data=:launch_data,launch_money=:launch_money,launch_time=:launch_time";
							$param = [
								":user_id" => $user_id,
								":user_key" => $user_key,
								":account_name" => $account_name,
								":layer_id" => $layer_id,
								":external_type" => $external_type,
								":launch_data" => $launch_data,
								":launch_money" => $money,
								":launch_time" => $time
							];
							$deal_mysql->execute($sql,$param);
							$get_export_serial_sql = "SELECT serial_last('external_export') as export_serial";
							$export_serial = '';
							foreach ($deal_mysql->query($get_export_serial_sql) as $v) {
								$export_serial = $v['export_serial'];
							}
							$params = [
								's' => 2,
								'user_id' => $user_id,
								'account' => $user_key,
								'money' => $money,
								'export_serial' => $export_serial,
								'deal_key' => $deal_key,
								'action' => 'addScore',
								'client_id' => $context->clientId(),
								'interface_key' => $plat,
								'method' => 'GameAmountTransfer'
							];
							$adapter->plan('ExternalGame/GameAmountTransfer',['data'=>$params],time(),1);
							break;
						//充值到AG视讯
						case 'ag':
							break;
						case 'lb':
							break;
					}
				case 'ky':
					switch ($plat) {
						//提现到我的钱包
						case 'my':
							//判断选择钱包内的金额
							if ($money > $ky_money) {
								$context->reply(['status' => 400,'msg' => '余额不足']);
								return;
							}
							//插入转入注单sql
							$sql = "INSERT INTO external_import_launch SET 
								user_id=:user_id,user_key=:user_key,account_name=:account_name,layer_id=:layer_id,external_type=:external_type,
								launch_data=:launch_data,launch_money=:launch_money,launch_time=:launch_time";
							$param = [
								":user_id" => $user_id,
								":user_key" => $user_key,
								":account_name" => $account_name,
								":layer_id" => $layer_id,
								":external_type" => $external_type,
								":launch_data" => $launch_data,
								":launch_money" => $money,
								":launch_time" => $time
							];
							$deal_mysql->execute($sql,$param);
							//获取插入的主键单号
							$get_import_serial_sql = "SELECT serial_last('external_import') as import_serial";
							$import_serial = '';
							foreach ($deal_mysql->query($get_import_serial_sql) as $v) {
								$import_serial = $v['import_serial'];
							}
							$params = [
								's' => 3,
								'user_id' => $user_id,
								'account' => $user_key,
								'money' => $money,
								'import_serial' => $import_serial,
								'deal_key' => $deal_key,
								'action' => 'subordinate',
								'client_id' => $context->clientId(),
								'interface_key' => $plat,
								'withdraw_key' => $wallet,	//校验是充值还是提现的key值
								'method' => 'GameAmountTransfer'
							];
							$adapter->plan('ExternalGame/GameAmountTransfer',['data'=>$params],time(),1);
							break;
						case 'ag':
							//先从ky提现再充值到ag
							break;
						case 'lb':
							break;
					}
				case 'ag':
					break;
				case 'lb':
					break;
				default:
					$context->reply(['status'=>404,'msg'=>'非法参数']);
					return;
			}
		} elseif ($type == 1) {
			//一键归户
			$walletData = ['ky'];
			$plat = 'my';
			foreach ($walletData as $wallet) {
				$external_type = $plat;
				if ($external_type == 'my') {
					$external_type = $wallet;
				}
				$money = $wallet.'_money';
				$launch_data = json_encode(['money'=>$$money]);
				//插入转入注单sql
				$sql = "INSERT INTO external_import_launch SET 
								user_id=:user_id,user_key=:user_key,account_name=:account_name,layer_id=:layer_id,external_type=:external_type,
								launch_data=:launch_data,launch_money=:launch_money,launch_time=:launch_time";
				$param = [
					":user_id" => $user_id,
					":user_key" => $user_key,
					":account_name" => $account_name,
					":layer_id" => $layer_id,
					":external_type" => $external_type,
					":launch_data" => $launch_data,
					":launch_money" => $$money,
					":launch_time" => $time
				];
				$deal_mysql->execute($sql,$param);
				//获取插入的主键单号
				$get_import_serial_sql = "SELECT serial_last('external_import') as import_serial";
				$import_serial = '';
				foreach ($deal_mysql->query($get_import_serial_sql) as $v) {
					$import_serial = $v['import_serial'];
				}
				$params = [
					's' => 3,
					'user_id' => $user_id,
					'account' => $user_key,
					'money' => $$money,
					'import_serial' => $import_serial,
					'deal_key' => $deal_key,
					'action' => 'subordinate',
					'client_id' => $context->clientId(),
					'interface_key' => $plat,
					'withdraw_key' => $wallet,	//校验是充值还是提现的key值
					'method' => 'GameAmountTransfer'
				];
				$adapter->plan('ExternalGame/GameAmountTransfer',['data'=>$params],time(),1);
			}
		}
	}

	/*
	 * 开元上分
	 * */
	protected function ky_recharge()
	{

	}
	/*
	 * 开元下分
	 * */
	protected  function ky_withdraw()
	{

	}
}