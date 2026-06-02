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
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license LGPL-3.0+
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/chronometry-bundle
 */

namespace Markocupic\ChronometryBundle\PhpOffice;

use Contao\Config;
use Contao\Controller;
use Contao\Date;
use Doctrine\DBAL\Connection;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\File;

readonly class RankingList
{
    public function __construct(
        private ChronometryHelper $chronometryHelper,
        private Connection $connection,
        private string $projectDir,
    ) {
    }

    public function generate(int $catId, bool $printEternalListOfTheBest): File
    {
        $strTable = 'tl_chronometry';

        // Load language file
        Controller::loadLanguageFile($strTable);

        $strTemplateSrc = Path::join($this->projectDir, 'vendor/markocupic/chronometry-bundle/docx/ranklist.docx');

        if (true === $printEternalListOfTheBest) {
            $strTemplateSrc = Path::join($this->projectDir, 'vendor/markocupic/chronometry-bundle/docx/eternal_list_of_the_best.docx');
        }

        $strTargetSrc = \sprintf('%s/system/tmp/rangliste_cat%s.docx', $this->projectDir, $catId);

        if ($printEternalListOfTheBest) {
            $strTargetSrc = \sprintf('%s/system/tmp/ewigenbestenliste_cat%s.docx', $this->projectDir, $catId);
        }

        $objPhpWord = new MsWordTemplateProcessor($strTemplateSrc, $strTargetSrc);

        $rowsA = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_chronometry WHERE published = 1 AND runningtimeUnix > 0 AND category = ? AND unranked = 0 AND dnf = 0 ORDER BY runningtimeUnix, number',
            [$catId],
        );

        $rowsB = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_chronometry WHERE published = 1 AND category = ? AND unranked = 1 ORDER BY runningtimeUnix, number',
            [$catId],
        );

        $rowsC = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_chronometry WHERE published = 1 AND category = ? AND unranked = 0 AND dnf = 1 ORDER BY runningtimeUnix, number',
            [$catId],
        );

        $rowsD = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_chronometry WHERE published = 1 AND runningtimeUnix < 1 AND category = ? AND unranked = 0 AND dnf = 0 ORDER BY runningtimeUnix, number',
            [$catId],
        );

        $rows = array_merge($rowsA, $rowsB, $rowsC, $rowsD);

        foreach ($rows as $row) {
            date_default_timezone_set('UTC');
            $time = Date::parse('H:i:s', (int) $row['runningtimeUnix']);
            $eventDate = Date::parse('d.m.Y', $row['eventDate']);

            date_default_timezone_set(Config::get('timeZone'));

            if ($row['unranked']) {
                $rank = 'o. Rang';
            } elseif ($row['dnf']) {
                $rank = 'd.n.f.';
            } elseif ($row['runningtimeUnix'] < 1) {
                $rank = '';
            } else {
                $rank = $this->chronometryHelper->getRank((int) $row['id']);
            }

            $objPhpWord->createClone('rank');
            $objPhpWord->addToClone('rank', 'rank', $rank, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'number', $row['number'], ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'firstname', $row['firstname'], ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'lastname', $row['lastname'], ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'time', $time > 0 ? $time : '', ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'eventDate', $eventDate, ['multiline' => false]);
        }

        // Category
        $category = $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$catId] ?? $catId;
        $objPhpWord->replace('category', $category, ['multiline' => false]);

        // Generate & send to browser
        return new File($objPhpWord->generate()->getRealPath());
    }
}
