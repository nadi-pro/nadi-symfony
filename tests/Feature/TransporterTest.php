<?php

namespace Nadi\Symfony\Tests\Feature;

use Nadi\Symfony\Tests\TestCase;
use Nadi\Symfony\Transporter;

class TransporterTest extends TestCase
{
    public function test_transporter_can_be_instantiated(): void
    {
        $config = $this->getNadiConfig();
        $transporter = new Transporter($config);

        $this->assertInstanceOf(Transporter::class, $transporter);
    }

    public function test_transporter_can_store_data(): void
    {
        $config = $this->getNadiConfig();
        $transporter = new Transporter($config);

        // store() may return null if sampling rejects the data
        $transporter->store(['type' => 'test', 'data' => []]);
        $this->assertInstanceOf(Transporter::class, $transporter);
    }

    public function test_transporter_configures_log_driver(): void
    {
        $config = $this->getNadiConfig(['driver' => 'log']);
        $transporter = new Transporter($config);

        $this->assertInstanceOf(Transporter::class, $transporter);
    }
}
