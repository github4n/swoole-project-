<?php
namespace App\Websocket\User\Withdraw;

use App\Websocket\CheckLogin;
use Lib\Websocket\Context;
use Lib\Config;

/*
 * 充值
 *  User/Withdraw/WithdrawBank {""}
 *
 * */

class WithdrawBank extends CheckLogin
{
    public function onReceiveLogined(Context $context, Config $config)
    {
        $guest_id = $context->getInfo("GuestId");
        if(!empty($guest_id)){
            $context->reply(["status"=>500,"msg"=>"游客身份，没有访问权限"]);
            return;
        }
        
        $userId = $context->getInfo("UserId");
        $mysql = $config->data_user;
        $mysql_staff = $config->data_staff;
        $deal_key = $context->getInfo("DealKey");

        $sql = "SELECT bank_name,bank_branch,account_number,account_name FROM bank_info WHERE user_id=:user_id";
        $param = [":user_id"=>$userId];
        foreach ($mysql->query($sql,$param) as $row){
            $info = $row;
        }
        if(empty($info)){
            $context->reply(["status"=>204,"msg"=>"你还没有绑定银行卡"]);
            return;
        }
        //获取提现的数据信息
        $sql = "SELECT * FROM site_setting ";
        $withdraw_list = iterator_to_array($mysql_staff->query($sql));
        if(!empty($withdraw_list)){
            foreach ($withdraw_list as $k=>$v){
                if($v["setting_key"] == "withdraw_min"){   //出款下限
                    $withdraw_min = $v["dbl_value"] ;
                }
                if($v["setting_key"] == "withdraw_max"){    //出款上限
                    $withdraw_max = $v["dbl_value"] ;
                }
                if($v["setting_key"] == "withdraw_fee_rate"){   //出款手续费比例
                    $withdraw_fee_rate = $v["dbl_value"] ;
                }
            }
        }
        $context->reply([
            "status"=>200,
            "msg"=>"获取成功",
            "info"=>$info,//银行卡信息
            "withdraw_min"=>$withdraw_min, //最小出款
            "withdraw_max"=>$withdraw_max, //最大出款
            "withdraw_fee_rate"=>$withdraw_fee_rate, //出款手续费比例
            ]);
    }
}
