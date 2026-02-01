<?php

namespace Nadi\Symfony;

use Nadi\Data\Type;

class Nadi
{
    private Transporter $transporter;

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->transporter = new Transporter($config);
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function store(array $data): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->transporter->store($data);
    }

    public function recordException(\Throwable $exception): void
    {
        $entry = new Data\ExceptionEntry($exception);
        $this->store($entry->toArray());
    }

    public function recordQuery(string $sql, float $duration, string $connectionName = 'default'): void
    {
        $entry = new Data\Entry(Type::QUERY);
        $entry->content = [
            'connection' => $connectionName,
            'sql' => $sql,
            'duration' => $duration,
            'slow' => $duration >= ($this->config['query']['slow_threshold'] ?? 500),
        ];
        $this->store($entry->toArray());
    }

    public function send(): void
    {
        $this->transporter->send();
    }

    public function test()
    {
        return $this->transporter->test();
    }

    public function verify()
    {
        return $this->transporter->verify();
    }

    public function getTransporter(): Transporter
    {
        return $this->transporter;
    }
}
