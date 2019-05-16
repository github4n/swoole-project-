<?php
namespace Plat\Task\Lottery\Spider;

use Lib\Config;
use Lib\Task\Context;
use Lib\Task\IHandler;

class Base implements IHandler
{
    protected $repeatInterval = 3;
    public function onTask(Context $context, Config $config)
    {
        ['game_key' => $game_key, 'period' => $requestPeriod] = $context->getData();
        $adapter = $context->getAdapter();
        $mysql = $config->data_public;
        if (!method_exists($this, $game_key)) {
            return;
        }

        $spider = \Lib\NameBuilder::parse(get_called_class())->basename();
        $got = false;

        foreach ($this->$game_key() as $period => $message) {
            if ($period == $requestPeriod) {
                $got = true;
            }
            $effect = $mysql->lottery_spider->load([[
                'game_key' => $game_key,
                'period' => $period,
                'spider' => $spider,
                'get_time' => time(),
                'message' => json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]], [], 'ignore');
            if (0 < $effect) {
                $row = [
                    'game_key' => $game_key,
                    'period' => $period,
                ] + $message;
                $mysql->lottery_number->load([$row], ['open_time' => time()], 'ignore');
                $adapter->plan('NotifySite', ['path' => 'Lottery/Number', 'data' => ['game_key' => $game_key, 'period' => $period]]);
            }
        }
        
        $number_sql = "select game_key from lottery_number where game_key=:game_key and period=:period";
        $spider_sql = "select game_key from lottery_spider where game_key=:game_key and period=:period and spider=:spider";
        $number_list = iterator_to_array($mysql->query($number_sql,[":game_key"=>$game_key,":period"=>$requestPeriod]));
        $spider_list = iterator_to_array($mysql->query($spider_sql,[":game_key"=>$game_key,":period"=>$requestPeriod,":spider"=>$spider]));
        if(!empty($number_list) || !empty($spider_list)){
            $got = true;
        }

        if (!$got) {
            $context->repeat(time() + $this->repeatInterval);
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
