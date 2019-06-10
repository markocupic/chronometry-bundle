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

use Markocupic\Chronometry;
use Contao\Controller;
use Contao\Config;
use Contao\Database;
use Contao\Date;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;

/**
 * Class PrintRankingList
 * @package Markocupic\SacEventToolBundle\Services\PhpOffice
 */
class PrintRankingList
{

    /**
     * @param $catId
     * @param $strTemplateSrc
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function sendToBrowser($catId, $strTemplateSrc)
    {
        $strTable = 'tl_chronometry';
        Controller::loadLanguageFile($strTable);
        $strTargetSrc = sprintf('system/tmp/rangliste_cat%s.docx', $catId);
        $objPhpWord = MsWordTemplateProcessor::create($strTemplateSrc, $strTargetSrc);

        $objRow = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE category=? AND runningtimeUnix > ? ORDER BY runningTimeUnix')->execute($catId, 0);

        while ($objRow->next())
        {
            date_default_timezone_set('UTC');
            $time = Date::parse('H:i:s', $objRow->runningtimeUnix);
            date_default_timezone_set(Config::get('timeZone'));

            $objPhpWord->createClone('rank');
            $objPhpWord->addToClone('rank', 'rank', Chronometry::getRank($objRow->id), array('multiline' => false));
            $objPhpWord->addToClone('rank', 'number', $objRow->number, array('multiline' => false));
            $objPhpWord->addToClone('rank', 'firstname', $objRow->firstname, array('multiline' => false));
            $objPhpWord->addToClone('rank', 'lastname', $objRow->lastname, array('multiline' => false));
            $objPhpWord->addToClone('rank', 'time', $time, array('multiline' => false));
        }

        // dnf
        $objRow = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE category=? AND dnf = ? ORDER BY lastname')->execute($catId, '1');
        while ($objRow->next())
        {
            $objPhpWord->createClone('rank');
            $objPhpWord->addToClone('rank', 'rank', '---', array('multiline' => false));
            $objPhpWord->addToClone('rank', 'number', $objRow->number, array('multiline' => false));
            $objPhpWord->addToClone('rank', 'firstname', $objRow->firstname, array('multiline' => false));
            $objPhpWord->addToClone('rank', 'lastname', $objRow->lastname, array('multiline' => false));
            $objPhpWord->addToClone('rank', 'time', 'dnf', array('multiline' => false));
        }

        // Category
        $category = isset($GLOBALS['TL_LANG']['tl_chronometry']['categories'][$catId]) ? $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$catId] : $catId;
        $objPhpWord->replace('category', $category, array('multiline' => false));

        // Create & download
        $objPhpWord->sendToBrowser(true)
            ->generateUncached(true)
            ->generate();
    }

}
