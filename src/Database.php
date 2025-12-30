<?php

declare(strict_types=1);

class Database
{
    private ?PDO $pdo = null;

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        if ($this->pdo !== null) {
            return;
        }

        global $config;
        $db = $config['db'] ?? null;
        if (empty($db) || empty($db['host']) || empty($db['name']) || empty($db['user']) || empty($db['pass'])) {
            throw new RuntimeException('Missing database configuration in $config[\'db\'].');
        }

        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
            $db['host'],
            $db['port'] ?? 5432,
            $db['name'],
            $db['user'],
            $db['pass']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, null, null, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => defined('DEBUG')
                    ? 'DB Error: ' . $e->getMessage()
                    : 'Internal server error. Please try again later.'
            ]);
            throw $e;
        }
    }

    public function execute(string $query, array $params = [], bool $expectResult = false, int $fetchStyle = PDO::FETCH_ASSOC): mixed
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $expectResult ? $stmt->fetchAll($fetchStyle) : $stmt->rowCount();
    }

    public function fetchOne(string $query, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function fetchValue(string $query, array $params = []): mixed
    {
        $row = $this->fetchOne($query, $params);
        return $row ? array_values($row)[0] : null;
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function prepare(string $query): PDOStatement
    {
        return $this->pdo->prepare($query);
    }
}
