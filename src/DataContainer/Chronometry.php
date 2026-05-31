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

namespace Markocupic\ChronometryBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;

class Chronometry
{
    public function __construct(private readonly ChronometryHelper $chronometryHelper)
    {
    }

    #[AsCallback(table: 'tl_chronometry', target: 'config.onload')]
    public function synchronizeTime(): void
    {
        $this->chronometryHelper->synchronizeTime();
    }
}
