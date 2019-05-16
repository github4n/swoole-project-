<?php

namespace Plat\Websocket\Admin;

use Lib\Config;
use Lib\Websocket\Context;
use Lib\Websocket\IHandler;

/**
 * ResumeLogin class.
 *
 * @description   回复登录
 * @Author  avery
 * @date  2019-05-08
 * @links  Admin/Login {"admin_name":"admin","admin_password":"123456"}
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class ResumeLogin implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        $data = $context->getData();
        $resume_key = $data['resume_key'];
        if (empty($resume_key)) {
            $context->reply(['status' => 202, 'msg' => '恢复的key不能为空']);

            return;
        }
        $client_ip = ip2long($context->getClientAddr());
        $user_agent = sha1($context->getInfo('User-Agent'));
        $sql = 'SELECT * FROM admin_session WHERE resume_key=:resume_key AND client_ip=:client_ip AND user_agent=:user_agent and lose_time > :lose_time';
        $params = [
            ':resume_key' => $resume_key,
            ':client_ip' => $client_ip,
            ':user_agent' => $user_agent,
            ':lose_time' => time() - 600,
        ];
        $mysql = $config->data_admin;
        $info = array();
        foreach ($mysql->query($sql, $params) as $row) {
            $info = $row;
        }
        if (empty($info)) {
            $context->reply(['status' => 400, 'msg' => '恢复登录失败']);

            return;
        } else {
            //更新缓存信息
            try {
                //用户掉线10分钟内 重新上线更新用户的信息
                $sql = 'UPDATE admin_session SET client_id=:client_id,lose_time=:lose_time WHERE resume_key = :resume_key';
                $param = [':client_id' => $context->clientId(), ':resume_key' => $resume_key, ':lose_time' => 0];
                $mysql->execute($sql, $param);
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '恢复失败请重新登录']);
                throw new \PDOException('sql run error'.$e);
            }
            // 获取用户信息
            $sql = 'SELECT admin_key,admin_name, add_time,role_map FROM admin_info_intact WHERE admin_id = :admin_id';
            $params = [':admin_id' => $info['admin_id']];
            $userInfo = [];
            foreach ($mysql->query($sql, $params) as $row) {
                $userInfo['admin_name'] = $row['admin_name'];
                $userInfo['admin_key'] = $row['admin_key'];
                $userInfo['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
                foreach (json_decode($row['role_map']) as $item) {
                    $userInfo['role_name'] = $item;
                }
            }
            // 获取用户权限
            $sql = 'SELECT admin_id, operate_key FROM admin_operate WHERE admin_id = :admin_id';
            $params = [':admin_id' => $info['admin_id']];
            $authKey = [];
            foreach ($mysql->query($sql, $params) as $row) {
                array_push($authKey, $row['operate_key']);
            }
            //缓存基本信息
            $context->setInfo('adminId', $info['admin_id']);
            $context->setInfo('adminAuth', json_encode($authKey));
            $context->setInfo('adminKey', $userInfo['admin_key']);
            //记录恢复日志
            $serverHost = $context->getServerHost();
            $clientAddr = $context->getClientAddr();
            $userAgent = $context->getInfo('User-Agent');
            $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $params = [
                ':admin_id' => $info['admin_id'],
                ':operate_key' => 'self_login',
                ':detail' => '服务器'.$serverHost.';恢复登录'.'ip'.$clientAddr.',User-Agent:'.$userAgent,
            ];
            $mysql->execute($sql, $params);
            $context->reply(['status' => 200, 'msg' => '恢复登录成功', 'resume_key' => $resume_key, 'userinfo' => $userInfo, 'authkey' => $authKey]);
            $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
            $sql = 'SELECT admin_id FROM admin_session where lose_time=0';
            $total = $mysql->execute($sql);
            $taskAdapter->plan('NotifyClient', ['path' => 'Admin/Online', 'data' => ['online_num' => $total]]);
        }
    }
}
