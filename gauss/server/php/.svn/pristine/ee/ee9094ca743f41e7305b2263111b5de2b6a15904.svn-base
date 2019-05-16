<?php
/**
 * Created by PhpStorm.
 * User: ayden
 * Date: 19-3-28
 * Time: 上午9:28
 */

namespace App\Task\ExternalGame;
use Lib\Config;
use Lib\Task\Adapter;
use Lib\Task\Context;
use Lib\Task\IHandler;

class GameBalance implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $data] = $context->getData();

        //设置了action Key为发送任务请求
        if (isset($data['action'])) {
            $taskAdapter = new Adapter($config->cache_daemon);
            $taskAdapter->plan('NotifySite', ['path' => 'ExternalGame/ExternalGameSend', 'data' => ["data" => $data]]);
        } else {

            //接收三方平台返回信息
            $client_id = isset($data['data']['client_id']) ? $data['data']['client_id'] : '';
            $status = $data['status'];
            $res = [
                'status' => $status,
                'msg' => ''
            ];
            $websocketAdapter = new \Lib\Websocket\Adapter($config->cache_daemon);
            if ($status == 200) {
                $result = $data['data'];
                $interface_key = isset($result['interface_key']) ? $result['interface_key'] :'';
                switch ($interface_key) {
                    case 'my':
                        $res['balance'] = $data['data']['balance'];
                        break;
                    case 'ag':

                        break;
                    case 'lb':

                        break;
                    case 'ky':
                        if($data['return_data']['d']['code'] == 0){
                            $res['balance'] = $data['return_data']['d']['money'];
                        }elseif($data['return_data']['d']['code'] == 35){
                            $res['balance'] = 0;
                        }else{
                            $res['status'] = $data['return_data']['d']['code'];
                        }
                        break;
                    default:

                        break;
                }
            }

            //推送客户端
            $websocketAdapter->send($client_id,'ExternalGame/GameBalance?'.$interface_key, $res);
        }
    }
}