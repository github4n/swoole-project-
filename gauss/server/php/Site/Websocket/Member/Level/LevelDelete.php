<?php

namespace Site\Websocket\Member\Level;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * LevelDelete class.
 *
 * @description   会员层级-删除会员层级
 * @Author  rose
 * @date   2019-05-08
 * @links  Member/Level/LevelDelete {"level_id":19}
 * @tags  参数：type(1自动升级,2手工升级) ,level_key:等级名称,
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class LevelDelete extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_layer_delete', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $mysql_staff = $config->data_staff;
        $reportMysql = $config->data_report;
        $layer_id = $data['level_id'];
        if (!is_numeric($layer_id)) {
            $context->reply(['status' => 203, 'msg' => '等级编号类型不正确']);

            return;
        }
        //查找会员层级人数
        $sql = 'SELECT user_id FROM user_info_intact WHERE layer_id=:layer_id';
        $param = [':layer_id' => $layer_id];
        try {
            $total = $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        if ($total > 0) {
            $context->reply(['status' => 204, 'msg' => '该层级不可删除，请将该层级人员移动至其他层级，两个月之后该层级可删除']);

            return;
        }
        //查询该层级是否存在报表数据
        $sql = 'select user_id from daily_user where layer_id = :layer_id';
        try {
            $user_total = $reportMysql->execute($sql, [':layer_id' => $layer_id]);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        if ($user_total > 0) {
            $context->reply(['status' => 204, 'msg' => '该层级不可删除，请将该层级人员移动至其他层级，两个月之后该层级可删除']);

            return;
        }

        $sql = 'DELETE FROM layer_permit WHERE layer_id = :layer_id';
        $param = [':layer_id' => $layer_id];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        $sql = 'DELETE FROM layer_info WHERE layer_id = :layer_id';
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '删除失败']);
            throw new \PDOException($e);
        }
        //删除会员层级并删除体系对应的管理层级
        $sql = 'delete from staff_layer where layer_id=:layer_id';
        $mysql_staff->execute($sql, [':layer_id' => $layer_id]);
        //删除相对应的通道的管理id
        $sqls = 'delete from deposit_route_layer where layer_id=:layer_id';
        $mysql_staff->execute($sqls, [':layer_id' => $layer_id]);
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip=:client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'user_layer_delete',
            ':detail' => '删除会员层级'.$layer_id,
        ];
        $mysql_staff->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '删除成功',
        ]);
        //更新redis信息
        $cache = $config->cache_site;
        $sql = 'select layer_name,layer_id from layer_info where layer_type<100';
        $userLayer = iterator_to_array($mysql->query($sql));
        $cache->hset('LayerList', 'userLayer', json_encode($userLayer));

        $sql = 'select layer_id,layer_name from layer_info';
        $allLayer = iterator_to_array($mysql->query($sql));
        $cache->hset('LayerList', 'allLayer', json_encode($allLayer));
    }
}
