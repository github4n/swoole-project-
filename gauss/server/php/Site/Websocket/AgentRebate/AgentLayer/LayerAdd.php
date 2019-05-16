<?php

namespace Site\Websocket\AgentRebate\AgentLayer;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * LayerAdd.php.
 *
 * @description   代理层级设置--新增代理层级
 * @Author  rose
 * @date  2019-04-07
 * @links  AgentRebate/AgentLayer/LayerAdd {"level_name":"测试代理层级","deposit":"2000","bet":"3000","auth":["insert","delete","select"],"min_deposit_user":10}
 * @modifyAuthor   Luis
 * @modifyTime  2019-04-23
 */
class LayerAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('broker_layer_insert', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $StaffGrade = $context->getInfo('StaffGrade');
        $staffId = $context->getInfo('StaffId');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $cache = $config->cache_site;
        $level_name = $data['level_name'];
        $deposit = $data['deposit'];
        $bet = $data['bet'];
        $min_deposit_user = $data['min_deposit_user']; //下级人数
        $auth = $data['auth'];
        if (empty($level_name)) {
            $context->reply(['status' => 204, 'msg' => '等级名称不能为空']);

            return;
        }
        //新增自动升级
        if (!is_numeric($deposit) || $deposit < 0 || $deposit > 99900000000) {
            $context->reply(['status' => 206, 'msg' => '存款总额不正确或超出正常范围']);

            return;
        }
        if (!is_numeric($bet) || $bet < 0 || $bet > 99900000000) {
            $context->reply(['status' => 207, 'msg' => '投注总额不正确或超出正常范围']);

            return;
        }
        if (!is_numeric($min_deposit_user) || $min_deposit_user < 0 || $min_deposit_user > 99900000000) {
            $context->reply(['status' => 208, 'msg' => '下级人数参数错误或超出正常范围']);

            return;
        }
        if (!empty($auth)) {
            if (!is_array($auth)) {
                $context->reply(['status' => 208, 'msg' => '权限参数类型错误']);

                return;
            }
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
        $sql = 'INSERT INTO layer_info SET layer_name=:layer_name,layer_type=:layer_type,min_deposit_amount=:min_deposit_amount,min_bet_amount=:min_bet_amount,min_deposit_user=:min_deposit_user';
        $param = [
            ':layer_name' => $level_name,
            ':layer_type' => 102,
            ':min_deposit_amount' => $deposit,
            ':min_bet_amount' => $bet,
            ':min_deposit_user' => $min_deposit_user,
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
            ':detail' => '新增代理层级',
        ];
        $staff_mysql = $config->data_staff;
        $staff_mysql->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '新增成功',
        ]);
        $sql = 'select layer_name,layer_id from layer_info where layer_type>100';
        $agentLayer = iterator_to_array($mysql->query($sql));
        $cache->hset('LayerList', 'agentLayer', json_encode($agentLayer));

        $sql = 'select layer_id,layer_name from layer_info';
        $allLayer = iterator_to_array($mysql->query($sql));
        $cache->hset('LayerList', 'allLayer', json_encode($allLayer));
    }
}
