<?php

namespace Nadi\Symfony\Metric;

use Nadi\Metric\Base;
use Nadi\Symfony\Support\OpenTelemetrySemanticConventions;

class Http extends Base
{
    public function metrics(): array
    {
        if (PHP_SAPI === 'cli' || ! isset($_SERVER['REQUEST_URI'])) {
            return [];
        }

        return OpenTelemetrySemanticConventions::httpAttributesFromGlobals();
    }
}
