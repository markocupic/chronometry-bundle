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

namespace Markocupic\ChronometryBundle\Helper;

use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Widget;
use Markocupic\ChronometryBundle\Model\ChronometryModel;

class ChronometryHelper
{
    public static function getRowAsObject(array $row): \stdClass
    {
        $objRow = new \stdClass();

        foreach ($row as $k => $v) {
            $objRow->{$k} = $v;
        }
        $objRow->fullname = $row['firstname'].' '.$row['lastname'];
        $objRow->runningtimeUnix = static::makeTimestamp($row['runningtime']);
        $objRow->starttimeUnix = static::makeTimestamp($row['starttime']);
        $objRow->endtimeUnix = static::makeTimestamp($row['endtime']);
        $objRow->rank = static::getRank((int) $row['id']);
        $objRow->requesting = false;

        return $objRow;
    }

    public static function getRank(int $id): int
    {
        $objAthlete = ChronometryModel::findByPk($id);

        if (null !== $objAthlete) {
            if ($objAthlete->runningtimeUnix < 1) {
                return 0;
            }

            $objDb = Database::getInstance()
                ->prepare('SELECT * FROM tl_chronometry WHERE runningtimeUnix > 0 AND published = ? AND category = ? ORDER BY runningtimeUnix')
                ->execute(1, $objAthlete->category)
            ;

            $values = $objDb->fetchEach('runningtimeUnix');

            $i = 1;
            $dupl = 0;
            $lastScore = '';

            foreach ($values as $score) {
                if ($lastScore === $score) {
                    ++$dupl;
                } else {
                    $dupl = 0;
                }

                if ($score === $objAthlete->runningtimeUnix) {
                    return $i - $dupl;
                }
                ++$i;
                $lastScore = $score;
            }
        }

        return 0;
    }

    public static function getStats(): \stdClass
    {
        $objChronometry = Database::getInstance()
            ->prepare('SELECT * FROM tl_chronometry WHERE published = ?')
            ->execute(0)
        ;
        $dispensed = $objChronometry->numRows;

        $objChronometry = Database::getInstance()
            ->prepare('SELECT * FROM tl_chronometry')
            ->execute()
        ;
        $total = $objChronometry->numRows;

        $objChronometry = Database::getInstance()
            ->prepare('SELECT * FROM tl_chronometry WHERE published = ? AND dnf = ?')
            ->execute(1, 1)
        ;
        $dnf = $objChronometry->numRows;

        $objChronometry = Database::getInstance()
            ->prepare('SELECT * FROM tl_chronometry WHERE published = ? AND runningtimeUnix > 0 AND dnf != ?')
            ->execute(1, 1)
        ;
        $haveFinished = $objChronometry->numRows;

        $objChronometry = Database::getInstance()
            ->prepare('SELECT * FROM tl_chronometry WHERE published = ? AND runningtimeUnix = 0 AND dnf != ?')
            ->execute(1, 1)
        ;
        $running = $objChronometry->numRows;

        $runnerstotal = $total - $dispensed;

        $objStats = new \stdClass();
        $objStats->total = $total;
        $objStats->dispensed = $dispensed;
        $objStats->haveFinished = $haveFinished;
        $objStats->running = $running;
        $objStats->haveGivenUp = $dnf;
        $objStats->runnersTotal = $runnerstotal;

        return $objStats;
    }

    public static function getCategories(): array
    {
        Controller::loadLanguageFile('tl_chronometry');
        $aCat = [];
        $arrCats = Config::get('chronometry_categories');

        if (!empty($arrCats) && \is_array($arrCats)) {
            foreach ($arrCats as $cat) {
                $objCat = new \stdClass();
                $objCat->id = $cat;
                $objCat->label = '' !== $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$cat] ? $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$cat] : 'undefined';
                $aCat[] = $objCat;
            }
        }

        return $aCat;
    }

    /**
     * @param $varValue
     */
    public static function customRegexp(string $strRegexp, $varValue, Widget $objWidget): bool
    {
        if ('chronometryTime' === $strRegexp) {
            if (!static::isValidTimeFormat($varValue)) {
                $objWidget->addError('Field '.$objWidget->label.' should be a valid time like hh:mm:ss.');
            }

            return true;
        }

        return false;
    }

    public static function isValidTimeFormat(string $strFormattedTime = ''): bool
    {
        if ('' === $strFormattedTime) {
            return true;
        }

        if (preg_match('/^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/', $strFormattedTime)) {
            return true;
        }

        return false;
    }

    public static function makeTimestamp(string $time = ''): int
    {
        if ('' === trim($time)) {
            return 0;
        }

        $time = explode(':', $time);

        if (3 !== \count($time)) {
            return 0;
        }

        return (int) ($time[0]) * 60 * 60 + (int) ($time[1]) * 60 + (int) ($time[2]);
    }

    public static function getTimeDifference(string $strStartTime, string $strEndTime): string
    {
        if ('' === $strStartTime || '' === $strEndTime) {
            return '';
        }

        $arrStartTime = explode(':', $strStartTime);
        $arrEndTime = explode(':', $strEndTime);

        $startTimeTstamp = mktime((int) $arrStartTime[0], (int) $arrStartTime[1], (int) $arrStartTime[2], 0, 0, 0);
        $endTimeTstamp = mktime((int) $arrEndTime[0], (int) $arrEndTime[1], (int) $arrEndTime[2], 0, 0, 0);

        $timeDifference = $endTimeTstamp - $startTimeTstamp;

        return gmdate('H:i:s', $timeDifference);
    }

    public static function synchronizeTime(): void
    {
        $set = [
            'runningtimeUnix' => 0,
            'runningtime' => '',
        ];

        // Set valid timestamps
        Database::getInstance()->prepare('UPDATE tl_chronometry %s WHERE endtime = ? OR runningtime = ? OR runningtime = ?')->set($set)->execute('', '', 0);

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE endtime != ?')->execute('');

        while ($objChronometry->next()) {
            $objChronometryModel = ChronometryModel::findByPk($objChronometry->id);

            if (null !== $objChronometryModel) {
                $objChronometryModel->runningtime = static::getTimeDifference($objChronometry->starttime, $objChronometry->endtime);
                $objChronometryModel->save();
            }
        }

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE runningtime != ?')->execute('');

        while ($objChronometry->next()) {
            $objChronometryModel = ChronometryModel::findByPk($objChronometry->id);

            if (null !== $objChronometryModel) {
                $objChronometryModel->runningtimeUnix = static::makeTimestamp($objChronometry->runningtime);
                $objChronometryModel->save();
            }
        }
    }
}
