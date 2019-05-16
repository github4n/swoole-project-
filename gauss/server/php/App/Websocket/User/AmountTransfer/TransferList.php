<?php

namespace App\Websocket\User\AccountDetails;
use Lib\Websocket\Context;
use Lib\Config;
use App\Websocket\CheckLogin;

/*
 * 我的-额度转换记录
 * User/AmountTransfer/TransferList
 * */
class TransferList extends CheckLogin
{
	public function onReceiveLogined(Context $context, Config $config)
	{
		$userId = $context->getInfo('UserId');
		$dealKey = $context->getInfo('DealKey');
		$deal_mysql = $config->__get('data_' . $dealKey);

	}
}