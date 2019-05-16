<?php
namespace Lib\Task;

class Context
{
    private $adapter, $path, $data;
    public function __construct(Adapter $adapter, string $task)
    {
        $this->adapter = $adapter;
        list($path, $json) = explode(' ', $task, 2);
        $data = json_decode($json, true);
        $this->path = $path;
        $this->data = $data;
    }
    public function getAdapter(): Adapter
    {
        return $this->adapter;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function repeat(int $time = null, int $priority = 5): void
    {
        $this->adapter->plan($this->path, $this->data, $time, $priority);
    }
}
