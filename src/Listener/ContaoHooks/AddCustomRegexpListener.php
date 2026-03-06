<?php

declare(strict_types=1);

/*
 * This file is part of Chronometry Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license LGPL-3.0+
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/chronometry-bundle
 */

namespace Markocupic\ChronometryBundle\Listener\ContaoHooks;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Widget;
use Markocupic\ChronometryBundle\Validator\ValidatorTime;

#[AsHook('addCustomRegexp', priority: 100)]
class AddCustomRegexpListener
{
    private ValidatorTime $validatorTime;

    public function __construct(ValidatorTime $validatorTime)
    {
        $this->validatorTime = $validatorTime;
    }

    public function __invoke(string $strRegexp, $varValue, Widget $objWidget): bool
    {
        if ('time_format_H:i:s' === $strRegexp) {
            if (!$this->validatorTime->isValidTimeFormat($varValue)) {
                $objWidget->addError('Field '.$objWidget->label.' should be a valid time format hh:mm:ss.');
            }

            return true;
        }

        return false;
    }
}
