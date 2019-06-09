<?php
/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */

declare(strict_types=1);

namespace Markocupic\SacEventToolBundle\Services\PhpOffice;

use Contao\ChronometryModel;
use Markocupic\Chronometry;
use Contao\Controller;
use Contao\Config;
use Contao\Date;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;

/**
 * Class PrintCertificate
 * @package Markocupic\SacEventToolBundle\Services\PhpOffice
 */
class PrintCertificate
{

    /**
     * @param $rowId
     * @param $strTemplateSrc
     * @return bool
     */
    public function sendToBrowser($rowId, $strTemplateSrc)
    {
        $strTable = 'tl_chronometry';
        Controller::loadDataContainer('tl_calendar_events');
        Controller::loadLanguageFile($strTable);

        $objChronometry = ChronometryModel::findByPk($rowId);
        if ($objChronometry === null)
        {
            return false;
        }

        $rank = Chronometry::getRank($objChronometry->id);

        date_default_timezone_set('UTC');
        $time = Date::parse('H:i:s', $objChronometry->runningtimeUnix);
        date_default_timezone_set(Config::get('timeZone'));

        $category = isset($GLOBALS['TL_LANG']['tl_chronometry']['categories'][$objChronometry->category]) ? $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$objChronometry->category] : $objChronometry->category;
        $arrData = [];
        $arrData[] = array('key' => 'firstname', 'value' => $objChronometry->firstname, 'options' => array('multiline' => false));
        $arrData[] = array('key' => 'lastname', 'value' => $objChronometry->lastname, 'options' => array('multiline' => false));
        $arrData[] = array('key' => 'category', 'value' => $category, 'options' => array('multiline' => false));
        $arrData[] = array('key' => 'time', 'value' => $time, 'options' => array('multiline' => false));

        $arrData[] = array('key' => 'rank', 'value' => $rank, 'options' => array('multiline' => false));

        $strTargetSrc = sprintf('system/tmp/certificate_cat%s_rank%s_%s_%s.docx', $objChronometry->category, $rank, $objChronometry->firstname, $objChronometry->lastname);

        // Create & download
        MsWordTemplateProcessor::create($arrData, $strTemplateSrc, $strTargetSrc)
            ->sendToBrowser(true)
            ->generateUncached(true)
            ->generate();
    }

}
