<?php

namespace Nadi\Symfony\Doctrine;

use Doctrine\DBAL\Driver as DBALDriver;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Driver\Middleware as DriverMiddleware;
use Nadi\Symfony\Nadi;

/**
 * Doctrine DBAL middleware for monitoring SQL queries.
 * Supports DBAL 3.x (SQLLogger) and DBAL 4.x (Middleware).
 */
class NadiSQLLogger implements DriverMiddleware
{
    public function __construct(
        private Nadi $nadi,
        private float $slowThreshold = 500.0,
    ) {}

    public function wrap(DBALDriver $driver): DBALDriver
    {
        return new NadiDriver($driver, $this->nadi, $this->slowThreshold);
    }
}

/**
 * @internal
 */
class NadiDriver extends AbstractDriverMiddleware
{
    public function __construct(
        DBALDriver $driver,
        private Nadi $nadi,
        private float $slowThreshold,
    ) {
        parent::__construct($driver);
    }

    public function connect(array $params): \Doctrine\DBAL\Driver\Connection
    {
        $connection = parent::connect($params);

        return new NadiConnection($connection, $this->nadi, $this->slowThreshold);
    }
}

/**
 * @internal
 */
class NadiConnection implements \Doctrine\DBAL\Driver\Connection
{
    public function __construct(
        private \Doctrine\DBAL\Driver\Connection $connection,
        private Nadi $nadi,
        private float $slowThreshold,
    ) {}

    public function prepare(string $sql): \Doctrine\DBAL\Driver\Statement
    {
        return new NadiStatement(
            $this->connection->prepare($sql),
            $this->nadi,
            $sql,
            $this->slowThreshold,
        );
    }

    public function query(string $sql): \Doctrine\DBAL\Driver\Result
    {
        $start = microtime(true);
        $result = $this->connection->query($sql);
        $duration = (microtime(true) - $start) * 1000;

        if ($duration >= $this->slowThreshold) {
            $this->nadi->recordQuery($sql, $duration);
        }

        return $result;
    }

    public function exec(string $sql): int|string
    {
        $start = microtime(true);
        $result = $this->connection->exec($sql);
        $duration = (microtime(true) - $start) * 1000;

        if ($duration >= $this->slowThreshold) {
            $this->nadi->recordQuery($sql, $duration);
        }

        return $result;
    }

    public function quote(string $value): string
    {
        return $this->connection->quote($value);
    }

    public function lastInsertId(): int|string
    {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function getServerVersion(): string
    {
        return $this->connection->getServerVersion();
    }

    public function getNativeConnection(): mixed
    {
        return $this->connection->getNativeConnection();
    }
}

/**
 * @internal
 */
class NadiStatement implements \Doctrine\DBAL\Driver\Statement
{
    public function __construct(
        private \Doctrine\DBAL\Driver\Statement $statement,
        private Nadi $nadi,
        private string $sql,
        private float $slowThreshold,
    ) {}

    public function bindValue(int|string $param, mixed $value, \Doctrine\DBAL\ParameterType $type = \Doctrine\DBAL\ParameterType::STRING): void
    {
        $this->statement->bindValue($param, $value, $type);
    }

    public function execute(): \Doctrine\DBAL\Driver\Result
    {
        $start = microtime(true);
        $result = $this->statement->execute();
        $duration = (microtime(true) - $start) * 1000;

        if ($duration >= $this->slowThreshold) {
            $this->nadi->recordQuery($this->sql, $duration);
        }

        return $result;
    }
}
