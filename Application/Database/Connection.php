<?php
declare(strict_types=1);

namespace Application\Database;

use PDO;

final class Connection
{
    private ?PDO $pdo = null;

    public function __construct(
        private array $config
    )
    {
        
    }

    public function getConnection(): PDO
    {
        if($this->pdo === null) {
            try {
                $dsn = $this->config['driver'] . ':host=' . $this->config['host'] . ';dbname=' . $this->config['dbname'] . ';charset=' . $this->config['charset'];
                $this->pdo = new PDO($dsn, $this->config['user'], $this->config['password'], $this->config['errmode']);
                
            } catch (\Throwable $th) {
                throw new \Exception("Error connecting to database: {$th->getMessage()}", 1);                
            }            
        }

        return $this->pdo;
    }
}