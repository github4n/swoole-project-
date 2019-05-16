<?php
/**
 * Created by PhpStorm.
 * User: lucy
 * Date: 19-3-15  04-19
 * Time: 上午8:55  将所有的sql绑定变量.
 */

namespace Site\Task\Fg;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;
use Lib\Task\Adapter;

class WinLoss implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data = $context->getData();
        $clientId = $data['clientId'];

        $winLossList = $data['winLossList'];
        $taskAdapter = new Adapter($config->cache_daemon);
        //查询账户余额
        $mysql = $config->data_user;
        $sql = 'SELECT user_id FROM user_fungaming WHERE fg_member_code=:fg_member_code';
        $param = [':fg_member_code' => $winLossList['username']];
        $userId = '';
        foreach ($mysql->query($sql, $param) as $row) {
            $userId = $row['user_id'];
        }
        if (!$userId) {
            $res = array(
                'data' => array(
                    'state' => 105,
                    'message' => '用户不存在',
                ),
                'clientId' => $clientId,
            );
            $taskAdapter->plan('NotifyPlat', ['path' => 'Fg/WinLoss', 'data' => $res]);

            return;
        }
        $sql = 'SELECT deal_key FROM user_info WHERE user_id=:user_id';
        $param = [':user_id' => $userId];
        $dealKey = '';
        foreach ($mysql->query($sql, $param) as $row) {
            $dealKey = $row['deal_key'];
        }
        $mysql = $config->__get('data_'.$dealKey);
        $sql = 'SELECT money,account_name,layer_id,user_key FROM account WHERE user_id=:user_id';
        $param = [':user_id' => $userId];
        $balance = '';
        $accountName = '';
        $layerId = '';
        $userKey = '';
        foreach ($mysql->query($sql, $param) as $row) {
            $balance = $row['money'];
            $accountName = $row['account_name'];
            $layerId = $row['layer_id'];
            $userKey = $row['user_key'];
        }

        //判断结算id是否存在
        $sql = "SELECT export_serial FROM external_export_success WHERE success_data->'$.betId' = :betId";
        $exportSerial = '';
        $params = [':betId' => $winLossList['betId']];
        foreach ($mysql->query($sql, $params) as $row) {
            $exportSerial = $row['export_serial'];
        }

        if (!$exportSerial) {
            $res = array(
                'data' => array(
                    'state' => 105,
                    'message' => '注单号不存在',
                ),
                'clientId' => $clientId,
            );
            $taskAdapter->plan('NotifyPlat', ['path' => 'Fg/WinLoss', 'data' => $res]);

            return;
        }

        //判断是否重复结算，重复结算的返回交易流水号
        $fgWinLossList = json_encode($winLossList);
        $sql = "SELECT import_serial FROM external_import_success WHERE success_data->'$.betId'=:betId";
        $importSerial = '';
        foreach ($mysql->query($sql, $params) as $row) {
            $importSerial = $row['import_serial'];
        }
        if ($importSerial) {
            $sql = 'SELECT success_deal_serial FROM external_import_success WHERE import_serial=:import_serial';
            $param = [':import_serial' => $importSerial];
            $successDealSerial = '';
            foreach ($mysql->query($sql, $param) as $row) {
                $successDealSerial = $row['success_deal_serial'];
            }
<<<<<<< .mine
            $data = [['uuid' => $successDealSerial]];
||||||| .r10673
            $data = [
<<<<<<< .mine
                [
                    'uuid' => $successDealSerial
                ]

            ];
=======
            $data = [
                'uuid' => $successDealSerial
||||||| .r13131
                'uuid' => $successDealSerial
=======
                'uuid' => $successDealSerial,
>>>>>>> .r15141
            ];
>>>>>>> .r10887
            $res = array(
                'data' => array(
                    'state' => 0,
                    'message' => '已结算',
                    'data' => $data,
                ),
                'clientId' => $clientId,
            );
            $taskAdapter->plan('NotifyPlat', ['path' => 'Fg/WinLoss', 'data' => $res]);

            return;
        } else {
            $time = time();
            $param = array(
                'user_id' => $userId,
                'user_key' => $userKey,
                'account_name' => $accountName,
                'layer_id' => $layerId,
                'external_type' => 'fg',
                'launch_data' => $fgWinLossList,
                'launch_money' => ($winLossList['prize'] / 100),
                'launch_time' => $time,
            );
            $str = '';
            $param1 = [];
            foreach ($param as $key => $val) {
                $str .= $key.'=:'.$key.',';
                $param1[':'.$key] = $val;
            }

            $str = trim($str, ',');
            $sql = 'INSERT INTO external_import_launch SET '.$str;
            $importSerial = '';
            try {
                $mysql->execute($sql, $param1);
                $sql = 'SELECT serial_last("external_import") as import_serial';
                foreach ($mysql->query($sql) as $row) {
                    $importSerial = $row['import_serial'];
                }
            } catch (\PDOException $e) {
                $taskAdapter->plan('NotifyPlat', ['path' => 'Fg/WinLoss', 'clientId' => $clientId, 'data' => ['state' => 2, 'message' => '获取失败']]);
                throw new \PDOException($e);
            }
            $sql = 'INSERT INTO external_import_success SET import_serial = :import_serial,success_time = :success_time,success_data = :fgWinLossList';
            $params = [
                ':import_serial' => $importSerial,
                ':success_time' => $time,
                ':fgWinLossList' => $fgWinLossList,
            ];
            $mysql->execute($sql, $params);
            $sql = 'SELECT success_deal_serial FROM external_import_success WHERE import_serial=:import_serial';
            $param = [':import_serial' => $importSerial];
            $successDealSerial = '';
            foreach ($mysql->query($sql, $param) as $row) {
                $successDealSerial = $row['success_deal_serial'];
            }

            date_default_timezone_set('UTC');
            $timestamp = new \DateTime();
            $timeStr = $timestamp->format(DATE_ISO8601);
            $data = [
<<<<<<< .mine
                'data' => [[
                        'betId' => $winLossList['betId'],
                        'uuid' => $successDealSerial,
                        'balance' => $balance*100+$winLossList['prize'],
                        "msg" => "该注单结算成功",
                        'state' => 0
                ]],
||||||| .r10673
                'data' => [array(
                    [
                        'betId' => $winLossList['betId'],
                        'uuid' => $successDealSerial,
                        'balance' => $balance*100+$winLossList['prize'],
                        "msg" => "该注单结算成功",
                        'state' => 0
                    ],

                )],
=======
                'data' => [array(
                    'betId' => $winLossList['betId'],
                    'uuid' => $successDealSerial,
                    'balance' => floor($balance * 100 + $winLossList['prize']),
                    'msg' => '该注单结算成功',
                    'state' => 0,
                )],
<<<<<<< .mine
>>>>>>> .r10887
                'walletTime' => $timeStr
||||||| .r13131
                'walletTime' => $timeStr
=======
                'walletTime' => $timeStr,
>>>>>>> .r15141
            ];
            $res = array(
                'data' => array(
                    'state' => 0,
                    'message' => '已结算',
                    'data' => $data,
                ),
                'clientId' => $clientId,
            );
            $b = json_encode($data);
            fwrite(STDERR, date('Y-m-d H:i:s').'-'.time().'-'.$clientId.'-'.'fg_WinLoss_plat_task_start'.$b."\n");
            $taskAdapter->plan('NotifyPlat', ['path' => 'Fg/WinLoss', 'data' => $res], time(), 1);
            $taskAdapter->plan('User/Balance', ['user_list' => [$userId]], time(), 6);
        }
    }
}
