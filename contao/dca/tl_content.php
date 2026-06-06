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

use Markocupic\ChronometryBundle\Controller\ContentElement\ChronometryListController;

/*
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_content']['palettes'][ChronometryListController::TYPE] = '{title_legend},name,headline,type;{config_legend};{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
