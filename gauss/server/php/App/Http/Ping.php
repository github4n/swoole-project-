<?php
namespace App\Http;

use Lib\Config;
use Lib\Http\Context;
use Lib\Http\Handler;

class Ping extends Handler
{
    public function onRequest(Context $context, Config $config)
    {
    	$data = $context->requestQuery();
    	//$name = $data['name'];
		$this->responseJson($context, $data);
        //$this->responseJson($context, ['time' => time()]);
    }
}
