<?php

declare(strict_types=1);

use Contao\EasyCodingStandard\Set\SetList;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return ECSConfig::configure()
    ->withSets([
        SetList::CONTAO,
        \Markocupic\EasyCodingStandard\Set\SetList::MARKOCUPIC,
    ])
    ->withPaths([
        __DIR__ . '/../../src',
    ])
    ->withSkip([
        '*.docx',
        '*.jpg',
        '*.jpeg',
        '*.png',
        '*.ttf',
        \Contao\EasyCodingStandard\Fixer\CommentLengthFixer::class         => ['*.php'],
        \PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer::class => [
            '*/DependencyInjection/Configuration.php',
        ],
    ])
    ->withRootFiles()
    ->withParallel()
    ->withSpacing(Option::INDENTATION_SPACES, "\n")
    ->withConfiguredRule(HeaderCommentFixer::class, [
        'header' => "This file is part of Chronometry Bundle.\n\n(c) Marko Cupic <m.cupic@gmx.ch>\n@license LGPL-3.0+\nFor the full copyright and license information,\nplease view the LICENSE file that was distributed with this source code.\n@link https://github.com/markocupic/chronometry-bundle",
    ])
    ->withCache(sys_get_temp_dir() . '/ecs/markocupic/chronometry-bundle');
