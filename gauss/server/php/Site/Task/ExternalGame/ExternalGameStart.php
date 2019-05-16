<?php

namespace Site\Task\ExternalGame;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
use Lib\Task\Adapter;

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
class ExternalGameStart implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $result = $context->getData();

        $status = isset($result['status']) ? $result['status'] : '';
        $data = isset($result['data']) ? $result['data'] : '';
        $interface_key = isset($data['interface_key']) ? $data['interface_key'] : '';
        if (empty($interface_key)) {
            $interface_key = $data['data']['interface_key'] ?? '';
        }

        $res = [
            'status' => 401,
            'msg' => '启动游戏失败',
        ];
        if (!empty($interface_key)) {
            switch ($interface_key) {
                case 'fg':
                    $user_id = isset($data['user_id']) ? $data['user_id'] : '';
                    $user_key = isset($data['user_key']) ? $data['user_key'] : '';
                    $member_code = isset($data['fg_member_code']) ? $data['fg_member_code'] : '';
                    $password = isset($data['fg_password']) ? $data['fg_password'] : '';
                    $openid = isset($data['fg_openid']) ? $data['fg_openid'] : '';
                    $game_url = isset($data['game_url']) ? $data['game_url'] : '';
                    $meta = isset($data['meta']) ? $data['meta'] : '';
                    $token = isset($data['token']) ? $data['token'] : '';
                    $mysqlUser = $config->data_user;
                    $user_info_sql = 'select deal_key from user_info_intact where user_id = :user_id and user_key= :user_key';
                    $userRes = $mysqlUser->execute($user_info_sql, [':user_id' => $user_id, ':user_key' => $user_key]);
                    if ($userRes == 1) {
                        $check_sql = 'select fg_openid from user_fungaming where user_id = :user_id';
                        $fg_player = $mysqlUser->execute($check_sql, [':user_id' => $user_id]);
                        if ($fg_player == 0) {
                            $sql = 'INSERT INTO user_fungaming SET user_id = :user_id,fg_openid = :openId,fg_member_code= :member_code,fg_password= :password';
                            $mysqlUser->execute($sql, [':user_id' => $user_id, ':openId' => $openid, ':member_code' => $member_code, ':password' => $password]);
                        }
                    }
                    $res['status'] = $status;
                    $res['msg'] = '成功';
                    $res['user_id'] = $user_id;
                    $res['url'] = $game_url.'&token='.$token;
                    $res['meta'] = $meta;
                    break;
                case 'ky':
                    $res['status'] = $data['status'];
                    $res['msg'] = $data['msg'];
                    $res['user_id'] = $data['data']['user_id'];
                    $return_url=$data['data']['return_url']??'';
                    if ($data['status'] == 200) {
                        if (isset($data['return_data']['d'])) {
                            $arr = $data['return_data']['d'];
                            $res['url'] = $arr['url'];
                            if(!empty($return_url)){
                                $res['url'].="&backUrl=$return_url&jumpType=2";
                            }
                        }
                    }

                    break;
                case 'lb':
                    break;
                case 'ag':
                    break;
            }
        }
        $adapter = new Adapter($config->cache_daemon);
        $adapter->plan('NotifyApp', ['path' => 'ExternalGame/GameStart', 'data' => ['data' => $res]], time(), 1);
    }
}
