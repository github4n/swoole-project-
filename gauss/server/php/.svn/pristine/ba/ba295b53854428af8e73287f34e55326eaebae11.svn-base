<?php
namespace Lib;

use Redis;

class Cache
{
    // redis://clientname:auth@host:port/db/prefix
    private static function openRedis(string $uri)
    {
        $parse = parse_url($uri);

        $redis = new Redis();
        $host = $parse['host'] ?? 'redis';
        $port = $parse['port'] ?? 6379;
        $redis->connect($host, $port);
        if (!empty($parse['pass'])) {
            $redis->auth($parse['pass']);
        }
        if (!empty($parse['user'])) {
            $redis->client('setname', $parse['user']);
        }
        $path = explode('/', $parse['path'] ?? '');
        if (is_numeric($path[1])) {
            $redis->select(intval($path[1]));
        }
        if (!empty($path[2])) {
            $prefix = $path[2] . ':';
            $redis->setOption(Redis::OPT_PREFIX, $prefix);
        }
        $redis->setOption(Redis::OPT_READ_TIMEOUT, -1);
        return $redis;
    }

    private $redis, $uri, $prefixLen;
    public function __construct(string $uri)
    {
        $this->uri = $uri;
        $this->redis = self::openRedis($uri);
        $prefix = $this->redis->getOption(Redis::OPT_PREFIX);
        $this->prefixLen = strlen($prefix);
    }
    public function __destruct()
    {
        $this->redis->close();
        unset($this->redis);
    }

    private $getValue = [];
    public function __get(string $name): self
    {
        if (empty($this->getValue[$name])) {
            $uri = $this->uri . ':' . $name;
            $obj = new self($uri);
            $this->getValue[$name] = $obj;
        }
        return $this->getValue[$name];
    }

    public function __call(string $name, array $args)
    {
        $result = $this->redis->$name(...$args);
        switch (strtolower($name)) {
            case 'keys':
            case 'getKeys':
                $tmp = [];
                $len = $this->prefixLen;
                foreach ($result as $item) {
                    $tmp[] = substr($item, $len);
                }
                $result = $tmp;
        }
        return $result;
    }
    public function scan(int &$cursor, string $pattern = '*', int $count = 10)
    {
        $result = $this->redis->scan($cursor, $pattern, $count);
        if (is_array($result)) {
            $tmp = [];
            $len = $this->prefixLen;
            foreach ($result as $item) {
                $tmp[] = substr($item, $len);
            }
            $result = $tmp;
        }
        return $result;
    }

    // $call=function(Cache $cache,string $channel,string $message){}
    public function subscribe(array $channels, callable $call)
    {
        $redis = self::openRedis($this->uri);
        $redis->subscribe($channels, function (Redis $redis, string $channel, string $message) use ($call) {
            $c = substr($channel, $this->prefixLen);
            $call($this, $c, $message);
        });
    }
}
