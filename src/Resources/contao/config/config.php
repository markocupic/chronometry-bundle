<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
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
