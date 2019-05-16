<?php

namespace Site\Websocket\AgentRebate\AgentLayer;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * ManualAdd.php.
 *
 * @description   代理层级设置--新增手工代理层级
 * @Author  rose
 * @date  2019-04-07
 * @links 参数：AgentRebate/AgentLayer/ManualAdd {"level_name":"测试手工代理层级","auth":["insert","delete","select"]}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
class ManualAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_layer_insert', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $staffId = $context->getInfo('StaffId');
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $level_name = $data['level_name'];
        $auth = $data['auth'];
        if (empty($level_name)) {
            $context->reply(['status' => 204, 'msg' => '等级名称不能为空']);

            return;
        }
//        if(empty($auth)){
//            $context->reply(["status"=>205,"msg"=>"分配的权限不能为空"]);
//            return;
//        }
        if (!is_array($auth)) {
            $context->reply(['status' => 208, 'msg' => '权限参数类型错误']);

            return;
        }
        //查询层级名称一样
        $sql = 'SELECT layer_name FROM layer_info where layer_name=:layer_name';
        try {
            foreach ($mysql->query($sql, [':layer_name' => $level_name]) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '添加失败']);
            throw new \PDOException($e);
        }
        if (!empty($info)) {
            $context->reply(['status' => 207, 'msg' => '名称已经存在']);

            return;
        }

        //等级信息
        $sql = 'INSERT INTO layer_info SET layer_name=:layer_name,layer_type=:layer_type';
        $param = [
            ':layer_name' => $level_name,
            ':layer_type' => 101,
        ];
        try {
            $mysql->execute($sql, $param);
            $sql = 'SELECT last_insert_id() as layer_id';
            foreach ($mysql->query($sql) as $row) {
                $layer_id = $row['layer_id'];
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '新增失败']);
            throw new \PDOException($e);
        }
        //权限信息
        $layer_info = [];
        foreach ($auth as $item) {
            $layer_info[] = [
                'layer_id' => $layer_id,
                'operate_key' => $item,
            ];
        }
        $mysql->layer_permit->load($layer_info, [], 'replace');
        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip= :client_ip';
        $params = [
            ':staff_id' => $staffId,
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'broker_layer_insert',
            ':detail' => '新增手动升级代理层级'.$layer_id,
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '新增成功',
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
