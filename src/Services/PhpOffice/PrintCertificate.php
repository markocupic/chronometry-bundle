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

        // Get category
        $category = isset($GLOBALS['TL_LANG']['tl_chronometry']['categories'][$objChronometry->category]) ? $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$objChronometry->category] : $objChronometry->category;

        // Get rank
        $rank = Chronometry::getRank($objChronometry->id);

        // Get time
        date_default_timezone_set('UTC');
        $time = Date::parse('H:i:s', $objChronometry->runningtimeUnix);
        date_default_timezone_set(Config::get('timeZone'));

        // Set target filename
        $strTargetSrc = sprintf('system/tmp/certificate_cat%s_rank%s_%s_%s.docx', $objChronometry->category, $rank, $objChronometry->firstname, $objChronometry->lastname);

        // Instantiate template processor
        $objPhpWord = MsWordTemplateProcessor::create($strTemplateSrc, $strTargetSrc);
        $objPhpWord->replace('firstname', $objChronometry->firstname, array('multiline' => false));
        $objPhpWord->replace('lastname', $objChronometry->lastname, array('multiline' => false));
        $objPhpWord->replace('category', $category, array('multiline' => false));
        $objPhpWord->replace('rank', $rank, array('multiline' => false));
        $objPhpWord->replace('time', $time, array('multiline' => false));

        // Generate & send to browser
        $objPhpWord->sendToBrowser(true)->generateUncached(true)->generate();
    }

}
