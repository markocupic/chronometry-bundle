<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Markocupic',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Classes
    'Markocupic\Chronometry'        => 'system/modules/chronometry/classes/Chronometry.php',

    // Models
    'Contao\ChronometryModel'       => 'system/modules/chronometry/models/ChronometryModel.php',

    // Modules
    'Markocupic\ChronometryList' => 'system/modules/chronometry/modules/ChronometryList.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_chronometry_startliste' => 'system/modules/chronometry/templates',
));
