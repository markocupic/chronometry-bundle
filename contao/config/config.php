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

use Contao\ArrayUtil;
use Markocupic\ChronometryBundle\Model\ChronometryModel;
use Markocupic\ChronometryBundle\Model\ChronometryArchiveModel;

/*
 * Back end modules
 */
ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['chronometry'], 1, [
    'chronometry'         => [
        'tables' => ['tl_chronometry'],
        'table'  => ['TableWizard', 'importTable'],
        'list'   => ['ListWizard', 'importList'],
    ],
    'chronometry_archive' => [
        'tables' => ['tl_chronometry_archive'],
        'table'  => ['TableWizard', 'importTable'],
        'list'   => ['ListWizard', 'importList'],
    ],
]);

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_chronometry'] = ChronometryModel::class;
$GLOBALS['TL_MODELS']['tl_chronometry_archive'] = ChronometryArchiveModel::class;

/*
 * Asset path
 */
define('MOD_CHRONOMETRY_ASSET_PATH', 'bundles/markocupicchronometry');

/*
 * Categories
 * @see references in $GLOBALS['TL_LANG']['tl_chronometry']['categories']
 */
$GLOBALS['TL_CONFIG']['chronometry_bundle_categories'] = ['1', '2', '3', '4'];

/*
 * Backup path
 */
$GLOBALS['TL_CONFIG']['chronometry_bundle_backup_path'] = 'files/chronometry_backup/%schronometry.csv';
