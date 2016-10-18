<?php

namespace Trovit\PhpCodeValidatorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Trovit\PhpCodeValidatorBundle\DependencyInjection\DynamicValidatorsCompilerPass;

class TrovitPhpCodeValidatorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DynamicValidatorsCompilerPass());
    }
}
