<?php
namespace Lib\Http;

use Lib\Config;

interface IHandler
{
    public function onRequest(Context $context, Config $config);
}