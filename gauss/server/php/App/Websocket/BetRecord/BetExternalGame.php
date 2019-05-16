<?php
/**
 * Created by PhpStorm.
 * User: lucy
 * Date: 19-3-22
 * Time: 上午10:03
 */
namespace App\Websocket\BetRecord;

use Lib\Config;
use Lib\Websocket\Context;
use App\Websocket\CheckLogin;

// BetRecord/BetExternalGame {"interface_key":"fg"}status:0;1赢,2输,3平
class BetExternalGame extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        //interface_key：选择游戏三方平台的类型

        $data = $context->getData();
        $interface_key = $data['interface_key'] ?? '';

        if (empty($interface_key)) {
            $context->reply(['status' => 201, 'msg' => '三方平台Key不能为空']);
            return;
        }
        $user_id = $context->getInfo('UserId');

        $deal_key = $context->getInfo('DealKey');
        $mysql = $config->__get("data_" . $deal_key);
        $param = [':user_id'=>$user_id,':external_type'=>$interface_key];
        $sql = "SELECT audit_serial,user_id,audit_amount,play_time,game_key,external_data FROM external_audit WHERE user_id=:user_id and external_type=:external_type ORDER BY play_time DESC";

        $result=[];
        switch ($interface_key) {
            case 'fg':

                // 查询订单
                foreach ($mysql->query($sql, $param) as $row){
                    $winloss_amount=json_decode($row['external_data'],true);
                    $row['fg_game_id']='fg_'.$winloss_amount['game_id'];
                    $row['fg_gt']=$winloss_amount['gt'];
                    $row['fg_id']=$winloss_amount['id'];
                    unset($row['external_data']);
                    if($winloss_amount['gt']=='fish'){
                        $money=$winloss_amount['fish_dead_chips']-$winloss_amount['bullet_chips'];
                    }else{

                        $money=$winloss_amount['result'];
                    }
                    $row['winloss_amount']=$money;
                    $result[]=$row;
                }

                //返回数据
                $context->reply(['status' => 200, 'msg' => '获取成功', 'data' =>$result]);
                break;
            case 'ag':


                break;
            case 'ky':
                $winList=[];
                $lostList=[];
                $tieList=[];
                $allList=[];
                $JsonLislt=file_get_contents('ky.json',1);
                $JsonLislt=json_decode($JsonLislt,true);

                foreach ($mysql->query($sql, $param) as $row){
                    $winloss_amount=json_decode($row['external_data'],true);
                    unset($row['external_data']);
                    $Profit=$winloss_amount['Profit'];
                    $row['winloss_amount']=$Profit;
                    $row['ServerID']=''.$winloss_amount['ServerID'];
                    $row['name']=$JsonLislt[$row['ServerID']];

                    if($Profit<0){
                        $row['winloss_amount_str']='输局';
                        $lostList[]=$row;
                    }elseif($Profit==0){
                        $row['winloss_amount_str']='平局';
                        $tieList[]=$row;
                    }else{
                        $row['winloss_amount_str']='赢局';
                        $winList[]=$row;
                    }

                    $allList[]=$row;
                }
                $result=[
                    'allList'=>$allList,
                    'winList'=>$winList,
                    'lostList'=>$lostList,
                    'tieList'=>$tieList,
                ];
                //返回数据
                $context->reply(['status' => 200, 'msg' => '获取成功', 'data' =>$result]);

                break;
            case 'lb':


                break;
            default:
                $context->reply(['status'=>404,'msg'=>'非法参数']);
                return;
                break;
        }
    }

}