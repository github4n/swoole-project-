<?php
namespace Site\Task\ExternalGame;
use Lib\Task\Context;
use Lib\Config;
use Lib\Task\IHandler;
use Lib\Task\Adapter;

/**
 * @file: ExternalGameReceive.php
 * @description   推送请求三方游戏的返回信息到APP
 * @Author  nathan
 * @date  2019-03-07
 * @links  plat/task/Fg/ky.php,plat/task/Fg/fg.php
 * @returndata json
 * @modifyAuthor lucy
 * @modifyTime 2019-04-01
 */
class ExternalGameReceive implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        // TODO: 推送请求三方游戏的返回信息到APP
        ['data' => $data] = $context->getData();
        $method = isset($data['data']['method']) ? $data['data']['method'] : '';
        $interface_key = isset($data['data']['interface_key']) ? $data['data']['interface_key'] : '';
        if ($method && $interface_key) {
            $adapter = new Adapter($config->cache_daemon);
            $adapter->plan('NotifyApp',['path' => 'ExternalGame/' . $method,'data'=>['data'=>$data]],time(),1);
        }
    }
}