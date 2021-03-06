<?php

namespace Plat\Websocket\Admin;

use Lib\Websocket\Context;
use Lib\Config;
use Plat\Websocket\CheckLogin;

/**
 * LoginOut class.
 *
 * @description   退出登录
 * @Author  avery
 * @date  2019-05-08
 * @links  Admin/LoginOut
 * @modifyAuthor   avery
 * @modifyTime  2019-05-08
 */
class LoginOut extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $cache = $config->cache_plat;
        $admin_id = $context->getInfo('adminId');
        $sql = 'DELETE FROM admin_session WHERE admin_id=:admin_id';
        $param = [':admin_id' => $admin_id];
        try {
            $mysql = $config->data_admin;
            $mysql->execute($sql, $param);
            //记录退出日志
            $sql = 'INSERT INTO operate_log SET admin_id = :admin_id, operate_key = :operate_key, detail = :detail';
            $params = [
                ':admin_id' => $admin_id,
                ':operate_key' => 'self_logout',
                ':detail' => '退出登录',
            ];
            $mysql->execute($sql, $params);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '退出失败']);
            throw new \PDOException($e);
        }
        //删除相关的redis信息
        $clientKey = 'websocket:client:'.$context->clientId();
        $cache->hdel($clientKey, 'adminId');
        $cache->hdel($clientKey, 'adminAuth');
        $cache->hdel($clientKey, 'adminKey');
        $context->reply(['status' => 200, 'msg' => '退出成功']);
    }
}
