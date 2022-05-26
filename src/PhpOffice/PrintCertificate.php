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

namespace Markocupic\ChronometryBundle\PhpOffice;

use Contao\Config;
use Contao\Controller;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;
use Markocupic\ChronometryBundle\Model\ChronometryModel;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;

class PrintCertificate
{
    public function sendToBrowser(int $rowId, string $strTemplateSrc): bool
    {
        $strTable = 'tl_chronometry';

        // Load DCA
        Controller::loadDataContainer('tl_calendar_events');

        // Load langueage file
        Controller::loadLanguageFile($strTable);

        $objChronometry = ChronometryModel::findByPk($rowId);

        if (null === $objChronometry) {
            return false;
        }

        // Get category
        $category = $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$objChronometry->category] ?? $objChronometry->category;

        // Get rank
        $rank = ChronometryHelper::getRank((int) $objChronometry->id);

        // Get time
        date_default_timezone_set('UTC');
        $time = date('H:i:s', (int) $objChronometry->runningtimeUnix);
        date_default_timezone_set(Config::get('timeZone'));

        // Set target filename
        $strTargetSrc = sprintf(
            'system/tmp/certificate_cat%s_rank%s_%s_%s.docx',
            $objChronometry->category,
            $rank,
            $objChronometry->firstname,
            $objChronometry->lastname
        );

        // Instantiate template processor
        $objPhpWord = new MsWordTemplateProcessor($strTemplateSrc, $strTargetSrc);

        $objPhpWord->replace('firstname', $objChronometry->firstname, ['multiline' => false]);
        $objPhpWord->replace('lastname', $objChronometry->lastname, ['multiline' => false]);
        $objPhpWord->replace('category', $category, ['multiline' => false]);
        $objPhpWord->replace('rank', $rank, ['multiline' => false]);
        $objPhpWord->replace('time', $time, ['multiline' => false]);

        // Generate & send to browser
        $objPhpWord->sendToBrowser(true, true)
            ->generateUncached(true)
            ->generate()
        ;
    }
}
