# Php Code Validator Bundle
[![Build Status](https://secure.travis-ci.org/trovit/php-code-validator-bundle.png)](http://travis-ci.org/trovit/php-code-validator-bundle) 

Symfony bundle which provides a basic system to organize and execute php code validators.

## Installation

### Step 1: Require bundle using composer

```Shell
$ composer require trovit/php-validator-bundle "^1.0"
```


### Step 2: Enable the bundle

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Trovit\PhpCodeValidatorBundle\PhpCodeValidatorBundle(),
        // ...
    );
}
```

### Step 3: Configure the bundle  

There are only 2 parameters available at the moment:

- *temporary_path* _(string)_: temporary path where the temporary files should be created. This is necessary for those validator libraries that only works with filesystem.

- *validator_services* _(string[])_: each string represents the reference name of a validator service

- *php_cs_config* _(string[])_: each string represents one of the configurations available in [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer)


Example:
```yaml
# app/config.yml
trovit_php_code_validator:
    temporary_path: "%kernel.cache_dir%/tmp/"
    validator_services:
      - 'trovit.php_code_validator.validators.php_cs_validator'
    php_cs_config:
        - reports:
            json: ~
        - verbosity: 0
        - showProgress: false
        - interactive: false
        - cache: false
        - showSources: true
```
### Step 4 (optional): Create your own Validator

When you need to format your code and the validators provided by this bundle doesn't satisfy your needs (different code language, formats, etc...) there is the possibility to create a new Validator class by implementing the Validator interface (_Trovit\PhpCodeValidator\Validators\Validator_) and implement its method *formatCode*

After that, you have to register the validator as a service and add the service reference name in the config (_check step 3_).


## Usage

Get the manager service wherever you want to call the method *execute* with the bad code (syntax errors for example) as a parameter. It will return the errors in a PhpCodeValidatorResult object.

Example with a php lint ([PrallelLint](https://github.com/JakubOnderka/PHP-Parallel-Lint)):
```php
// src/AppBundle/Controller/DefaultController.php

$code = '<?php echo "hola" ?>'; //missing ;

// This will return a PhpCodeValidatorResult object wich contains an array of detected problems
$result = $this->get('trovit.php_code_validator.managers.validator_manager')->execute($code);

$result->hasErrors(); // will return 1 (hasWarnings() is also available if needed)

$result->getErrors(); //will return an array of PhpCodeValidatorProblem:

/*
    new PhpCodeValidatorProblem()
    ->setMessage('Unexpected end of file, expecting variable '.
        '(T_VARIABLE) or ${ (T_DOLLAR_OPEN_CURLY_BRACES) or {$ (T_CURLY_OPEN)')
    ->setErrorType(PhpCodeValidatorProblem::ERROR_TYPE)
    ->setLineNum(1)
    ->setColumnNum(null)
    ->setErrorName('Parallel Lint Error');
*/
```

## List of available validators

- *CodeSnifferValidator*: Wrapper of [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- *ParallelLintValidator*: Wrapper of [Parallel Lint Validator](https://github.com/JakubOnderka/PHP-Parallel-Lint)

Feel free to add more validators and contribute by PR!
