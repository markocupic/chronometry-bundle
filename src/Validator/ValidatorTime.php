<?php

declare(strict_types=1);

/*
 * This file is part of Chronometry Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license LGPL-3.0+
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/chronometry-bundle
 */

namespace Markocupic\ChronometryBundle\Validator;

class ValidatorTime
{
    public function isValidTimeFormat(string $strFormattedTime = ''): bool
    {
        if ('' === $strFormattedTime) {
            return true;
        }

        if (preg_match('/^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/', $strFormattedTime)) {
            return true;
        }

        return false;
    }
}
