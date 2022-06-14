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

use Contao\Config;
use Contao\DataContainer;

$GLOBALS['TL_DCA']['tl_chronometry'] = [
    'config'   => [
        'dataContainer'    => 'Table',
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'        => 'primary',
                'published' => 'index',
            ],
        ],
    ],
    'list'     => [
        'sorting'           => [
            'mode'        => DataContainer::MODE_SORTABLE,
            'fields'      => ['firstname ASC'],
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label'             => [
            'fields'      => ['number', 'firstname', 'lastname', 'published', 'stufe', 'starttime', 'runningtime'],
            'showColumns' => true,
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy'   => [
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.gif',
            ],
            'cut'    => [
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
            ],
            'delete' => [
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['toggle'],
                'href'  => 'act=toggle&amp;field=published',
                'icon'  => 'visible.svg',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    // Palettes
    'palettes' => [
        'default' => '{published_legend},published;{name_legend},firstname,lastname,stufe,gender,number,category,teachername,notice;{time_legend},eventDate,starttime,endtime,runningtime,runningtimeUnix,dnf',
    ],
    // Fields
    'fields'   => [
        'id'              => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'          => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'number'          => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'decodeEntities' => false, 'maxlength' => 3, 'tl_class' => 'w50'],
            'sql'       => "int(3) unsigned NOT NULL default '0'",
        ],
        'gender'          => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => ['male', 'female'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'stufe'           => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 1, 'tl_class' => 'w50'],
            'sql'       => "int(1) unsigned NOT NULL default '0'",
        ],
        'category'        => [
            'reference' => &$GLOBALS['TL_LANG']['tl_chronometry']['categories'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'options'   => Config::get('chronometry_bundle_categories'),
            'inputType' => 'select',
            'eval'      => ['mandatory' => true, 'maxlength' => 1, 'tl_class' => 'w50'],
            'sql'       => "varchar(1) NOT NULL default ''",
        ],
        'firstname'       => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'lastname'        => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'teachername'     => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'starttime'       => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'rgxp' => 'time_format_H:i:s', 'maxlength' => 8, 'tl_class' => 'w50'],
            'sql'       => "varchar(8) NOT NULL default ''",
        ],
        'eventDate'       => [
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_DAY_DESC,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "varchar(11) NOT NULL default ''",
        ],
        'endtime'         => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'time_format_H:i:s', 'maxlength' => 8, 'tl_class' => 'w50'],
            'sql'       => "varchar(8) NOT NULL default ''",
        ],
        'runningtime'     => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'time_format_H:i:s', 'maxlength' => 8, 'tl_class' => 'w50'],
            'sql'       => "varchar(8) NOT NULL default ''",
        ],
        'runningtimeUnix' => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'published'       => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['isBoolean' => true, 'doNotCopy' => true, 'tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'dnf'             => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['isBoolean' => true, 'doNotCopy' => true, 'tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'notice'          => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'textarea',
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
    ],
];
