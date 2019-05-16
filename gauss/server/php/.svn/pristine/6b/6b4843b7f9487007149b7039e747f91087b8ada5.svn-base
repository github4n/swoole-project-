<?php

namespace Site\Websocket\Member\Level;

use Lib\Websocket\Context;
use Lib\Config;
use Site\Websocket\CheckLogin;

/**
 * LevelAdd class.
 *
 * @description   会员层级-新增自动升级
 * @Author  rose
 * @date  2019-05-08
 * @links  Member/Level/LevelAdd {"level_name":"测试会员层级","deposit":"2000","bet":"3000","auth":["insert","delete","select"]}

 * @modifyAuthor   blake
 * @modifyTime  2019-05-08
 */
class LevelAdd extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $StaffGrade = $context->getInfo('StaffGrade');
        if ($StaffGrade != 0) {
            $context->reply(['status' => 203, 'msg' => '当前账号没有操作权限']);

            return;
        }
        //检查权限
        $auth = json_decode($context->getInfo('StaffAuth'));
        if (!in_array('user_layer_insert', $auth)) {
            $context->reply(['status' => 202, 'msg' => '你还没有操作权限']);

            return;
        }
        $data = $context->getData();
        $mysql = $config->data_user;
        $mysql_staff = $config->data_staff;
        $level_name = $data['level_name'];
        $deposit = $data['deposit'];
        $bet = $data['bet'];
        $auth = $data['auth'];
        if (empty($level_name)) {
            $context->reply(['status' => 204, 'msg' => '请输入2-20位字符,支持中文,英文和数字']);

            return;
        }
        // 验证规则
        $preg = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{2,20}$/u';
        if (!preg_match($preg, $level_name)) {
            $context->reply(['status' => 205, 'msg' => '等级名称,请输入2-20位字符']);

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
        if (!empty($auth)) {
            if (!is_array($auth)) {
                $context->reply(['status' => 208, 'msg' => '请选择权限']);

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
            $context->reply(['status' => 207, 'msg' => '层级名称已存在,请重新输入']);

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

        //等级信息
        $sql = 'INSERT INTO layer_info SET layer_name=:layer_name,layer_type=:layer_type,min_deposit_amount=:min_deposit_amount,min_bet_amount=:min_bet_amount';
        $param = [
            ':layer_name' => $level_name,
            ':layer_type' => 2,
            ':min_deposit_amount' => $deposit,
            ':min_bet_amount' => $bet,
        ];
        $layer_id = '';
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
        if (!empty($auth)) {
            foreach ($auth as $item) {
                $sql = 'INSERT INTO layer_permit SET layer_id=:layer_id,operate_key=:operate_key';
                $param = [
                    ':layer_id' => $layer_id,
                    ':operate_key' => $item,
                ];
                try {
                    $mysql->execute($sql, $param);
                } catch (\PDOException $e) {
                    $context->reply(['status' => 400, 'msg' => '新增失败']);
                    throw new \PDOException($e);
                }
            }
        }

        //记录日志
        $sql = 'INSERT INTO operate_log SET staff_id=:staff_id, operate_key=:operate_key, detail=:detail,client_ip=:client_ip';
        $params = [
            ':staff_id' => $context->getInfo('StaffId'),
            ':client_ip' => ip2long($context->getClientAddr()),
            ':operate_key' => 'user_layer_insert',
            ':detail' => '新增会员层级'.$layer_id,
        ];
        $mysql_staff->execute($sql, $params);
        $context->reply([
            'status' => 200,
            'msg' => '新增成功',
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
