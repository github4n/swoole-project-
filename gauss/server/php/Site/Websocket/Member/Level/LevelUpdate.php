<?php

namespace Site\Websocket\Member\Level;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * LevelUpdate class.
 *
 * @description   会员层级设置-编辑自动层级修改的信息
 * @Author  blake
 * @date  2019-05-08
 * @links  Member/Level/LevelUpdate {"level_id":20,"level_name":"超级会员2","deposit":400,"bet":200,"auth":["insert_into","update_up","deletes"]}
 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class LevelUpdate extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_layer_update', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $mysql_staff = $config->data_staff;
        $layer_id = $data['level_id'];
        $level_name = $data['level_name'];
        $auth = $data['auth'];
        $deposit = $data['deposit'];
        $bet = $data['bet'];
        if (empty($layer_id)) {
            $context->reply(['status' => 204, 'msg' => '请选择修改的层级']);

            return;
        }
        if (empty($level_name)) {
            $context->reply(['status' => 205, 'msg' => '请输入等级名称']);

            return;
        }
        // 验证规则
        $preg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{2,20}$/u';
        if (!preg_match($preg, $level_name)) {
            $context->reply(['status' => 205, 'msg' => '等级名称,请不要超过2-20位,包括英文,数字,汉字']);

            return;
        }
        if (!is_numeric($layer_id)) {
            $context->reply(['status' => 207, 'msg' => '请选择修改层级']);

            return;
        }
        if (!is_numeric($deposit)) {
            $context->reply(['status' => 208, 'msg' => '请设置金额']);

            return;
        }
        if (!is_numeric($bet)) {
            $context->reply(['status' => 209, 'msg' => '请设置金额']);

            return;
        }
        if (!empty($auth)) {
            if (!is_array($auth)) {
                $context->reply(['status' => 208, 'msg' => '请选择权限']);

                return;
            }
        }
        $info = [];
        //查询层级名称一样
        $sql = 'SELECT layer_name FROM layer_info WHERE layer_id != :layer_id and layer_name=:layer_name';
        try {
            foreach ($mysql->query($sql, [':layer_id' => $layer_id, ':layer_name' => $level_name]) as $row) {
                $info = $row;
            }
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '添加失败']);
            throw new \PDOException($e);
        }
        if (!empty($info)) {
            $context->reply(['status' => 207, 'msg' => '该层级已被创建,请重新输入']);

            return;
        }

        $sql = 'SELECT layer_name  FROM layer_info where min_deposit_amount=:min_deposit_amount and min_bet_amount=:min_bet_amount';
        $layer_info = [];
        foreach ($mysql->query($sql, [':min_deposit_amount' => $deposit, ':min_bet_amount' => $bet]) as $row) {
            $layer_info = $row['layer_name'];
        }
        if (!empty($layer_info)) {
            $context->reply(['status' => 208, 'msg' => '自动升级条件不可相同']);

            return;
        }

        $sql = 'UPDATE layer_info SET layer_name=:layer_name, min_deposit_amount=:min_deposit_amount, min_bet_amount=:min_bet_amount WHERE layer_id=:layer_id';
        $param = [
            ':layer_name' => $level_name,
            ':min_deposit_amount' => $deposit,
            ':min_bet_amount' => $bet,
            ':layer_id' => $layer_id,
        ];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
            throw new \PDOException($e);
        }
        //修改层级的权限信息（删除之前的）
        $sql = 'DELETE FROM layer_permit WHERE layer_id=:layer_id';
        $param = [':layer_id' => $layer_id];
        try {
            $mysql->execute($sql, $param);
        } catch (\PDOException $e) {
            $context->reply(['status' => 400, 'msg' => '修改失败']);
        }
        foreach ($auth as $item) {
            $sql = 'INSERT INTO layer_permit SET layer_id=:layer_id,operate_key=:operate_key';
            $param = [':layer_id' => $layer_id, ':operate_key' => $item];
            try {
                $mysql->execute($sql, $param);
            } catch (\PDOException $e) {
                $context->reply(['status' => 400, 'msg' => '修改失败']);

                return;
            }
        }

        //记录日志信息
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip=:client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'user_layer_update',
            ':detail' => '修改会员层级'.$layer_id,
        ];
        $mysql_staff->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '修改成功',
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
