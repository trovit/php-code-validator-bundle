<?php

namespace Trovit\PhpCodeValidatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws InvalidTypeException
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('trovit_php_code_validator');

        $rootNode
            ->children()
                ->scalarNode('temporary_path')
                    ->info('Path where the temporary files are going to be created if needed.')
                    ->isRequired()
                    ->validate()
                    ->always(function ($v) {
                        if (is_dir($v)) {
                            return $v;
                        }
                        throw new InvalidTypeException('Temporary path is not a valid directory.'
                            .PHP_EOL.'Read the docs of the repo for more information.');
                    })->end()
                ->end()
            ->end()
        ;

        $rootNode
            ->children()
                ->variableNode('validator_services')
                    ->info('Array of strings where each string is the service reference name of a validator')
                    ->defaultValue(array(
                        'trovit.php_code_validator.validators.parallel_lint_validator',
                    ))
                    ->validate()
                    ->ifString()
                        ->then(function ($validator) {
                            return array($validator);
                        })
                    ->end()
                ->end()
            ->end();

        $rootNode
            ->children()
                ->variableNode('php_cs_config')
                    ->info('Configuration of Php Code Sniffer')
                    ->defaultValue([
                        'reports' => ['json' => null],
                        'verbosity' => 0,
                        'showProgress' => false,
                        'interactive' => false,
                        'cache' => false,
                        'showSources' => true,
                    ])
                    ->beforeNormalization()
                    ->always(function ($v) {
                        if (!is_array($v)) {
                            throw new InvalidTypeException('Configuration of Php Code Sniffer should be an array');
                        }
                        $v['reports']['json'] = null;
                        $v['verbosity'] = 0;
                        $v['showProgress'] = false;
                        $v['interactive'] = false;
                        $v['cache'] = false;
                        $v['showSources'] = true;
                        return $v;
                    })
                ->end()
            ->end();
        return $treeBuilder;
    }
}
