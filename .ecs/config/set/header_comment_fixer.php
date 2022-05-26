<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ECSConfig): void {

    $services = $ECSConfig->services();

    $services
        ->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => "This file is part of Chronometry Bundle.\n\n(c) Marko Cupic ".date('Y')." <m.cupic@gmx.ch>\n@license LGPL-3.0+\nFor the full copyright and license information,\nplease view the LICENSE file that was distributed with this source code.\n@link https://github.com/markocupic/chronometry-bundle",
        ]])
    ;
};
