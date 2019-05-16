<?php
namespace Plat\Task\Lottery\Spider;

use Lib\Config;
use Lib\Task\Context;

class History extends Base
{
    private const URLFORMAT = 'https://spider.xblan.cc/opencai/%s/%s.json';
    public function onTask(Context $context, Config $config)
    {
        ['game_key' => $game_key] = $context->getData();
        $adapter = $context->getAdapter();
        $mysql = $config->data_public;
        if (!method_exists($this, $game_key)) {
            return;
        }

        $sql = 'select min(plan_time) as since from lottery_period_opening where game_key=:game_key';
        foreach ($mysql->query($sql, ['game_key' => $game_key]) as $row) {
            if (empty($row['since'])) {
                continue;
            }
            $generator = $this->$game_key($row['since']);
            if (!empty($generator)) {
                $mysql->lottery_number->import($generator, ['game_key' => $game_key, 'open_time' => time()], 'ignore');
            }
        }
    }
    public function tiktok_cq(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'cqssc', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                    'normal4' => intval($openNumbers[3]),
                    'normal5' => intval($openNumbers[4]),
                ];
            }
        }
    }
    public function dice_js(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'jsk3', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                ];
            }
        }
    }
    public function dice_ah(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'ahk3', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                ];
            }
        }

    }
    public function lucky_cq(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'xylc', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                    'normal4' => intval($openNumbers[3]),
                    'normal5' => intval($openNumbers[4]),
                    'normal6' => intval($openNumbers[5]),
                    'normal7' => intval($openNumbers[6]),
                    'normal8' => intval($openNumbers[7]),
                ];
            }
        }
    }
    public function lucky_gd(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'gdkl10f', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                    'normal4' => intval($openNumbers[3]),
                    'normal5' => intval($openNumbers[4]),
                    'normal6' => intval($openNumbers[5]),
                    'normal7' => intval($openNumbers[6]),
                    'normal8' => intval($openNumbers[7]),
                ];
            }
        }
    }
    public function racer_bj(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'bjpk10', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                    'normal4' => intval($openNumbers[3]),
                    'normal5' => intval($openNumbers[4]),
                    'normal6' => intval($openNumbers[5]),
                    'normal7' => intval($openNumbers[6]),
                    'normal8' => intval($openNumbers[7]),
                    'normal9' => intval($openNumbers[8]),
                    'normal10' => intval($openNumbers[9]),
                ];
            }
        }
    }
    public function racer_malta(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'xyft', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                    'normal4' => intval($openNumbers[3]),
                    'normal5' => intval($openNumbers[4]),
                    'normal6' => intval($openNumbers[5]),
                    'normal7' => intval($openNumbers[6]),
                    'normal8' => intval($openNumbers[7]),
                    'normal9' => intval($openNumbers[8]),
                    'normal10' => intval($openNumbers[9]),
                ];
            }
        }
    }
    public function eleven_gd(int $since)
    {
        $now = time();
        for ($time = $since; $time < $now; $time += 86400) {
            $url = sprintf(self::URLFORMAT, 'gd11x5', date('Y/m/d', $time));
            $json = $this->httpGet($url);
            $data = json_decode($json, true);
            if (empty($data['data'])) {
                return;
            }
            foreach ($data['data'] as $row) {
                ['expect' => $period, 'opencode' => $openCode, 'opentimestamp' => $openTime] = $row;
                $openNumbers = explode(',', $openCode);
                yield [
                    'period' => $period,
                    'open_time' => $openTime,
                    'normal1' => intval($openNumbers[0]),
                    'normal2' => intval($openNumbers[1]),
                    'normal3' => intval($openNumbers[2]),
                    'normal4' => intval($openNumbers[3]),
                    'normal5' => intval($openNumbers[4]),
                ];
            }
        }
    }
    // public function six_hk(int $since)
    // {
    // }
}
