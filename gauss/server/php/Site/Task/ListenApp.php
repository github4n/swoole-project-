<?php
namespace Site\Task;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class ListenApp implements IHandler
{
    private static $whitelist = [
		'Report/User',
        'AppLoginNotify',
		'Ip/Gather',
		'User/Notice',
        'User/Recharge',
		'ExternalGame/ExternalGameSend',
		'ExternalGame/GameAmountTransfer',
        'ExternalGame/GetMoneyBalance',
        'Lottery/Trend'
    ];
    public function onTask(Context $context, Config $config)
    {
        ['app' => $app] = $context->getData();
        $adapter = $context->getAdapter();
        $cache = $config->__get('cache_' . $app);
        while (false !== ($message = $cache->rpop('NotifySite'))) {
            list($path, $json) = explode(' ', $message, 2);
            if (!in_array($path, self::$whitelist)) {
                continue;
            }
            $data = json_decode($json, true);
            $data['app'] = $app;
            $adapter->plan($path, $data, time(), 7);
        }

<<<<<<< .mine
        $context->repeat(time() +3, 7);
||||||| .r11361
        $context->repeat(time() + 3, 7);
=======
        $context->repeat(time() + 1, 7);
>>>>>>> .r15141
    }
}
