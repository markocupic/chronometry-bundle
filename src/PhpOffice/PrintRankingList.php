<?php
/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic.
 *
 * @see https://github.com/markocupic/chronometry-bundle
 */

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

namespace Markocupic\ChronometryBundle\PhpOffice;

use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Date;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;

class PrintRankingList
{
    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function sendToBrowser(int $catId, string $strTemplateSrc, bool $printEternalListOfTheBest): void
    {
        $strTable = 'tl_chronometry';

        // Load language file
        Controller::loadLanguageFile($strTable);

        $strTargetSrc = sprintf('system/tmp/rangliste_cat%s.docx', $catId);

        if ($printEternalListOfTheBest) {
            $strTargetSrc = sprintf('system/tmp/ewigenbestenliste_cat%s.docx', $catId);
        }

        $objPhpWord = new MsWordTemplateProcessor($strTemplateSrc, $strTargetSrc);

        $objRow = Database::getInstance()
            ->prepare('SELECT * FROM tl_chronometry WHERE category = ? AND runningtimeUnix > ? ORDER BY runningTimeUnix')
            ->execute($catId, 0)
        ;

        while ($objRow->next()) {
            date_default_timezone_set('UTC');
            $time = Date::parse('H:i:s', $objRow->runningtimeUnix);
            $eventDate = Date::parse('d.m.Y', $objRow->eventDate);

            date_default_timezone_set(Config::get('timeZone'));

            $objPhpWord->createClone('rank');
            $objPhpWord->addToClone('rank', 'rank', ChronometryHelper::getRank((int) $objRow->id), ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'number', $objRow->number, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'firstname', $objRow->firstname, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'lastname', $objRow->lastname, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'time', $time, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'eventDate', $eventDate, ['multiline' => false]);
        }

        // dnf
        $objRow = Database::getInstance()
            ->prepare('SELECT * FROM tl_chronometry WHERE category = ? AND dnf = ? ORDER BY lastname')
            ->execute($catId, '1')
        ;

        while ($objRow->next()) {
            $objPhpWord->createClone('rank');
            $objPhpWord->addToClone('rank', 'rank', '---', ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'number', $objRow->number, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'firstname', $objRow->firstname, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'lastname', $objRow->lastname, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'time', 'dnf', ['multiline' => false]);
        }

        // Category
        $category = $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$catId] ?? $catId;
        $objPhpWord->replace('category', $category, ['multiline' => false]);

        // Generate & send to browser
        $objPhpWord->sendToBrowser(true, true)
            ->generateUncached(true)
            ->generate()
        ;
    }
}
