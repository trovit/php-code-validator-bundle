<?php

namespace Trovit\PhpCodeValidatorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class DynamicValidatorsCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function process(ContainerBuilder $container)
    {
        $validators = $container->getParameter('trovit_php_code_validator.validator_services');
        $validatorReferences = [];

        foreach ($validators as $validator) {
            if (!$container->hasDefinition($validator)) {
                throw new ServiceNotFoundException($validator);
            }
            $validatorReferences[] = new Reference($validator);
        }

        $container->getDefinition('trovit.php_code_validator.managers.validator_manager')
            ->setArguments([$validatorReferences]);
    }
}
