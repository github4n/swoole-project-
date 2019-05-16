<?php
namespace Lib;

include __DIR__ . '/NameBuilder.php';

class Loader
{
    public static function register(string $path, string $namespace)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException('path not found');
        }
        if (empty($namespace)) {
            throw new \InvalidArgumentException('namespace is empty');
        }
        $obj = new self($path, $namespace);
        spl_autoload_register($obj);
    }

    private $realpath, $space;
    private function __construct(string $path, string $namespace)
    {
        $this->realpath = realpath($path);
        $this->space = NameBuilder::parse($namespace);
    }
    public function __invoke(string $className) : bool
    {
        $class = NameBuilder::parse($className);
        if ($this->space->contains($class)) {
            $diff = $class->slice($this->space->level());
            $path = $this->realpath . $diff->absolute(DIRECTORY_SEPARATOR) . '.php';
            if (is_file($path)) {
                require $path;
                return true;
            }
        }
        return false;
    }
}

Loader::register(__DIR__, __NAMESPACE__);
