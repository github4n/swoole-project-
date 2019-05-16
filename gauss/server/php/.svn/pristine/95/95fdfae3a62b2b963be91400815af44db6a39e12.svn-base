<?php
/**
 * Created by PhpStorm.
 * User: lucy
 * Date: 19-3-8
 * Time: 上午8:45
 */

namespace Plat\Http\Fg;

use Lib\Config;
use Lib\Http\Context;
use Lib\Http\Handler;
use Plat\Http\Fg\Common;

class Balance extends Handler
{
    public function onRequest(Context $context, Config $config)
    {
        $get_data= $context->requestPost();

        $query=[];
        parse_str($get_data,$query);
        $Common=new Common();
        $partnerId=$Common->getPartnerId($config);
        $flg=true;
        $name='';
        if(!isset($query['partnerId']) || empty($query['partnerId']) || strcmp($query['partnerId'],$partnerId)!==0){
           $flg=false;
           $name='partnerId';
        }
        if(!isset($query['username']) || empty($query['username'])){
            $flg=false;
            $name='username';
        }
        if(!isset($query['nonce_str']) || empty($query['nonce_str'])){
            $flg=false;
            $name='nonce_str';
        }
       
        if(!isset($query['sign']) || empty($query['sign'])){
            $flg=false;
            $name='sign';
        }

        if(!$flg){
            $arr=$Common->return_data(2,$name.'参数错误');
            $this->responseJson($context, $arr);
            return;
        }

        $username=$query['username'];
        $nonce_str=$query['nonce_str'];
       
        $sign=$query['sign'];
        $param=array('username'=>$username,'partnerId'=>$partnerId,'nonce_str'=>$nonce_str);
        $new_sign=$Common->MakeSign($param,$config);

        if(strcmp($new_sign,$sign)!==0){
            $arr=$Common->return_data(108,'签名错误');
            $this->responseJson($context, $arr);
            return;
        }

        $site_key=explode('fg',$username)[0];

        $param['clientId']=$context->clientId();
        $param['site_key']=$site_key;
        $adapter = new \Lib\Task\Adapter($config->cache_plat);
        $adapter->plan('NotifySite',['path' => 'Fg/GetBalance','data'=>$param]);

    }
}
