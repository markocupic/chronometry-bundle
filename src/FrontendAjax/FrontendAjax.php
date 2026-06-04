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

namespace Markocupic\ChronometryBundle\FrontendAjax;

use Contao\Config;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Markocupic\ChronometryBundle\Data\Status;
use Markocupic\ChronometryBundle\Export\CsvWriter;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\JsonResponse;

class FrontendAjax
{
    private Adapter $config;

    public function __construct(
        private readonly Connection $connection,
        private readonly ChronometryHelper $chronometryHelper,
        private readonly ContaoFramework $framework,
        private readonly CsvWriter $csvWriter,
        private readonly string $projectDir,
    ) {
        $this->config = $this->framework->getAdapter(Config::class);
    }

    public function checkIsOnline(): void
    {
        $arrJson = [];
        $arrJson['status'] = 'success';
        $response = new JsonResponse($arrJson);

        throw new ResponseException($response);
    }

    public function fetchAppData(): void
    {
        $arrRows = [];
        $arrJson = [];

        $rows = $this->connection
            ->fetchAllAssociative(
                'SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime, stufe, teachername, gender',
                [1],
            )
        ;

        foreach ($rows as $row) {
            $arrRows[] = $this->chronometryHelper->getRowAsObject($row);
        }

        $arrJson['status'] = 'success';
        $arrJson['stats'] = $this->chronometryHelper->getStats();
        $arrJson['runners'] = $arrRows;
        $arrJson['categories'] = $this->chronometryHelper->getCategories();

        $response = new JsonResponse($arrJson);

        throw new ResponseException($response);
    }

    /**
     * @throws \Exception
     */
    public function persistRow(int $id, string $endtime, string $status): void
    {
        $arrJson = [];
        $arrJson['status'] = 'error';

        $set = $this->connection->fetchAssociative('SELECT * FROM tl_chronometry WHERE id = ?', [$id]);

        // Save endtime
        if (false === $set) {
            $response = new JsonResponse($arrJson);

            throw new ResponseException($response);
        }

        if ($status === Status::finisher->value || $status === Status::unranked->value) {
            $set['status'] = $status;
            $set['endtime'] = $endtime;
            $set['runningtime'] = $this->chronometryHelper->getTimeSpan($set['starttime'], $endtime);
            $set['runningtimeUnix'] = $this->chronometryHelper->makeTimestamp($set['runningtime']);
        } else {
            $set['status'] = $status;
            $set['endtime'] = '';
            $set['runningtime'] = '';
            $set['runningtimeUnix'] = 0;
        }

        if ($this->connection->update('tl_chronometry', $set, ['id' => $id])) {
            $set['tstamp'] = time();
            $this->connection->update('tl_chronometry', $set, ['id' => $id]);
        }

        $items = [];
        $json = [];

        // Get data
        $rows = $this->connection
            ->fetchAllAssociative(
                'SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime, stufe, teachername, gender',
                [1],
            )
        ;

        foreach ($rows as $row) {
            $items[] = $this->chronometryHelper->getRowAsObject($row);
        }

        $json['status'] = 'success';
        $json['stats'] = $this->chronometryHelper->getStats();
        $json['runners'] = $items;
        $json['categories'] = $this->chronometryHelper->getCategories();

        // Do backup
        $strDatim = date('Ymd_H_i_s_', time());
        $backupPath = Path::join($this->projectDir, $this->config->get('chronometry_bundle_backup_path'));
        $path = \sprintf($backupPath, $strDatim);

        $this->csvWriter->saveToFile($path);

        $response = new JsonResponse($json);

        throw new ResponseException($response);
    }
}
