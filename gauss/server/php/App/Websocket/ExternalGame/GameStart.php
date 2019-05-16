<?php
namespace App\Websocket\ExternalGame;

use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

/*
 * 启动三方游戏
 * @description   app/启动三方游戏接口
 * @Author  nathan 
 * @date  2019-05-08
 * @links  ExternalGame/GameStart 
 * @modifyAuthor   nathan
 * @modifyTime  2019-05-08
 * @param interface_key|string 平台Key game_key|string 游戏Key game_type|string 游戏类型
 * @return  status
 * 200|成功 
 * 400|三方平台Key不能为空
 * 402|启动游戏相关参数错误
 * 405|启动游戏id不能为空
 * 403|返回地址不能为空
 * 其余staus码参见对接文档
 */
class GameStart extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $param = $context->getData();

        $interface_key = isset($param['interface_key']) ? $param['interface_key'] : '';
        if (empty($interface_key)) {
            $context->reply(['status' => 400, 'msg' => '三方平台Key不能为空', $param]);

            return;
        }
        $user_id = $context->getInfo('UserId');
        $user_key = $context->getInfo('UserKey');

        $adapter = new \Lib\Task\Adapter($config->cache_daemon);
        $ip = $context->getClientAddr();

        $return_url = '';
        $Host = $context->getInfo('websocket_url');

        if (!empty($Host)) {
            if (stripos ($Host,'wss')===false) {
                $a=substr($Host,3);
                $return_url = 'https'. $a.'/GamePostMsg';
            } else {
                $a=substr($Host,2);
                $return_url = 'http'.$a.'/GamePostMsg';
            }
        }

        switch ($interface_key) {
            case 'fg':
                $game_key = isset($param['game_key']) ? $param['game_key'] : '';
                $game_type = isset($param['game_type']) ? $param['game_type'] : '';
                $language = isset($param['language']) ? $param['language'] : 'zh-cn';

                //切割server_id
                $server_id = substr($game_key, 3);
                //读取游戏列表json文件
                $file_data = file_get_contents($interface_key.'_game.json', __FILE__);
                $data = json_decode($file_data);
                $game_code = '';
                foreach ($data->data as $game) {
                    //匹配数据
                    if ($server_id == $game->service_id) {
                        $game_code = $game->gamecode;
                    }
                }
                $ipArray = explode('.', $ip);
                if ($ipArray[2] == 1) {
                    $realIp = '27.116.63.114';
                } elseif ($ipArray[2] == 2) {
                    $realIp = '27.116.63.115';
                } else {
                    $realIp = '27.116.63.114';
                }

                if (empty($game_key) || empty($game_code)) {
                    $context->reply(['status' => 401, 'msg' => '游戏参数错误', [$game_key, $game_code]]);

                    return;
                }
                if (empty($game_type) || empty($language) || empty($return_url) || empty($ip) || empty($game_key) || empty($game_code)) {
                    $context->reply(['status' => 402, 'msg' => '启动游戏相关参数错误']);

                    return;
                }
                $params = [
                    'user_id' => $user_id,
                    'user_key' => $user_key,
                    'game_code' => $game_code,
                    'game_type' => $game_type,
                    'language' => $language,
                    'ip' => $realIp,
                    'return_url' => $return_url,
                    'action' => 'launch_game',
                    'interface_key' => $interface_key,
                    'site_action' => 'ExternalGameStart',
                ];

                $adapter->plan('NotifySite', ['path' => 'ExternalGame/ExternalGameSend', 'data' => ['data' => $params]], time(), 1);
                break;
            case 'ag':

                break;
            case 'ky':

                $KindId = isset($param['game_key']) ? $param['game_key'] : '';
                if (empty($KindId)) {
                    $context->reply(['status' => 405, 'msg' => '启动游戏id不能为空']);

                    return;
                }
                if (empty($return_url)) {
                    $context->reply(['status' => 403, 'msg' => '返回地址不能为空']);
                }
                $KindId = explode('_', $KindId)[1];
                $params = [
                    'user_id' => $user_id,
                    'user_key' => $user_key,
                    'KindID' => $KindId,
                    'money' => 0,
                    'ip' => $ip,
                    's' => 0,
                    'return_url' => $return_url,
                    'action' => 'loginKy',
                    'interface_key' => $interface_key,
                    'site_action' => 'ExternalGameStart',
                ];
                $adapter->plan('NotifySite', ['path' => 'ExternalGame/ExternalGameSend', 'data' => ['data' => $params]], time(), 1);
                break;
            case 'lb':

                break;
            default:
                $context->reply(['status' => 404, 'msg' => '非法参数']);

                return;
                break;
        }
    }
}
