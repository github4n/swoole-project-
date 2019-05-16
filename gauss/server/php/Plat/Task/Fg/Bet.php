<?php

namespace Plat\Task\Fg;
use Lib\Task\Context;
use Lib\Config;
use Lib\Task\IHandler;

class Bet implements IHandler
{
	public function onTask(Context $context, Config $config)
	{
		$data = $context->getData();
		$context = new \Lib\Http\Context($config->cache_daemon,$data['clientId']);
		$json = json_encode($data['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$context->responseStatus(200);
		$context->responseHeader('Content-Type', 'application/json');
		$context->responseBody($json);
		$context->responseFinish();
	}
}