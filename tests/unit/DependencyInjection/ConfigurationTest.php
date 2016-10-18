<?php

namespace Trovit\PhpCodeValidatorBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Trovit\PhpCodeValidatorBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testTemporaryPathWithoutValueAndIsRequired()
    {
        $this->assertConfigurationIsInvalid(array(array()), 'temporary_path');
    }

    public function testTemporaryPathIsNotAValidFilePath()
    {
        $this->assertConfigurationIsInvalid(
            array(
                array('temporary_path' => 'not a valid file path :('),
            ),
            'Temporary path is not a valid directory.'
        );
    }

    public function testTemporaryPathIsAValidFilePath()
    {
        $this->assertConfigurationIsValid(
            array(
                array('temporary_path' => __DIR__),
            )
        );
    }

    public function testDefaultValidator()
    {
        $this->assertProcessedConfigurationEquals(
            array(),
            array(
                'validator_services' => array(
                    'trovit.php_code_validator.validators.parallel_lint_validator',
                ),
            ),
            'validator_services'
        );
    }

    public function testOnlyOneValidatorAsString()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array('validator_services' => 'trovit.php_code_validator.validators.php_cs_validator'),
            ),
            array(
                'validator_services' => array(
                    'trovit.php_code_validator.validators.php_cs_validator',
                ),
            ),
            'validator_services'
        );
    }

    public function testDefaultCodeSnifferConfig()
    {
        $this->assertProcessedConfigurationEquals(
            array(),
            array(
                'php_cs_config' => array(
                    'reports' => ['json' => null],
                    'verbosity' => 0,
                    'showProgress' => false,
                    'interactive' => false,
                    'cache' => false,
                    'showSources' => true
                ),
            ),
            'php_cs_config'
        );
    }
}
