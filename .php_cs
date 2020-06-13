<?php

// Customize your own with https://mlocati.github.io/php-cs-fixer-configurator/

return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'comment_to_phpdoc' => true,
        'declare_strict_types' => true,
        'final_class' => true,
        'header_comment' => ['header' => ''],
        'heredoc_to_nowdoc' => true,
        'increment_style' => false,
        'list_syntax' => ['syntax' => 'short'],
        'mb_str_functions' => true,
        'method_chaining_indentation' => false,
        'modernize_types_casting' => true,
        'multiline_whitespace_before_semicolons' => false,
        'native_function_invocation' => true,
        'no_superfluous_phpdoc_tags' => false,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_order' => true,
        'phpdoc_no_package' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        'php_unit_test_class_requires_covers' => false,
        'protected_to_private' => true,
        'self_accessor' => false,
        'single_line_comment_style' => null,
        'single_line_throw' => false,
        'yoda_style' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__ . '/src/')
        ->notName('Kernel.php')
    )
;
