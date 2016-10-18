<?php

namespace Trovit\PhpCodeValidatorBundle\Tests\Functional\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Trovit\PhpCodeValidator\Entity\PhpCodeValidatorProblem;
use Trovit\PhpCodeValidatorBundle\TrovitPhpCodeValidatorBundle;

class TrovitPhpCodeValidatorExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadWithTemporaryFile()
    {
        $config['temporary_path'] = __DIR__;
        $container = $this->getContainerForConfig([$config]);
        $validatorManager = $container->get('trovit.php_code_validator.managers.validator_manager');
        $result = $validatorManager->execute('<?php echo "hola ?>');
        $this->assertTrue($result->hasErrors());
    }

    public function testLoadWithoutConfig()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child node "temporary_path" at path '
            .'"trovit_php_code_validator" must be configured.');
        $this->getContainerForConfig([[]]);
    }

    public function testLoadWithGoodServiceValidatorAndCodeSnifferError()
    {
        $config['temporary_path'] = __DIR__;
        $config['validator_services'] = [
            'trovit.php_code_validator.validators.php_cs_validator',
            'trovit.php_code_validator.validators.parallel_lint_validator',
        ];
        $container = $this->getContainerForConfig([$config]);
        $validatorManager = $container->get('trovit.php_code_validator.managers.validator_manager');
        $result = $validatorManager->execute('<?php echo "hola ?>');
        $this->assertTrue($result->hasErrors());
        $this->assertEquals(
            [
                (new PhpCodeValidatorProblem())
                    ->setMessage('Missing file doc comment')
                    ->setErrorType(PhpCodeValidatorProblem::ERROR_TYPE)
                    ->setLineNum(1)
                    ->setColumnNum(7)
                    ->setErrorName('Code Sniffer Error')
            ],
            $result->getErrors()
        );
    }

    public function testLoadWithGoodServiceValidatorAndParallelLintError()
    {
        $config['temporary_path'] = __DIR__;
        $config['validator_services'] = [
            'trovit.php_code_validator.validators.parallel_lint_validator',
            'trovit.php_code_validator.validators.php_cs_validator',
        ];
        $container = $this->getContainerForConfig([$config]);
        $validatorManager = $container->get('trovit.php_code_validator.managers.validator_manager');
        $result = $validatorManager->execute('<?php echo "hola ?>');
        $this->assertTrue($result->hasErrors());
        $this->assertEquals(
            [
                (new PhpCodeValidatorProblem())
                    ->setMessage('Unexpected end of file, expecting variable '.
                        '(T_VARIABLE) or ${ (T_DOLLAR_OPEN_CURLY_BRACES) or {$ (T_CURLY_OPEN)')
                    ->setErrorType(PhpCodeValidatorProblem::ERROR_TYPE)
                    ->setLineNum(1)
                    ->setColumnNum(null)
                    ->setErrorName('Parallel Lint Error')
            ],
            $result->getErrors()
        );
    }

    public function testLoadWithBadServiceValidator()
    {
        $config['temporary_path'] = __DIR__;
        $config['validator_services'] = ['bad_fake_service'];
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "bad_fake_service".');
        $this->getContainerForConfig([$config]);
    }

    private function getContainerForConfig(array $configs)
    {
        $kernel = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')
            ->getMock();

        $kernel
            ->expects($this->any())
            ->method('getBundles')
            ->will($this->returnValue(array()));

        $bundle = new TrovitPhpCodeValidatorBundle($kernel);
        $container = new ContainerBuilder();
        $extension = $bundle->getContainerExtension();
        $container->registerExtension($extension);
        $extension->load($configs, $container);
        $bundle->build($container);
        $container->compile();

        return $container;
    }
}
