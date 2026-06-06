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

namespace Markocupic\ChronometryBundle\EventListener\ContaoHooks;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Widget;
use Markocupic\ChronometryBundle\Validator\TimeValidator;

#[AsHook('addCustomRegexp', priority: 100)]
class AddCustomRegexpListener
{
    public function __construct(private readonly TimeValidator $validatorTime)
    {
    }

    public function __invoke(string $strRegexp, $varValue, Widget $widget): bool
    {
        if ('time_format_H:i:s' === $strRegexp) {
            if (!$this->validatorTime->isValidTimeFormat($varValue)) {
                $widget->addError(\sprintf('Field "%s" should be a valid time format hh:mm:ss.', $widget->label));
            }

            return true;
        }

        return false;
    }
}
