<?php

/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */


/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], 1, array
(
	'chronometry' => array
	(
		'tables'      => array('tl_chronometry'),
		'table'       => array('TableWizard', 'importTable'),
		'list'        => array('ListWizard', 'importList')
	)
));


/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, array
(
	'chronometry' => array
	(
        'chronometryList'    => 'Markocupic\ChronometryList',
	)
));

$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = array('Markocupic\Chronometry', 'customRegexp');

// Asset path
define('MOD_CHRONOMETRY_ASSET_PATH', 'bundles/markocupicchronometry');

// Categories
// @see references in $GLOBALS['TL_LANG']['tl_chronometry']['categories']
$GLOBALS['TL_CONFIG']['chronometry_categories'] = array('1', '2', '3', '4');
