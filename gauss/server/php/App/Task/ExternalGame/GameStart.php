<?php
/**
 * Created by PhpStorm.
 * User: nathan,lucy
 * Date: 19-3-11,,19-3-27
 * Time: 下午12:38
 */
namespace App\Task\ExternalGame;
use Lib\Config;
use Lib\Task\Adapter;
use Lib\Task\Context;
use Lib\Task\IHandler;

class GameStart implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['data' => $data] = $context->getData();

        //设置了action Key为发送任务请求
        if (isset($data['action'])) {
            $taskAdapter = new Adapter($config->cache_daemon);
            $taskAdapter->plan('NotifySite',['path' => 'ExternalGame/ExternalGameSend', 'data'=>["data"=>$data]]);
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
                    case 'fg':
                        $openid = $result['fg_openid'];
                        $member_code = $result['fg_member_code'];
                        $user_id = $result['user_id'];
                        $password = $result['fg_password'];
                        $mysqlUser = $config->data_user;
                        $fg_player = '';
                        $check_sql = "select fg_openid from user_fungaming where user_id = '$user_id'";
                        foreach ($mysqlUser->query($check_sql) as $val) {
                            //不存在游戏账户则创建
                            $fg_player = $val['fg_openid'];
                        }
                        if (empty($fg_player)) {
                            $sql = "INSERT INTO user_fungaming SET user_id = '$user_id',fg_openid ='$openid',fg_member_code='$member_code',fg_password='$password'";
                            $mysqlUser->execute($sql);
                        }
                        $res['game_url'] = $result['game_url'];
                        $res['name'] = $result['name'];
                        $res['token'] = $result['token'];
                        $res['meta'] = $result['meta'];
                        break;
                    case 'ag':

                        break;
                    case 'ky':
                        if(isset($data['return_data'])){
                            if(isset($data['return_data']['d'])){
                                $arr=$data['return_data']['d'];
                                if($arr['code']!=0){
                                    $res['status']=$arr['code'];
                                    $res['msg']='返回出错';
                                }else{
                                   $res['url'] =$arr['url'];
                                }
                            }
                        }
                        break;
                    case 'lb':

                        break;
                    default:

                        break;
                }
            }
            //推送客户端
            $websocketAdapter->send($client_id,'ExternalGame/GameStart', $res);
        }
    }
}