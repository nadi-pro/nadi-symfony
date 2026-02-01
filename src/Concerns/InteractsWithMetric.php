<?php

namespace Nadi\Symfony\Concerns;

use Nadi\Symfony\Metric\Application;
use Nadi\Symfony\Metric\Framework;
use Nadi\Symfony\Metric\Http;
use Nadi\Symfony\Metric\Network;

trait InteractsWithMetric
{
    public function registerMetrics(): void
    {
        if (method_exists($this, 'addMetric')) {
            $this->addMetric(new Http);
            $this->addMetric(new Framework);
            $this->addMetric(new Application);
            $this->addMetric(new Network);
        }
    }
}
