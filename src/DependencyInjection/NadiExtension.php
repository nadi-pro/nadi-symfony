<?php

namespace Nadi\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class NadiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('nadi.enabled', $config['enabled']);
        $container->setParameter('nadi.driver', $config['driver']);
        $container->setParameter('nadi.connections', $config['connections']);
        $container->setParameter('nadi.query', $config['query']);
        $container->setParameter('nadi.http', $config['http']);
        $container->setParameter('nadi.sampling', $config['sampling']);

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'nadi';
    }
}
