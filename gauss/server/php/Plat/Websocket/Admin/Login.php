<?php

namespace Plat\Websocket\Admin;

use Lib\Config;
use Lib\Websocket\Context;
use Lib\Websocket\IHandler;

/**
 * Login class.
 *
 * @description   登录
 * @Author  avery
 * @date  2019-05-08
 *  admin_name:登录名, admin_password:登录密码
 * @links  Admin/Login {"admin_name":"admin","admin_password":"123456"}
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class Login implements IHandler
{
    public function onReceive(Context $context, Config $config)
    {
        if (!empty($context->getInfo('adminId'))) {
            $context->reply(['status' => 207, 'msg' => '已登录 请勿重复请求']);

            return;
        }
        $cache = $config->cache_plat;
        $websocketAdapter = $context->getAdapter();
        $data = $context->getData();
        $adminKey = trim($data['admin_name']);
        $password = trim($data['admin_password']);

        // 为空判断
        if (empty($adminKey)) {
            $context->reply(['status' => 201, 'msg' => '账号不能为空']);

            return;
        }
        if (empty($password)) {
            $context->reply(['status' => 202, 'msg' => '密码不能为空']);

            return;
        }
        $mysql = $config->data_admin;
        $sql = 'CALL admin_auth_verify(:adminKey, :password)';
        $params = [':adminKey' => $adminKey, ':password' => $password];

        $adminAuth = [];
        foreach ($mysql->query($sql, $params) as $row) {
            $adminAuth = $row;
        }
        if (empty($adminAuth)) {
            $context->reply(['status' => 203, 'msg' => '账号或密码错误']);

            return;
        }
        //存放用户的登录缓存
        $clientId = $context->clientId();
        $adminId = $adminAuth['admin_id'];
        // 获取用户信息
        $sql = 'SELECT admin_name, add_time,role_map,admin_key FROM admin_info_intact WHERE admin_id = :admin_id';
        $params = [':admin_id' => $adminId];
        $userInfo = [];
        foreach ($mysql->query($sql, $params) as $row) {
            $userInfo['admin_name'] = $row['admin_name'];
            $userInfo['admin_key'] = $row['admin_key'];
            $userInfo['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
            foreach (json_decode($row['role_map']) as $item) {
                $userInfo['role_name'] = $item;
            }
        }
        if ($adminKey !== $userInfo['admin_key']) {
            $context->reply(['status' => 300, 'msg' => '请输入正确的登录账号']);

            return;
        }
        //删除之前的登录信息
        $sql = 'select client_id from admin_session where admin_id = :admin_id';
        $client_list = iterator_to_array($mysql->query($sql, $params));
        if (!empty($client_list)) {
            foreach ($client_list as $key => $val) {
                //删除相关的redis信息

                $clientKey = 'websocket:client:'.$val['client_id'];
                $cache->hdel($clientKey, 'adminId');
                $cache->hdel($clientKey, 'adminAuth');
                $cache->hdel($clientKey, 'adminKey');

                $websocketAdapter->send($val['client_id'], 'Login/Notice', ['status' => 800, 'msg' => '该账号已在其他地方登录']);
            }
            $sql = 'delete from admin_session where admin_id = :admin_id';
            $mysql->execute($sql, $params);
        }
        //记录登录缓存
        try {
            $session_sql = 'INSERT INTO admin_session SET client_id = :client_id, admin_id = :admin_id, login_time = :login_time, client_ip=:client_ip, user_agent=:user_agent';
            $params = [
                ':client_id' => $clientId,
                ':admin_id' => $adminId,
                ':login_time' => time(),
                ':client_ip' => ip2long($context->getClientAddr()),
                ':user_agent' => sha1($context->getInfo('User-Agent')),
            ];
            $mysql->execute($session_sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '登录失败']);
            throw new \PDOException('sql run error'.$e);
        }
        //统计在线人数
        $sql = 'SELECT admin_id FROM admin_session where lose_time=0';
        $online_num = $mysql->execute($sql);

        // 获取用户权限
        $sql = 'SELECT admin_id, operate_key FROM admin_operate WHERE admin_id = :admin_id';
        $params = [':admin_id' => $adminId];
        $authKey = [];
        foreach ($mysql->query($sql, $params) as $row) {
            array_push($authKey, $row['operate_key']);
        }
        // 更新登录时间
        try {
            $sql = 'UPDATE admin_info SET login_time = :login_time, login_ip = :login_ip WHERE admin_id = :admin_id';
            $params = [
                ':admin_id' => $adminId,
                ':login_time' => time(),
                ':login_ip' => ip2long($context->getClientAddr()),
            ];
            $mysql->execute($sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '登录失败']);
            throw new \PDOException($e);
        }
        //获取resume_key
        $sql = 'SELECT resume_key FROM admin_session WHERE client_id=:client_id';
        $param = [':client_id' => $clientId];
        $info = array();
        foreach ($mysql->query($sql, $param) as $row) {
            $info = $row;
        }
        if (empty($info)) {
            $context->reply(['status' => 400, 'msg' => '登录失败']);

            return;
        } else {
            $resume_key = $info['resume_key'];
        }

        $context->setInfo('adminId', $adminId);
        $context->setInfo('adminAuth', json_encode($authKey));
        $context->setInfo('adminKey', $adminKey);
        $context->reply(['status' => 200, 'msg' => '登录成功', 'online_num' => $online_num, 'resume_key' => $resume_key, 'userinfo' => $userInfo, 'authkey' => $authKey]);

        // 记录日志
        $serverHost = $context->getServerHost();
        $clientAddr = $context->getClientAddr();
        $userAgent = $context->getInfo('User-Agent');
        $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
        $params = [
            ':admin_id' => $adminId,
            ':operate_key' => 'self_login',
            ':detail' => '服务器'.$serverHost.';登录'.'ip'.$clientAddr.',User-Agent:'.$userAgent,
        ];
        $mysql->execute($sql, $params);
        $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
        $sql = 'SELECT admin_id FROM admin_session where lose_time=0';
        $total = $mysql->execute($sql);
        $taskAdapter->plan('NotifyClient', ['path' => 'Admin/Online', 'data' => ['online_num' => $total]]);
    }
}
