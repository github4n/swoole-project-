<?php

namespace Lib\Websocket;

use Lib\Cache;
use Lib\Config;
use Lib\NameBuilder;

class Daemon
{
    private $adapter;
    private $namespace;

    public function __construct(Cache $cache, string $namespace)
    {
        $this->adapter = new Adapter($cache);
        $this->namespace = NameBuilder::parse($namespace);
    }

    public function run(Config $config)
    {
        while ($this->nothingChange()) {
            set_time_limit(10);
            try {
                foreach ($config as $name => $value) {
                    if ($value instanceof \Lib\Data\Connection) {
                        $value->ping();
                    }
                }
                $context = $this->adapter->receive();
                if (is_null($context)) {
                    usleep(10000);
                    continue;
                }
                if (!$context->isAlive()) {
                    if ('Disconnect' != $context->getPath()) {
                        continue;
                    }
                }
                $path = $context->getPath();
                $className = $this->namespace->child($path)->__toString();
                if (is_subclass_of($className, __NAMESPACE__.'\\IHandler')) {
                    $obj = new $className();
                    $obj->onReceive($context, $config);
                }
            } catch (\Throwable $ex) {
                $log = sprintf("[%s]%s\n", date('Y-m-d H:i:s'), $ex);
                fwrite(STDERR, $log);
            }
        }
    }

    private function nothingChange(): bool
    {
        static $time = null;
        if (is_null($time)) {
            $time = time();
        }

        foreach (get_included_files() as $file) {
            if ($time < filectime($file) || $time < filemtime($file)) {
                return false;
            }
        }

        return true;
    }
}
