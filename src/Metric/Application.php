<?php

namespace Nadi\Symfony\Metric;

use Nadi\Metric\Base;

class Application extends Base
{
    public function metrics(): array
    {
        $metrics = [];

        if (isset($_SERVER['APP_ENV'])) {
            $metrics['app.environment'] = $_SERVER['APP_ENV'];
        }

        if (PHP_SAPI === 'cli') {
            $metrics['app.context'] = 'console';
            if (isset($_SERVER['argv'])) {
                $metrics['app.command'] = implode(' ', array_slice($_SERVER['argv'], 1));
            }
        }

        return $metrics;
    }
}
