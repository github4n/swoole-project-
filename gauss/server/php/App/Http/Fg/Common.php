<?php

namespace App\Http\Fg;

class Common
{
    private $partnerId = "uat_gongying1214";
	private $key= "196f947f9c00ee8e20af23cc0d5b5926";

	public function __get($name)
	{
		return $this->$name;
	}

	// 生成签名
	public function MakeSign($values)
	{
		//签名步骤一:按字典序排序参数
		ksort($values);
		$string = $this->ToUrlParams($values);
		//签名步骤二:在 string 后加入 KEY
		$string = $string . "&key=". $this->key;
		//签名步骤三:MD5 加密
		$string = md5($string);
		//签名步骤四:所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}

	// 格式化参数格式化成 url 参数
	public function ToUrlParams($values)
	{
		$buff = "";
		foreach ($values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}

	// 生成20位随机字符串
	public function generateRandom($length = 15)
	{
		$characters = '0123456789876543210123456789';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	function return_data($state,$msg,$data=array())
	{
		$arr=array(
		    "state"=> $state, //状态码
		    "message"=> $msg, //返回信息内容
		    "data"=>$data
		);
		return $arr ;
    	}

    //获取UTC格式的时间
    function utc_time()
	{
        date_default_timezone_set('UTC');
        $timestamp = new \DateTime();
        $timeStr = $timestamp->format(DATE_ISO8601);
        return $timeStr;
    }
}
