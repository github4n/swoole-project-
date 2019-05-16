<?php
namespace Lib\Data;

use PDO;
use PDOStatement;

abstract class PDOWrapper
{
    protected $pdo;
    protected const MAX_BIND_COUNT = 10000;
    protected function prepareComplex(string $sql, array $args): PDOStatement
    {
        $_sql = $sql;
        $_args = [];
        foreach ($args as $name => $value) {
            if (is_array($value)) {
                $_names = [];
                foreach ($value as $subName => $subValue) {
                    $_name = $name . '_' . $subName;
                    $_names[] = $_name;
                    $_args[$_name] = $subValue;
                }
                $_list = '(' . implode(',', $_names) . ')';
                $_sql = str_replace($name, $_list, $_sql);
            } elseif (is_scalar($value)) {
                $_args[$name] = $value;
            } else {
                throw new \UnexpectedValueException("invalid type for argument $name");
            }
        }
        return $this->prepareSimple($_sql, $_args);
    }
    protected function prepareSimple(string $sql, array $args): PDOStatement
    {
        $ps = $this->pdo->prepare($sql);
        foreach ($args as $name => $value) {
            if (is_null($value)) {
                $ps->bindValue($name, $value, PDO::PARAM_NULL);
            } elseif (is_bool($value)) {
                $ps->bindValue($name, $value, PDO::PARAM_BOOL);
            } elseif (is_int($value)) {
                $ps->bindValue($name, $value, PDO::PARAM_INT);
            } else {
                $ps->bindValue($name, $value);
            }
        }
        return $ps;
    }
    protected function executeIterator(PDOStatement $ps)
    {
        $ps->execute();
        while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
        $ps->closeCursor();
    }
    protected function executeEffect(PDOStatement $ps): int
    {
        if ($ps->execute()) {
            return $ps->rowCount();
        } else {
            return -1;
        }
    }
}
