<?php
namespace Lib\Http;

use Lib\Cache;

class Context
{
    private $cache, $id;
    private $requestKey, $responseKey;
    public function __construct(Cache $cache, string $id)
    {
        $this->cache = $cache;
        $this->id = $id;
        $this->requestKey = 'http:request:' . $id;
        $this->responseKey = 'http:response:' . $id;
        $this->responseStatus(404);
        $this->responseBody('Not Found');
    }
    public function requestPath(): string
    {
        return $this->requestHeader('SCRIPT_NAME');
    }
    public function clientId(): string
    {
        return $this->id;
    }
    public function requestQuery(): string
    {
        return $this->requestHeader('QUERY_STRING');
    }
    public function requestPost(): string
    {
        return $this->requestHeader('POST');
    }
    public function requestHeader(string $name): string
    {
        return $this->cache->hget($this->requestKey, $name);
    }
    public function responseStatus(int $status): void
    {
        $this->responseHeader('STATUS', $status);
    }
    public function responseBody(string $body): void
    {
        $this->responseHeader('BODY', $body);
    }

    public function responseHeader(string $name, string $value): void
    {
        $this->cache->hset($this->responseKey, $name, $value);
    }
    public function responseFinish(){
        $this->cache->lpush('http:response', $this->id);
        $log = sprintf("[%s]response %d\n", date('Y-m-d H:i:s'), $this->id);
        fwrite(STDOUT, $log);
    }
}
