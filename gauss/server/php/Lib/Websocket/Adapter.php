<?php

namespace Lib\Websocket;

use Lib\Cache;

class Adapter
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function send(string $clientId, string $path, $data): void
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('empty path');
        }
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $message = "{$clientId} {$path} {$json}";
        $this->cache->lpush('websocket:send', $message);

        $log = sprintf("[%s]send %s %s\n", date('Y-m-d H:i:s'), $clientId, $path);
        fwrite(STDOUT, $log);
    }

    public function receive(): ?Context
    {
        $message = $this->cache->rpop('websocket:receive');
        if (false === $message) {
            return null;
        }

        $context = new Context($this, $message);

        // 检测重复
        $checkKey = 'websocket:receive:' . $context->clientId() . ':' . $context->getCall();
        if ($this->cache->set($checkKey, time(), ['nx', 'ex' => 1])) {
            $log = sprintf("[%s]recv %s %s\n", date('Y-m-d H:i:s'), $context->clientId(), $context->getCall());
            fwrite(STDOUT, $log);

            return $context;
        } else {
            $log = sprintf("[%s]drop %s %s\n", date('Y-m-d H:i:s'), $context->clientId(), $context->getCall());
            fwrite(STDOUT, $log);

            return null;
        }
    }

    public function isAlive(string $clientId): bool
    {
        $clientKey = 'websocket:client:' . $clientId;

        return $this->cache->exists($clientKey);
    }

    public function getClientInfo(string $clientId, string $name): string
    {
        $clientKey = 'websocket:client:' . $clientId;

        return $this->cache->hget($clientKey, $name);
    }

    public function setClientInfo(string $clientId, string $name, string $value): void
    {
        $clientKey = 'websocket:client:' . $clientId;
        $this->cache->hset($clientKey, $name, $value);
    }

    public function queryClients()
    {
        $keys = $this->cache->keys('websocket:client:*');
        foreach ($keys as $key) {
            $keyParts = explode(':', $key);
            $clientId = end($keyParts);
            if (!empty($clientId)) {
                yield $clientId;
            }
        }
    }
}
