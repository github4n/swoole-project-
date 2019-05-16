<?php
namespace Plat\Task\Lottery\Spider;

class B1cp extends Base
{
    private const URLFORMAT = 'https://spider.xblan.cc/b1cp/%s.json';
    public function __construct()
    {
    }
    public function tiktok_cq()
    {
        $url = sprintf(self::URLFORMAT, 'cqssc');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
                'normal1' => intval($openNumbers[0]),
                'normal2' => intval($openNumbers[1]),
                'normal3' => intval($openNumbers[2]),
                'normal4' => intval($openNumbers[3]),
                'normal5' => intval($openNumbers[4]),
            ];
        }
    }
    public function dice_js()
    {
        $url = sprintf(self::URLFORMAT, 'jsk3');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
                'normal1' => intval($openNumbers[0]),
                'normal2' => intval($openNumbers[1]),
                'normal3' => intval($openNumbers[2]),
            ];
        }
    }
    public function dice_ah()
    {
        $url = sprintf(self::URLFORMAT, 'ahk3');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
                'normal1' => intval($openNumbers[0]),
                'normal2' => intval($openNumbers[1]),
                'normal3' => intval($openNumbers[2]),
            ];
        }

    }
    public function lucky_cq()
    {
        $url = sprintf(self::URLFORMAT, 'cqklsf');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
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
    public function lucky_gd()
    {
        $url = sprintf(self::URLFORMAT, 'gdklsf');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
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
    public function racer_bj()
    {
        $url = sprintf(self::URLFORMAT, 'bjpk10');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
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
    public function racer_malta()
    {
        $url = sprintf(self::URLFORMAT, 'xyft');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
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
    public function eleven_gd()
    {
        $url = sprintf(self::URLFORMAT, 'gd115');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
                'normal1' => intval($openNumbers[0]),
                'normal2' => intval($openNumbers[1]),
                'normal3' => intval($openNumbers[2]),
                'normal4' => intval($openNumbers[3]),
                'normal5' => intval($openNumbers[4]),
            ];
        }
    }
    public function six_hk()
    {
        $url = sprintf(self::URLFORMAT, 'hk6');
        $json = $this->httpGet($url);
        $data = json_decode($json, true);
        if (empty($data['data'])) {
            return;
        }
        foreach ($data['data'] as $row) {
            ['expect' => $period, 'opencode' => $openCode, 'opentime' => $openTime] = $row;
            $openNumbers = explode(',', $openCode);
            yield $period => [
                'open_time' => strtotime($openTime),
                'normal1' => intval($openNumbers[0]),
                'normal2' => intval($openNumbers[1]),
                'normal3' => intval($openNumbers[2]),
                'normal4' => intval($openNumbers[3]),
                'normal5' => intval($openNumbers[4]),
                'normal6' => intval($openNumbers[5]),
                'special1' => intval($openNumbers[6]),
            ];
        }
    }
}
