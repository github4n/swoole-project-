<?php
namespace Site\Task\ExternalGame;
use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: ExternalGameSend.php
 * @description   转发app请求至站点
 * @Author  nathan
 * @date  2019-03-07
 * @links  App\Websocket\ExternalGame\GameStart
 * @returndata json
 * @modifyAuthor lucy
 * @modifyTime 2019-04-01
 */
class ExternalGameSend implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        //转发app请求至站点
        ['data' => $param] = $context->getData();

        $adapter = $context->getAdapter();
        $adapter->plan('NotifyPlat', ['path' => 'ExternalGame/' . $param['interface_key'],'data' =>['data'=>$param]],time(),1);
    }
}