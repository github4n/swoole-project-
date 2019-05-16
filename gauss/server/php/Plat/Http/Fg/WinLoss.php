<?php
/**
 * Created by PhpStorm.
 * User: ayden
 * Date: 19-3-12
 * Time: 下午4:52
 */

namespace Plat\Http\Fg;

use Lib\Config;
use Lib\Http\Context;
use Lib\Http\Handler;
use Lib\Task\Adapter;
/*
 * fg游戏结算
 * $partnerId String 代理商账号
 * $nonce_str String 随机字符串,不长于32位
 * $sign String 通过签名算法计算得出的签名值
 * $winLossList String winLoss json 奖励列表每次结算仅有一笔,betId:注单号,prize:结算金额,username:用户名称,reckon:结算 ID
 * http://127.0.0.1:8080/0/Fg/WinLoss
 */
class WinLoss extends Handler
{
    public function onRequest(Context $context, Config $config)
    {

        $common = new Common();
        $request = $context->requestPost();
        parse_str($request, $params); //将url参数字符串转换成数组
        if(!$params){
            $res = $common->return_data(2,'参数为空');
            $this -> responseJson($context,$res);
            return;
        }

        //判断代理商账号是否正确
        $partnerId = $common->getPartnerId($config);
        if($partnerId != $params['partnerId']){
            $res = $common->return_data(2,'参数错误');
            $this -> responseJson($context,$res);
            return;
        }

        //处理奖励数据
        $winLossList = $params['winLossList'];
        $winLossList = str_replace("\\","",$winLossList);
        $winLossList = str_replace("[","",$winLossList);
        $winLossList = str_replace("]","",$winLossList);
        $winLossList = json_decode($winLossList,true);
        if(!is_numeric($winLossList['prize'])){
            $res = $common->return_data(104,'金额非法');
            $this -> responseJson($context,$res);
            return;
        }

        //生成签名
        $signCheck = $common->MakeSign($params,$config);

        //校验加密后的参数
        if($signCheck !== $params['sign']){
            $res = $common -> return_data(108,'签名失败');
            $this -> responseJson($context,$res);
            return;
        }

        $siteKey = explode('fg',$winLossList['username'])[0];
        $clientId = $context -> clientId();
        $taskAdapter = new Adapter($config->cache_daemon);
        $taskAdapter->plan('NotifySite',['path' => 'Fg/WinLoss', 'data'=> ['clientId' => $clientId, 'site_key' => $siteKey, 'winLossList' => $winLossList]]);
    }
}