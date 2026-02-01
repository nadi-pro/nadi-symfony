<?php

namespace Nadi\Symfony\Tests\Feature;

use Nadi\Symfony\DependencyInjection\Configuration;
use Nadi\Symfony\DependencyInjection\NadiExtension;
use Nadi\Symfony\NadiBundle;
use Nadi\Symfony\Tests\TestCase;
use Symfony\Component\Config\Definition\Processor;

class BundleTest extends TestCase
{
    public function test_bundle_can_be_instantiated(): void
    {
        $bundle = new NadiBundle;

        $this->assertInstanceOf(NadiBundle::class, $bundle);
    }

    public function test_bundle_returns_extension(): void
    {
        $bundle = new NadiBundle;
        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(NadiExtension::class, $extension);
    }

    public function test_configuration_has_default_values(): void
    {
        $configuration = new Configuration;
        $processor = new Processor;

        $config = $processor->processConfiguration($configuration, []);

        $this->assertTrue($config['enabled']);
        $this->assertEquals('log', $config['driver']);
        $this->assertEquals(500, $config['query']['slow_threshold']);
        $this->assertEquals('fixed_rate', $config['sampling']['strategy']);
    }

    public function test_configuration_accepts_custom_values(): void
    {
        $configuration = new Configuration;
        $processor = new Processor;

        $config = $processor->processConfiguration($configuration, [
            'nadi' => [
                'enabled' => false,
                'driver' => 'http',
                'query' => [
                    'slow_threshold' => 1000,
                ],
            ],
        ]);

        $this->assertFalse($config['enabled']);
        $this->assertEquals('http', $config['driver']);
        $this->assertEquals(1000, $config['query']['slow_threshold']);
    }
}
