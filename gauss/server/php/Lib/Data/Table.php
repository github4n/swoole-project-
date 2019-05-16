<?php

namespace Lib\Data;

class Table extends PDOWrapper
{

    private $tableName, $fields, $max_row_count;
    public function __construct(PDOWrapper $wrapper, string $tableName)
    {
        $this->pdo = $wrapper->pdo;
        $this->tableName = $tableName;

        $this->fields = [];
        $sql = 'show columns from `' . $tableName . '`';
        $ps = $this->pdo->prepare($sql);
        foreach ($this->executeIterator($ps) as $row) {
            if (empty($row['Extra'])) {
                $this->fields[$row['Field']] = $row;
            }
        }
        $this->max_row_count = intval(parent::MAX_BIND_COUNT / count($this->fields));
    }

    // $onerr = ignore : insert ignore into ...
    // $onerr = update ... : insert into ... on duplicate key update ...
    public function import(\Traversable $iterator, array $defaults = [], string $onerr = ''): int
    {
        if (empty($iterator)) {
            return 0;
        }
        $rows = [];
        $effect = 0;
        foreach ($iterator as $row) {
            $rows[] = $row;
            if (count($rows) > $this->max_row_count) {
                $effect += $this->insertMultipleRows($rows, $defaults, $onerr);
                $rows = [];
            }
        }
        if (0 < count($rows)) {
            $effect += $this->insertMultipleRows($rows, $defaults, $onerr);
        }
        return $effect;
    }
    public function load(array $rows, array $defaults = [], string $onerr = ''): int
    {
        $count = count($rows);
        $effect = 0;
        for ($offset = 0; $offset < $count; $offset += $this->max_row_count) {
            $slice = array_slice($rows, $offset, $this->max_row_count);
            $effect += $this->insertMultipleRows($slice, $defaults, $onerr);
        }
        return $effect;
    }
    private function insertMultipleRows(array $rows, array $defaults = [], string $onerr = ''): int
    {
        if (empty($rows)) {
            return 0;
        }

        $sqlValues = [];
        $args = [];
        foreach ($rows as $r => $row) {
            $keys = [];
            $f = 0;
            foreach ($this->fields as $field => $define) {

                $f++;
                $key = ":r{$r}f{$f}";
                $keys[] = $key;
                $args[$key] = $row[$field] ?? $defaults[$field] ?? $define['Default'];
            }
            $sqlValues[] = '(' . implode(',', $keys) . ')';
        }

        $sqlTable = '`' . $this->tableName . '`(`' . implode('`,`', array_keys($this->fields)) . '`)';
        if (0 == strcasecmp('ignore', $onerr)) {
            $sql = 'insert ignore into ' . $sqlTable . ' values ' . implode(',', $sqlValues);
        } elseif (0 == strcasecmp('replace', $onerr)) {
            $sql = 'replace into ' . $sqlTable . ' values ' . implode(',', $sqlValues);
        } elseif (0 == strncasecmp('update ', $onerr, 7)) {
            $sql = 'insert into ' . $sqlTable . ' values ' . implode(',', $sqlValues) . ' on duplicate key ' . $onerr;
        } else {
            $sql = 'insert into ' . $sqlTable . ' values ' . implode(',', $sqlValues);
        }
        $ps = $this->prepareSimple($sql, $args);
        if ($ps->execute()) {
            return $ps->rowCount();
        } else {
            return 0;
        }
    }
}
