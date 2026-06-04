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

namespace Markocupic\ChronometryBundle\Data;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum Status: string implements TranslatableLabelInterface
{
    case dnf = 'dnf';
    case finisher = 'finisher';
    case unranked = 'unranked';
    case notstarted = 'notstarted';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage(
            'status.'.$this->value,
            [],
            'chronometry',
        );
    }
}
