<?php
namespace Site\Task\Fg;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: GetBalance.php
 * @description   fg 获取余额的第三方接口的site任务
 * @Author  lucy
 * @date  2019-04-08
 * @links  Plat\Http\Fg\Balance.php
 * @returndata {}
 * @modifyAuthor
 * @modifyTime
 */
class GetBalance implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        //转发plat请求至站点
        $datas = $context->getData();

        $username = $datas['username'];
        $clientId = $datas['clientId'];
        $adapter = $context->getAdapter();
        //查询余额
        $mysql = $config->data_user;

        $sql = 'select user_id from user_fungaming where fg_member_code=:fg_member_code';

        $params = [':fg_member_code' => $username];
        foreach ($mysql->query($sql, $params) as $row) {
            $user_id = $row['user_id'];
        }

        if (!isset($user_id) || empty($user_id)) {
            $data = [
                'res' => [
                    "state" => 105,
                    "message" => "用户不存在",
                    "data" => ''
                ],
                'clientId' => $clientId

            ];
        } else {
            $sql = 'SELECT deal_key FROM user_info WHERE user_id=:user_id';
            $param = [':user_id' => $user_id];
            $money = 0;
            foreach ($mysql->query($sql, $param) as $row) {
                $dealKey = $row['deal_key'];
                $mysql = $config->__get('data_' . $dealKey);
                $sql = 'SELECT money FROM account WHERE user_id=:user_id';
                foreach ($mysql->query($sql, $param) as $row) {
                    $money = floor($row['money'] * 100);
                }
            }
            $Common = new Common();
            $walletTime = $Common->utc_time();

            $data = [
                'res' => [
                    "state" => 0,
                    "message" => "ok",
                    'data' => [
                        'username' => $username,
                        "balance" => $money,
                        'walletTime' => $walletTime,
                    ]
                ],
                'clientId' => $clientId
            ];
        }

        $adapter->plan('NotifyPlat', ['path' => 'Fg/GetBalance', 'data' => $data]);
    }
}
