<?php

namespace Nadi\Symfony\Metric;

use Nadi\Metric\Base;
use Nadi\Symfony\Support\OpenTelemetrySemanticConventions;
use Symfony\Component\HttpKernel\Kernel;

class Framework extends Base
{
    public function metrics(): array
    {
        return [
            'framework.name' => 'symfony',
            'framework.version' => Kernel::VERSION,
            OpenTelemetrySemanticConventions::SERVICE_NAME => 'symfony-app',
            OpenTelemetrySemanticConventions::SERVICE_VERSION => '1.0.0',
            OpenTelemetrySemanticConventions::DEPLOYMENT_ENVIRONMENT => $_SERVER['APP_ENV'] ?? 'production',
        ];
    }
}
