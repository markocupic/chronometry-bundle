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
use Markocupic\ChronometryBundle\Data\Status;
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
        $table = $printEternalListOfTheBest ? 'tl_chronometry_archive' : 'tl_chronometry';

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
            "SELECT * FROM $table WHERE published = 1 AND runningtimeUnix > 0 AND category = ? AND status = ? ORDER BY runningtimeUnix, number",
            [$catId, Status::finisher->value],
        );

        $rowsB = $this->connection->fetchAllAssociative(
            "SELECT * FROM $table WHERE published = 1 AND category = ? AND status = ? ORDER BY runningtimeUnix, number",
            [$catId, Status::unranked->value],
        );

        $rowsC = $this->connection->fetchAllAssociative(
            "SELECT * FROM $table WHERE published = 1 AND category = ? AND status = ? ORDER BY runningtimeUnix, number",
            [$catId, Status::dnf->value],
        );

        $rowsD = $this->connection->fetchAllAssociative(
            "SELECT * FROM $table WHERE published = 1 AND category = ? AND status = ? ORDER BY runningtimeUnix, number",
            [$catId, Status::notstarted->value],
        );

        $rowsE = $this->connection->fetchAllAssociative(
            "SELECT * FROM $table WHERE published = 1 AND category = ? AND status = ? ORDER BY runningtimeUnix, number",
            [$catId, ''],
        );

        $rows = array_merge($rowsA, $rowsB, $rowsC, $rowsD, $rowsE);

        foreach ($rows as $row) {
            date_default_timezone_set('UTC');
            $time = Date::parse('H:i:s', (int) $row['runningtimeUnix']);
            $eventDate = Date::parse('d.m.Y', $row['eventDate']);

            date_default_timezone_set(Config::get('timeZone'));

            if ($row['status'] === Status::unranked->value) {
                $rank = 'o. Rang';
            } elseif ($row['status'] === Status::dnf->value) {
                $rank = 'd.n.f.';
            } elseif ($row['status'] === Status::notstarted->value) {
                $rank = 'n. gestartet';
            } elseif ($row['status'] === Status::finisher->value) {
                $rank = $this->chronometryHelper->getRank($row['id'], $table);
            } else {
                $rank = '';
            }

            $objPhpWord->createClone('rank');
            $objPhpWord->addToClone('rank', 'rank', $rank, ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'number', $row['number'], ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'firstname', $row['firstname'], ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'lastname', $row['lastname'], ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'time', $row['runningtimeUnix'] > 0 ? $time : '', ['multiline' => false]);
            $objPhpWord->addToClone('rank', 'eventDate', $eventDate, ['multiline' => false]);
        }

        // Category
        // Load language file
        Controller::loadLanguageFile('default');
        $category = $GLOBALS['TL_LANG']['CHRONOMETRY']['categories'][$catId] ?? $catId;
        $objPhpWord->replace('category', $category, ['multiline' => false]);

        // Generate & send to browser
        return new File($objPhpWord->generate()->getRealPath());
    }
}
