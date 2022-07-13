<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/dev',
        __DIR__ . '/tests'
    ])
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile('.php-cs-fixer.cache') // forward compatibility with 3.x line
    ;
