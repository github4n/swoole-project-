<?php
namespace App\Http\Fg;
use Lib\Config;
use Lib\Http\Context;
use Lib\Http\Handler;
use App\Http\Fg\Common as CommonGame;

class Bet extends Handler
{
	/**
	 * 下注 	  http://127.0.0.1:8080/2/Fg/Bet
	 * @param $username   游戏平台存储的用户的member_code
	 * @param $partnerId  代理商账号
	 * @param $nonce_str  随机字符串
	 * @param $sign  	  通过签名算法计算得出的签名值
	 * @param $betId  	  注单ID
	 * @param $betAmount  下注金额 单位为分
	 */
	public function onRequest(Context $context, Config $config)
	{
		$data = $context->requestPost();
		parse_str($data, $params);
		$username = isset($params['username']) ? $params['username'] : '';
		$partnerId = isset($params['partnerId']) ? $params['partnerId'] : '';
		$nonceStr = isset($params['nonce_str']) ? $params['nonce_str'] : '';
		$sign = isset($params['sign']) ? $params['sign'] : '';
		$betId = isset($params['betId']) ? $params['betId'] : '';
		$betAmount = isset($params['betAmount']) ? $params['betAmount']/100 : '';
		if (!$username || !$partnerId || !$nonceStr || !$sign || !$betId || !$betAmount) {
			$this->responseJson($context, ['state'=>2, 'message'=>'参数异常']);
			return;
		}
		if (!is_numeric($betAmount) || $betAmount <= 0) {
			$this->responseJson($context, ['state'=>104, 'message'=>'金额非法']);
			return;
		}
		//签名参数
		$params = [
			'nonce_str' => $nonceStr,
			'username' => $username,
			'partnerId' => $partnerId,
			'sign' => $sign,
			'betId' => $betId,
			'betAmount' => $betAmount
		];
		//生成签名
		$common = new CommonGame();
		$makeSign = $common->MakeSign($params);
		//校验签名
		if ($makeSign !== $sign) {
			$this->responseJson($context, ['state'=>108, 'message'=>'签名异常']);
			return;
		}
		//判断用户是否存在
		$mysql = $config->data_user;
		$sql = "SELECT user_id FROM user_fungaming WHERE fg_member_code='$username' ";
		foreach ($mysql->query($sql) as $v) {
			$user_id = $v['user_id'];
		}
		if (!$user_id) {
			$this->responseJson($context, ['state'=>105, 'message'=>'用户名不存在']);
			return;
		}
		//获取用户数据
		$sql = "SELECT user_key,account_name,deal_key,layer_id FROM user_info_intact WHERE user_id='$user_id'";
		foreach ($mysql->query($sql) as $v) {
			$user_key = $v['user_key'];
			$account_name = $v['account_name'];
			$deal_key = $v['deal_key'];
			$layer_id = $v['layer_id'];
		}
		//获取关联库
		$deal_mysql = $config->__get("data_" . $deal_key);
		//判断金额
		$sql = "SELECT money FROM account WHERE user_id='$user_id'";
		foreach ($deal_mysql->query($sql) as $v) {
			$money = $v['money'];
		}
		if ($betAmount > $money) {
			$this->responseJson($context, ['state'=>100, 'message'=>'玩家余额不足']);
			return;
		}
		//判断注单号是否存在
		$sql = "SELECT * FROM external_export_fungaming WHERE fg_bet_id ='$betId' ";
		$fungaming = [];
		foreach ($deal_mysql->query($sql) as $v) {
			$fungaming = $v;
		}
		if (!empty($fungaming)) {
			$data = [
				'username' => $username,
				'balance' => $money*100,
				'walletTime' => $common->utc_time()
			];
			$res = $common->return_data(102,'注单已存在', $data);
			$this->responseJson($context, $res);
			return;
		}
		$time = time();
		//新增转出记录sql
		$sql = "INSERT INTO external_export_launch SET
				user_id=:user_id,user_key=:user_key,account_name=:account_name,
				layer_id=:layer_id,external_type=:external_type,launch_money=:launch_money,
				launch_time=:launch_time";
		$param = [
			":user_id"=> $user_id,
			":user_key"=> $user_key,
			":account_name"=> !empty($account_name) ? $account_name : 1,
			":layer_id"=> $layer_id,
			":external_type" => 'fg',
			":launch_money"=> $betAmount,
			":launch_time"=> $time
		];
		try{
			$deal_mysql->execute($sql, $param);
			$get_export_serial_sql = "SELECT serial_last('external_export') as export_serial";
			foreach ($deal_mysql->query($get_export_serial_sql) as $v) {
				$export_serial = $v['export_serial'];
			}
		}catch (\PDOException $e){
			$this->responseJson($context, ['state'=>2, 'message'=>'转出失败']);
			return;
			throw new \PDOException($e);
		}
		//插入转出注单id sql
		$sqls = "INSERT INTO external_export_fungaming SET export_serial='$export_serial',fg_bet_id='$betId' ";
		//插入转出成功记录sql
		$sqlss = "INSERT INTO external_export_success SET export_serial='$export_serial',success_time='$time '";
		try{
			$deal_mysql->execute($sqls);
			$deal_mysql->execute($sqlss);
		}catch (\PDOException $e){
			$this->responseJson($context, ['state'=>2, 'message'=>'转出失败']);
			return;
			throw new \PDOException($e);
		}
		$data = [
			'username' => $username,
			'balance' => ($money-$betAmount)*100,
			'walletTime' => $common->utc_time()
		];
		$res = $common->return_data(0,'OK', $data);
		$this->responseJson($context, $res);
	}
}
