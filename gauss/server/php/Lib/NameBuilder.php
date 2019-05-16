<?php
namespace Lib;

class NameBuilder
{
    public static function split(string $name) : array
    {
        preg_match_all('/[_a-z][_0-9a-z]*/i', $name, $matches);
        return $matches[0];
    }
    public static function parse(string $name) : self
    {
        $parts = self::split($name);
        return new self($parts);
    }

    private $parts;
    private function __construct(array $parts)
    {
        $this->parts = $parts;
    }
    public function parent() : self
    {
        $parts = $this->parts;
        if (empty($parts)) {
            return $this;
        } else {
            array_pop($parts);
            return new self($parts);
        }
    }
    public function basename() : string
    {
        return end($this->parts);
    }
    public function child(string $name) : self
    {
        $parts = $this->parts;
        $diff = self::split($name);
        array_push($parts, ...$diff);
        return new self($parts);
    }
    public function contains(self $child) : bool
    {
        foreach ($this->parts as $i => $n) {
            if ($n != $child->parts[$i]) return false;
        }
        return true;
    }
    public function level() : int
    {
        return count($this->parts);
    }
    public function slice(int $offset, int $count = null) : self
    {
        $parts = array_slice($this->parts, $offset, $count);
        return new self($parts);
    }
    public function __toString() : string
    {
        $str = implode('\\', $this->parts);
        return $str;
    }
    public function absolute(string $glue = '\\') : string
    {
        $str = implode($glue, $this->parts);
        if (!empty($str)) {
            $str = $glue . $str;
        }
        return $str;
    }
}