<?php

namespace Nadi\Symfony\Data;

use Nadi\Data\ExceptionEntry as DataExceptionEntry;
use Nadi\Symfony\Concerns\InteractsWithMetric;

class ExceptionEntry extends DataExceptionEntry
{
    use InteractsWithMetric;

    public function __construct($exception, $type, array $content)
    {
        parent::__construct($exception, $type, $content);
        $this->registerMetrics();
    }
}
