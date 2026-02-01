<?php

namespace Nadi\Symfony\Handler;

use Nadi\Data\Type;
use Nadi\Symfony\Concerns\FetchesStackTrace;
use Nadi\Symfony\Data\Entry;
use Nadi\Symfony\Support\OpenTelemetrySemanticConventions;

class HandleQueryEvent extends Base
{
    use FetchesStackTrace;

    private array $config = [];

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function handleQuery(string $sql, float $time, string $connectionName = 'default'): void
    {
        $slowThreshold = $this->config['query']['slow_threshold'] ?? 500;

        if ($time <= $slowThreshold) {
            return;
        }

        $otelAttributes = OpenTelemetrySemanticConventions::databaseAttributes($connectionName, $sql, $time);
        $userAttributes = OpenTelemetrySemanticConventions::userAttributes();
        $sessionAttributes = OpenTelemetrySemanticConventions::sessionAttributes();
        $otelData = array_merge($otelAttributes, $userAttributes, $sessionAttributes);

        if ($caller = $this->getCallerFromStackTrace()) {
            $otelData[OpenTelemetrySemanticConventions::CODE_FILEPATH] = $caller['file'];
            $otelData[OpenTelemetrySemanticConventions::CODE_LINENO] = $caller['line'];

            $entryData = [
                'connection' => $connectionName,
                'sql' => $sql,
                'time' => number_format($time, 2, '.', ''),
                'slow' => true,
                'file' => $caller['file'],
                'line' => $caller['line'],
                'otel' => $otelData,
            ];

            $this->store(
                Entry::make(Type::QUERY, $entryData)
                    ->setHashFamily($this->hash($sql.date('Y-m-d')))
                    ->tags([
                        'slow',
                        OpenTelemetrySemanticConventions::DB_CONNECTION_NAME.':'.$connectionName,
                        'query.slow:true',
                    ])
                    ->toArray()
            );
        }
    }
}
