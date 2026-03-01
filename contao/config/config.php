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

use Contao\ArrayUtil;
use Markocupic\ChronometryBundle\Model\ChronometryModel;

/*
 * Back end modules
 */
ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['content'], 1, [
    'chronometry' => [
        'tables' => ['tl_chronometry'],
        'table' => ['TableWizard', 'importTable'],
        'list' => ['ListWizard', 'importList'],
    ],
]);

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_chronometry'] = ChronometryModel::class;

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
