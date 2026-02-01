<?php

namespace Nadi\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nadi');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('driver')->defaultValue('log')->end()
                ->arrayNode('connections')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('log')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('path')->defaultValue('%kernel.logs_dir%/nadi')->end()
                            ->end()
                        ->end()
                        ->arrayNode('http')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('apiKey')->defaultNull()->end()
                                ->scalarNode('appKey')->defaultNull()->end()
                                ->scalarNode('endpoint')->defaultValue('https://api.nadi.pro')->end()
                                ->scalarNode('version')->defaultValue('v1')->end()
                            ->end()
                        ->end()
                        ->arrayNode('opentelemetry')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('endpoint')->defaultValue('http://localhost:4318')->end()
                                ->scalarNode('service_name')->defaultValue('symfony-app')->end()
                                ->scalarNode('service_version')->defaultValue('1.0.0')->end()
                                ->scalarNode('deployment_environment')->defaultValue('production')->end()
                                ->booleanNode('suppress_errors')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('slow_threshold')->defaultValue(500)->end()
                    ->end()
                ->end()
                ->arrayNode('http')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('hidden_request_headers')
                            ->scalarPrototype()->end()
                            ->defaultValue(['authorization', 'php-auth-pw'])
                        ->end()
                        ->arrayNode('hidden_parameters')
                            ->scalarPrototype()->end()
                            ->defaultValue(['password', 'password_confirmation'])
                        ->end()
                        ->arrayNode('ignored_status_codes')
                            ->integerPrototype()->end()
                            ->defaultValue([100, 101, 102, 103, 200, 201, 202, 203, 204, 205, 206, 207, 300, 302, 303, 304, 305, 306, 307, 308])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sampling')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('strategy')->defaultValue('fixed_rate')->end()
                        ->arrayNode('config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->floatNode('sampling_rate')->defaultValue(0.1)->end()
                                ->floatNode('base_rate')->defaultValue(0.05)->end()
                                ->floatNode('load_factor')->defaultValue(1.0)->end()
                                ->integerNode('interval_seconds')->defaultValue(60)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
