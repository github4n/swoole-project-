<?php
namespace Site\Task\ExternalGame;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: GameAmountTransfer.php
 * @description   非免转平台的额度转换接口的平台任务
 * @Author  lucy
 * @date  2019-04-08
 * @links  App\websocket\ExternalGame\GameAmountTransfer.php
 * @returndata
 * @modifyAuthor
 * @modifyTime
 */
class GameAmountTransfer implements IHandler
{
	public function onTask(Context $context, Config $config)
	{
		//转发app请求至站点
		['data' => $data] = $context->getData();
		$interface_key = $data['interface_key'];
		$adapter = $context->getAdapter();
		//当提现到我的钱包时
		if ($interface_key == 'my') {
			$interface_key = $data['withdraw_key'];
		}
		$adapter->plan('NotifyPlat', ['path' => 'ExternalGame/' . $interface_key,'data' =>['data'=>$data]]);
	}
}