<?php

namespace Lib\Websocket;

class Context
{
    private $adapter;
    private $id;
    private $call;
    private $path;
    private $data;

    public function __construct(Adapter $adapter, string $message)
    {
        $this->adapter = $adapter;
        $field = explode(' ', $message, 3);
        $this->id = $field[0];
        $this->call = $field[1] ?? '';
        $this->path = parse_url($this->call, PHP_URL_PATH);
        $this->data = json_decode($field[2] ?? 'null', true);
    }

    public function isAlive(): bool
    {
        return $this->adapter->isAlive($this->id);
    }

    public function getAdapter(): Adapter
    {
        return $this->adapter;
    }

    public function clientId(): string
    {
        return $this->id;
    }

    public function getCall(): string
    {
        return $this->call;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getData()
    {
        return $this->data;
    }

    public function reply($data): void
    {
        $this->adapter->send($this->id, $this->call, $data);
    }

    public function getInfo(string $name): string
    {
        return $this->adapter->getClientInfo($this->id, $name);
    }

    public function setInfo(string $name, string $value): void
    {
        $this->adapter->setClientInfo($this->id, $name, $value);
    }

    public function getServerHost(): string
    {
        $result = $this->getInfo('X-Server-Host');
        if (empty($result)) {
            $result = $this->getInfo('Host');
        }

        return $result;
    }

    public function getClientAddr(): string
    {
        $result = $this->getInfo('X-Client-Addr');
        if (empty($result)) {
            $result = $this->getInfo('REMOTE_ADDR');
        }

        return $result;
    }
}
