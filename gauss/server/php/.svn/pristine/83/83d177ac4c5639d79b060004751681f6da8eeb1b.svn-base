<?php
namespace Lib\Data;

use PDO;

class Connection extends PDOWrapper
{
    private $uri, $tables, $lastPingTime;
    protected function init()
    {
        $parse = parse_url($this->uri);
        $host = $parse['host'] ?? 'redis';
        $port = $parse['port'] ?? 3306;
        $user = $parse['user'] ?? '';
        $pass = $parse['pass'] ?? '';
        $dbname = trim($parse['path'], '/');
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8mb4' collate 'utf8mb4_unicode_ci'",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $this->tables = [];
        $this->lastPingTime = time();
    }
    // mysql://user:pass@host:port/db
    public function __construct(string $uri)
    {
        $this->uri = $uri;
        $this->init();
    }
    public function ping()
    {
        if (30 < time() - $this->lastPingTime) {return;}
        $this->lastPingTime = time();
        try {
            $this->pdo->query('select 1');
        } catch (PDOException $ex) {
            $this->init();
        }
    }
    public function query(string $sql, array $params = [])
    {
        $ps = $this->prepareComplex($sql, $params);
        return $this->executeIterator($ps);
    }
    public function execute(string $sql, array $params = []): int
    {
        $ps = $this->prepareComplex($sql, $params);
        return $this->executeEffect($ps);
    }
    public function __get(string $tableName): Table
    {
        if (empty($this->tables[$tableName])) {
            $this->tables[$tableName] = new Table($this, $tableName);
        }
        return $this->tables[$tableName];
    }
}
