<?php
namespace Site\Task\Fg;

use Lib\Task\Context;
use Lib\Config;
use Lib\Task\IHandler;

/**
 * @file: CheckWinLoss.php
 * @description   防止fg结算失败
 * @Author  lucy
 * @date  2019-04-08
 * @links  Plat\Task\Fg\WinLoss.php
 * @returndata
 * @modifyAuthor
 * @modifyTime
 */

class CheckWinLoss implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data = $context->getData();
        $res = $data['res'] ?? '';
        if (!empty($res)) {
            if (isset($res['http_code']) && $res['http_code'] == 200) {
                $end_money = $res['res']['end_chips'] ?? 0;
            }
        }
        if (!isset($end_money)) {
            return;
        }
        $mysql = $config->data_user;
        $params = $data['success_param'];
        $params[':success_time'] = time();
        $user_id = $data['user_id'];
        $betId = $data['betId'];
        $sql = 'SELECT deal_key FROM user_info WHERE user_id=:user_id';
        $param = [':user_id' => $user_id];
        $dealKey = '';
        foreach ($mysql->query($sql, $param) as $row) {
            $dealKey = $row['deal_key'];
        }
        if ($end_money <= 0) {
            //fg结算成功
            if (!empty($dealKey)) {
                $mysql = $config->__get('data_' . $dealKey);
                $sql = "SELECT import_serial FROM external_import_success WHERE success_data->'$.betId'=:betId";
                $importSerial = '';
                foreach ($mysql->query($sql, [':betId' => $betId]) as $row) {
                    $importSerial = $row['import_serial'];
                }
                if (empty($importSerial)) {
                    $sql = 'INSERT INTO external_import_success SET import_serial = :import_serial,success_time = :success_time,success_data = :fgWinLossList';
                    $importSerial = $params[':import_serial'];
                    $mysql->execute($sql, $params);
                    $sql = 'SELECT success_deal_serial FROM external_import_success WHERE import_serial=:import_serial';
                    $param = [':import_serial' => $importSerial];
                    $successDealSerial = '';
                    foreach ($mysql->query($sql, $param) as $row) {
                        $successDealSerial = $row['success_deal_serial'];
                    }
                    if (empty($successDealSerial)) {

                        fwrite(STDERR, "site/Task/Fg/CheckBet: 结算失败'.date('[Y-m-d H:i:s]')\n");
                    } else {
                        $taskAdapter = $context->getAdapter();
                        $taskAdapter->plan('User/Balance', ['user_list' => [$user_id]], time(), 6);
                    }
                }
            }
        } else {
            //fg结算失败

            if (!empty($dealKey)) {
                $mysql = $config->__get('data_' . $dealKey);
                $sqls = "INSERT INTO external_import_failure SET import_serial=:import_serial,failure_time=:success_time,failure_data=:fgWinLossList";
                try {
                    //执行
                    $mysql->execute($sqls, $params);
                } catch (\PDOException $e) {
                    throw new \PDOException($e);
                }
            }
        }
    }
}
