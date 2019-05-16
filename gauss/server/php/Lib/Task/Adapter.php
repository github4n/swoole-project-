<?php
namespace Lib\Task;

use Lib\Cache;

class Adapter
{
    // argv=[priority,time,task]
    private const LUA_PUSH = <<<LUA
local k=KEYS[1]..ARGV[1]
local score=redis.call('ZSCORE',k,ARGV[3])
if not score or score > ARGV[2] then
    redis.call('ZADD',k,ARGV[2],ARGV[3])
    return true
else
    return false
end
LUA;
    // argv=[time]
    private const LUA_POP = <<<LUA
for i=0,9,1 do
    local k=KEYS[1]..i
    local msg=redis.call('ZRANGEBYSCORE',k,0,ARGV[1],'limit',0,1)
    if 0<#msg then
        redis.call('ZREM',k,msg[1])
        return msg[1]
    end
end
LUA;
    private $cache, $shaPush, $shaPop;
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
        $this->shaPush = $cache->script('load', self::LUA_PUSH);
        $this->shaPop = $cache->script('load', self::LUA_POP);
    }
    public function plan(string $path, array $data, int $time = null, int $priority = 5): bool
    {
        $task = $path . ' ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (is_null($time)) {
            $time = time();
        }

        $log = sprintf("[%s]plan %d/%d %s\n", date('Y-m-d H:i:s'), $time, $priority, $task);
        fwrite(STDOUT, $log);
        return $this->cache->evalsha($this->shaPush, ['task:', $priority, $time, $task], 1);
    }
    public function pick(): ?Context
    {
        $task = $this->cache->evalsha($this->shaPop, ['task:', time()], 1);
        if (false === $task) {
            return null;
        }

        $log = sprintf("[%s]pick %s\n", date('Y-m-d H:i:s'), $task);
        fwrite(STDOUT, $log);
        return new Context($this, $task);
    }
}
