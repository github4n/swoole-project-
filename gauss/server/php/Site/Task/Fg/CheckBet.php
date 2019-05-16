<?php
namespace Site\Task\Fg;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

/**
 * @file: CheckBet.php
 * @description   fg游戏下单失败,手动结算文件
 * @Author  lucy
 * @date  2019-04-26
 * @links  plat/Http/Fg/Bet
 * @returndata
 * @modifyAuthor
 * @modifyTime
 */
class CheckBet implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        $data=$context->getData();
        $username=$data['username']?? '';
        $params=$data['success_param']?? '';
        $params[':success_time']=time();
        $res=$data['res'] ?? '';
        if(!empty($username)){
            $mysql = $config->data_user;
            $sql = 'SELECT user_id FROM user_fungaming WHERE fg_member_code=:fg_member_code';
            $param = [':fg_member_code' => $username];
            $userId = '';
            foreach ($mysql->query($sql, $param) as $row) {
                $userId = $row['user_id'];
            }

            if (!empty($userId)) {
                $sql = 'SELECT deal_key FROM user_info WHERE user_id=:user_id';
                $param = [':user_id' => $userId];
                $dealKey = '';
                foreach ($mysql->query($sql, $param) as $row) {
                    $dealKey = $row['deal_key'];
                }
                if(empty($dealKey)){
                    fwrite(STDERR, "site/Task/Fg/CheckBet: 下注失败时本地执行错误失败'.date('[Y-m-d H:i:s]') 获取用户的deal_key失败\n");
                    return;
                }
                $mysql = $config->__get('data_'.$dealKey);

                if(isset($res['http_code'])&& $res['http_code']==200){
                    //插入转出成功记录sql
                    $sqlss = "INSERT INTO external_export_success SET export_serial=:export_serial,success_time=:success_time,success_data = :lauch_data";
                    try{
                        $mysql->execute($sqlss,$params);
                    }catch (\PDOException $e){
                        fwrite(STDERR, "site/Task/Fg/CheckBet: 下注失败时本地执行成功失败'.date('[Y-m-d H:i:s]') 获取用户的deal_key失败\n");
                        throw new \PDOException($e);
                    }
                }else{
                    $sqls = "INSERT INTO external_export_failure SET export_serial=:export_serial,failure_deal_serial='1',failure_time=:success_time,failure_data=:lauch_data";
                    try{
                        //执行
                        $mysql->execute($sqls,$params);
                    }catch (\PDOException $e){
                        fwrite(STDERR, "site/Task/Fg/CheckBet: 下注失败时本地执行错误失败'.date('[Y-m-d H:i:s]') 获取用户的deal_key失败\n");
                        throw new \PDOException($e);
                    }

                }


                $taskAdapter=$context->getAdapter();
                $taskAdapter->plan('User/Balance', ['user_list' => [$userId]], time(), 6);

            }

        }

    }
}