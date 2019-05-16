<?php

namespace Site\Websocket\AgentRebate\AgentLayer;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * NewLayerEdit.php.
 *
 * @description   代理层级设置--保存新代理的信息
 * @Author  rose
 * @date  2019-04-07
 * @links  参数：AgentRebate/AgentLayer/NewLayerUpdate {"layer_id":11,"days":20,"auth":["insert_into","delete_from","select_from"]}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
class NewLayerUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_layer_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $layer_id = $data['layer_id'];
        $days = $data['days'];
        $auth = $data['auth'];
        if (!is_numeric($layer_id)) {
            $context->reply(['status' => 204, 'msg' => '编辑的代理层级类型不正确']);

            return;
        }
        if (!is_numeric($days)) {
            $context->reply(['status' => 205, 'msg' => '天数参数类型错误']);

            return;
        }
        if (!empty($auth)) {
            if (!is_array($auth)) {
                $context->reply(['status' => 208, 'msg' => '权限参数类型错误']);

                return;
            }
        }

        //修改层级的权限信息（删除之前的）
        $sql = 'DELETE FROM layer_permit WHERE layer_id=:layer_id';
        $param = [':layer_id' => $layer_id];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
        }
        $layer_info = [];
        foreach ($auth as $item) {
            $layer_info[] = [
                'layer_id' => $layer_id,
                'operate_key' => $item,
            ];
        }
        $mysql->layer_permit->load($layer_info, [], 'replace');

        $sql = 'UPDATE layer_info SET max_day=:max_day WHERE layer_id=:layer_id';
        $param = [
            ':max_day' => $days,
            ':layer_id' => $layer_id,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }

        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':operate_key' => 'broker_layer_update',
            ':client_ip' => ip2long($context->getClientAddr()),
            ':detail' => '修改代理层级'.$layer_id,
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '修改成功',
        ]);
        $cache = $config->cache_site;
        $sql = 'select layer_name,layer_id from layer_info where layer_type>100';
        $agentLayer = iterator_to_array($mysql->query($sql));
        $cache->hset('LayerList', 'agentLayer', json_encode($agentLayer));

        $sql = 'select layer_id,layer_name from layer_info';
        $allLayer = iterator_to_array($mysql->query($sql));
        $cache->hset('LayerList', 'allLayer', json_encode($allLayer));
    }
}
