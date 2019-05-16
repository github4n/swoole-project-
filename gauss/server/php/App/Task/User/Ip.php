<?php
namespace App\Task\User;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Ip implements IHandler
{
    public function onTask(Context $context, Config $config)
    {
        ['ip' => $ip] = $context->getData();
        $url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if($data['code'] == 0){
            $ipData = [
                "ip_net" => ip2long($ip)>>8,
                "country" => $data["data"]["country"],
                "area" => empty($data["data"]["area"]) ? "" : $data["data"]["area"],
                "region" => $data["data"]["region"],
                "city" => $data["data"]["city"],
                "county" => $data["data"]["county"],
                "isp" => $data["data"]["isp"],
                "update_time" => time(),
            ];

            if(ip2long($ip)>>8 != 0){
                $taskAdapter = new \Lib\Task\Adapter($config->cache_daemon);
                $taskAdapter->plan('NotifySite', ['path' => 'Ip/Gather', 'data' => ["data" => $ipData]]);
            }

        }

    }
    protected function httpGet(string $url): ?string
    {
        $curl = curl_init();
        $options = [
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
        ];
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        if ($errno) {
            $message = "curl error({$errno}):{$error}\n" . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            fwrite(STDERR, Date('[Y-m-d H:i:s]') . $message . "\n");
            return null;
        } else {
            return $result;
        }
    }
}
