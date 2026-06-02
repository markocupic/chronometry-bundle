<?php

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

namespace Markocupic\ChronometryBundle\Helper;

use Contao\Config;
use Contao\Controller;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Markocupic\ChronometryBundle\Model\ChronometryModel;

readonly class ChronometryHelper
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws Exception
     */
    public function getRowAsObject(array $row): \stdClass
    {
        $objRow = new \stdClass();

        foreach ($row as $k => $v) {
            $objRow->{$k} = $v;
        }

        $objRow->fullname = $row['firstname'].' '.$row['lastname'];
        $objRow->runningtimeUnix = $this->makeTimestamp($row['runningtime']);
        $objRow->starttimeUnix = $this->makeTimestamp($row['starttime']);
        $objRow->endtimeUnix = $this->makeTimestamp($row['endtime']);
        $objRow->rank = $this->getRank((int) $row['id']);

        return $objRow;
    }

    /**
     * @throws Exception
     */
    public function getRank(int $id): int|string
    {
        $objAthlete = ChronometryModel::findById($id);

        if (null === $objAthlete) {
            return 0;
        }

        if ($objAthlete->unranked) {
            return 'unranked';
        }

        if ($objAthlete->runningtimeUnix > 1) {
            $tstamps = $this->connection->fetchFirstColumn(
                'SELECT runningtimeUnix FROM tl_chronometry WHERE runningtimeUnix > 0 AND published = 1 AND category = ? ORDER BY runningtimeUnix',
                [$objAthlete->category],
            );

            return $this->calculateRank($objAthlete->runningtimeUnix, $tstamps);
        }

        return 'd.n.f';
    }

    /**
     * @throws Exception
     */
    public function getStats(): \stdClass
    {
        $dispensed = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_chronometry WHERE published = ?',
            [0],
        );

        $total = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_chronometry',
        );

        $dnf = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_chronometry WHERE published = ? AND dnf = ?',
            [1, 1],
        );

        $haveFinished = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_chronometry WHERE published = ? AND runningtimeUnix > 0 AND dnf != ?',
            [1, 1],
        );

        $unranked = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_chronometry WHERE published = ? AND unranked = 1 AND runningtimeUnix > 0 AND dnf != ?',
            [1, 1],
        );

        $running = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_chronometry WHERE published = ? AND runningtimeUnix = 0 AND dnf != ?',
            [1, 1],
        );

        $runnersTotal = $total - $dispensed;

        $objStats = new \stdClass();
        $objStats->total = $total;
        $objStats->dispensed = $dispensed;
        $objStats->haveFinished = $haveFinished;
        $objStats->running = $running;
        $objStats->haveGivenUp = $dnf;
        $objStats->runnersTotal = $runnersTotal;
        $objStats->unranked = $unranked;

        return $objStats;
    }

    public function getCategories(): array
    {
        Controller::loadLanguageFile(ChronometryModel::getTable());
        $aCat = [];
        $arrCats = Config::get('chronometry_bundle_categories');

        if (!empty($arrCats) && \is_array($arrCats)) {
            foreach ($arrCats as $cat) {
                $objCat = new \stdClass();
                $objCat->id = $cat;

                $table = ChronometryModel::getTable();
                $categoryLabel = $GLOBALS['TL_LANG'][$table]['categories'][$cat] ?? '';
                $objCat->label = '' !== $categoryLabel ? $categoryLabel : 'undefined';
                $aCat[] = $objCat;
            }
        }

        return $aCat;
    }

    public function makeTimestamp(string $time = ''): int
    {
        if ('' === trim($time)) {
            return 0;
        }

        $time = explode(':', $time);

        if (3 !== \count($time)) {
            return 0;
        }

        return (int) $time[0] * 60 * 60 + (int) $time[1] * 60 + (int) $time[2];
    }

    public function getTimeSpan(string $strStartTime, string $strEndTime): string
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

    /**
     * @throws Exception
     */
    public function synchronizeTime(): void
    {
        $this->connection->beginTransaction();

        try {
            // Set valid timestamps
            $this->connection->executeStatement(
                'UPDATE tl_chronometry SET runningtimeUnix = 0, runningtime = "" WHERE endtime = ? OR runningtime = ? OR runningtime = ?',
                ['', '', 0],
            );
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }

        $this->connection->beginTransaction();

        try {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT * FROM tl_chronometry WHERE endtime != ?',
                [''],
            );

            foreach ($rows as $row) {
                $set = ['runningtime' => $this->getTimeSpan($row['starttime'], $row['endtime'])];
                $this->connection->update('tl_chronometry', $set, ['id' => $row['id']]);
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }

        $this->connection->beginTransaction();

        try {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT * FROM tl_chronometry WHERE runningtime != ?',
                [''],
            );

            foreach ($rows as $row) {
                $set = ['runningtimeUnix' => $this->makeTimestamp($row['runningtime'])];
                $this->connection->update('tl_chronometry', $set, ['id' => $row['id']]);
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    private function calculateRank(int $time, array $times): int
    {
        $faster = 0;

        foreach ($times as $t) {
            if ($t < $time) {
                ++$faster;
            }
        }

        return $faster + 1;
    }
}
