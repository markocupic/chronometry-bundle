<?php
/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */

declare(strict_types=1);

namespace Markocupic\SacEventToolBundle\Services\Docx;

use Markocupic\Chronometry;
use Contao\Controller;
use Contao\Config;
use Contao\Database;
use Contao\Date;
use Markocupic\PhpOffice\PhpWord\GenerateDocxFromTemplate;

/**
 * Class PrintRanklist
 * @package Markocupic\SacEventToolBundle\Services\Docx
 */
class PrintRanklist
{

    /**
     * @param $rowId
     * @param $strTemplateSrc
     * @return bool
     */
    public function sendToBrowser($catId, $strTemplateSrc)
    {
        $strTable = 'tl_chronometry';
        Controller::loadLanguageFile($strTable);

        $arrData = [];
        $rows = [];
        $objRow = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE category=? AND runningtimeUnix > ? ORDER BY runningTimeUnix')->execute($catId, 0);

        while ($objRow->next())
        {
            date_default_timezone_set('UTC');
            $time = Date::parse('H:i:s', $objRow->runningtimeUnix);
            date_default_timezone_set(Config::get('timeZone'));
            $rows[] = array(
                array('key' => 'rank', 'value' => Chronometry::getRank($objRow->id), 'options' => array('multiline' => false)),
                array('key' => 'number', 'value' => $objRow->number, 'options' => array('multiline' => false)),
                array('key' => 'firstname', 'value' => $objRow->firstname, 'options' => array('multiline' => false)),
                array('key' => 'lastname', 'value' => $objRow->lastname, 'options' => array('multiline' => false)),
                array('key' => 'time', 'value' => $time, 'options' => array('multiline' => false)),
            );
        }

        // dnf
        $objRow = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE category=? AND dnf = ? ORDER BY lastname')->execute($catId, '1');
        while ($objRow->next())
        {
            $rows[] = array(
                array('key' => 'rank', 'value' => '--', 'options' => array('multiline' => false)),
                array('key' => 'number', 'value' => $objRow->number, 'options' => array('multiline' => false)),
                array('key' => 'firstname', 'value' => $objRow->firstname, 'options' => array('multiline' => false)),
                array('key' => 'lastname', 'value' => $objRow->lastname, 'options' => array('multiline' => false)),
                array('key' => 'time', 'value' => 'dnf', 'options' => array('multiline' => false)),
            );
        }

        $arrData[] = array('clone' => 'rank', 'rows' => $rows);

        // Category
        $category = isset($GLOBALS['TL_LANG']['tl_chronometry']['categories'][$catId]) ? $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$catId] : $catId;
        $arrData[] = array('key' => 'category', 'value' => $category, 'options' => array('multiline' => false));

        $strTargetSrc = sprintf('system/tmp/rangliste_cat%s.docx', $catId);

        // Create & download
        GenerateDocxFromTemplate::create($arrData, $strTemplateSrc, $strTargetSrc)
            ->sendToBrowser(true)
            ->generateUncached(true)
            ->generate();
    }

}
