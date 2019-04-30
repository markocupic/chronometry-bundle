<?php

/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */


/**
 * Table tl_calendar_events
 */
$GLOBALS['TL_DCA']['tl_chronometry'] = array(

    // Config
    'config' => array(
        'dataContainer' => 'Table',
        'switchToEdit' => true,
        'enableVersioning' => true,
        'onload_callback' => array
        (
            array('tl_chronometry', 'synchronizeTime'),
        ),
        'sql' => array(
            'keys' => array(
                'id' => 'primary',
                'published' => 'index',
            ),
        ),
    ),
    // List
    'list' => array(
        'sorting' => array(
            'mode' => 2,
            'fields' => array('firstname ASC'),
            'flag' => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ),
        'label' => array
        (
            'fields' => array('number', 'firstname', 'lastname', 'published', 'stufe', 'starttime', 'runningtime'),
            'showColumns' => true
        ),
        'global_operations' => array(
            'all' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ),
        ),
        'operations' => array(

            'edit' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['editmeta'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ),
            'copy' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.gif',
            ),
            'cut' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
            ),
            'delete' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'toggle' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                //'button_callback' => array('tl_chronometry', 'toggleIcon'),
            ),
            'show' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ),
        ),
    ),
    // Palettes
    'palettes' => array(
        //'__selector__' => array('addImage', 'addImage', 'addGallery', 'source'),
        'default' => '{published_legend},published;{name_legend},firstname,lastname,stufe,gender,number,category,teachername,notice;{time_legend},starttime,endtime,runningtime,runningtimeUnix,aufgegeben',
        // Subpalettes
        //'subpalettes' => array()
    ),
    // Fields
    'fields' => array(
        'id' => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp' => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'number' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['number'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'decodeEntities' => false, 'maxlength' => 3, 'tl_class' => 'w50'),
            'sql' => "int(3) unsigned NOT NULL default '0'",
        ),
        'gender' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['gender'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => array('male', 'female'),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => array('includeBlankOption' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50'),
            'sql' => "varchar(32) NOT NULL default ''"
        ),
        'stufe' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['stufe'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'decodeEntities' => false, 'maxlength' => 1, 'tl_class' => 'w50'),
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ),
        'category' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['category'],
            'reference' => &$GLOBALS['TL_LANG']['tl_chronometry']['categories'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'options' => Config::get('chronometry_categories'),
            'inputType' => 'select',
            'eval' => array('mandatory' => true, 'decodeEntities' => false, 'maxlength' => 1, 'tl_class' => 'w50'),
            'sql' => "varchar(1) NOT NULL default ''"
        ),
        'firstname' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['firstname'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('doNotCopy' => false, 'chosen' => true, 'mandatory' => false, 'tl_class' => 'clr'),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
        'lastname' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['lastname'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('doNotCopy' => false, 'chosen' => true, 'mandatory' => false, 'tl_class' => 'clr'),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
        'teachername' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['teachername'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('doNotCopy' => false, 'chosen' => true, 'mandatory' => false, 'tl_class' => 'clr'),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
        'starttime' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['starttime'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => false, 'decodeEntities' => false, 'rgxp' => 'chronometryTime', 'maxlength' => 8, 'tl_class' => 'w50'),
            'sql' => "varchar(8) NOT NULL default ''",
        ),
        'endtime' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['endtime'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => false, 'decodeEntities' => false, 'rgxp' => 'chronometryTime', 'maxlength' => 8, 'tl_class' => 'w50'),
            'sql' => "varchar(8) NOT NULL default ''",
        ),
        'runningtime' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['runningtime'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => false, 'decodeEntities' => false, 'rgxp' => 'chronometryTime', 'maxlength' => 8, 'tl_class' => 'w50'),
            'sql' => "varchar(8) NOT NULL default ''",
        ),
        'runningtimeUnix' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['runningtimeUnix'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => false, 'rgxp' => 'natural', 'tl_class' => 'w50'),

            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'published' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['published'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'flag' => 2,
            'inputType' => 'checkbox',
            'eval' => array('submitOnChange' => true, 'doNotCopy' => true, 'tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''",
        ),
        'aufgegeben' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['aufgegeben'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'flag' => 2,
            'inputType' => 'checkbox',
            'eval' => array('doNotCopy' => true, 'tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''",
        ),
        'notice' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_chronometry']['notice'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'textarea',
            'eval' => array('doNotCopy' => true, 'tl_class' => 'clr'),
            'sql' => "varchar(255) NOT NULL default ''",
        )
    )

);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_chronometry extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     *
     */
    public static function synchronizeTime()
    {
        Markocupic\Chronometry::synchronizeTime();
    }
}
