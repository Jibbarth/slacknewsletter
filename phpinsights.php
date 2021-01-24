<?php

declare(strict_types=1);

return [
    'preset' => 'symfony',
    'ide' => null,
    'exclude' => [
        'src/Kernel.php',
    ],
    'add' => [
        \NunoMaduro\PhpInsights\Domain\Metrics\Code\Code::class => [
            \SlevomatCodingStandard\Sniffs\ControlStructures\RequireYodaComparisonSniff::class,
        ],
    ],
    'remove' => [
        \SlevomatCodingStandard\Sniffs\Classes\SuperfluousInterfaceNamingSniff::class,
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff::class,
        \SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff::class,
        \NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits::class,
        \NunoMaduro\PhpInsights\Domain\Insights\ForbiddenSecurityIssues::class,
        \SlevomatCodingStandard\Sniffs\Classes\SuperfluousTraitNamingSniff::class,
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting\TodoSniff::class,
    ],
    'config' => [
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 160,
        ],
        \PhpCsFixer\Fixer\Import\OrderedImportsFixer::class => [
            'imports_order' => ['const', 'class', 'function'],
        ],
        \SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff::class => [
            'exclude' => [
                'src/Parser/SlackMessageParser.php',
                'src/Service/Slack/BrowseService.php',
            ],
        ],
        \SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff::class => [
            'exclude' => [
                'src/Command',
            ],
        ],
        \ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff::class => [
            'maxLength' => 50,
        ],
    ],
    'requirements' => [
        'min-quality' => 100,
        // 'min-complexity' => 0,
        'min-architecture' => 100,
        'min-style' => 100,
    ],
];
