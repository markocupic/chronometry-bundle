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

namespace Markocupic\ChronometryBundle\FrontendAjax;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Markocupic\ChronometryBundle\Csv\CsvWriter;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;
use Markocupic\ChronometryBundle\Model\ChronometryModel;
use Symfony\Component\HttpFoundation\JsonResponse;

class FrontendAjax
{
    private ContaoFramework $framework;
    private Connection $connection;
    private CsvWriter $csvWriter;

    // Adapters
    private Adapter $config;
    private Adapter $chronometryHelper;

    public function __construct(ContaoFramework $framework, Connection $connection, CsvWriter $csvWriter)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->csvWriter = $csvWriter;

        // Adapters
        $this->config = $this->framework->getAdapter(Config::class);
        $this->chronometryHelper = $this->framework->getAdapter(ChronometryHelper::class);
        $this->chronometryModel = $this->framework->getAdapter(ChronometryModel::class);
    }

    public function checkOnlineStatus(): JsonResponse
    {
        $arrJson = [];
        $arrJson['status'] = 'success';
        $response = new JsonResponse($arrJson);

        return $response->send();
    }

    public function getDataAll(): JsonResponse
    {
        $arrRows = [];
        $arrJson = [];

        $result = $this->connection
            ->executeQuery(
                'SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime, stufe, teachername, gender',
                ['1'],
            )
        ;

        while (false !== ($row = $result->fetchAssociative())) {
            $arrRows[] = $this->chronometryHelper->getRowAsObject($row);
        }

        $arrJson['status'] = 'success';
        $arrJson['stats'] = $this->chronometryHelper->getStats();
        $arrJson['runners'] = $arrRows;
        $arrJson['categories'] = $this->chronometryHelper->getCategories();

        $response = new JsonResponse($arrJson);

        return $response->send();
    }

    /**
     * @throws \Exception
     */
    public function saveRow(int $id, string $endtime, bool $dnf): JsonResponse
    {
        $arrJson = [];
        $arrJson['status'] = 'error';

        // Save endtime
        if ($id > 0) {
            $objChronometry = $this->chronometryModel->findByPk($id);

            if (null !== $objChronometry) {
                $objChronometry->endtime = $endtime;

                // Athlete did not finish the challenge
                $objChronometry->dnf = $dnf ? '1' : '';

                if ('1' === $dnf) {
                    $objChronometry->endtime = '';
                }

                $objChronometry->runningtime = $this->chronometryHelper->getTimeDifference($objChronometry->starttime, $objChronometry->endtime);
                $objChronometry->runningtimeUnix = $this->chronometryHelper->makeTimestamp($objChronometry->runningtime);
                $objChronometry->tstamp = time();
                $objChronometry->save();

                $arrItems = [];
                $arrJson = [];

                // Get data
                $result = $this->connection
                    ->executeQuery(
                        'SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime, stufe, teachername, gender',
                        ['1'],
                    )
                ;

                while (false !== ($row = $result->fetchAssociative())) {
                    $arrItems[] = $this->chronometryHelper->getRowAsObject($row);
                }

                $arrJson['status'] = 'success';
                $arrJson['stats'] = $this->chronometryHelper->getStats();
                $arrJson['runners'] = $arrItems;
                $arrJson['categories'] = $this->chronometryHelper->getCategories();

                // Do backup
                $strDatim = date('Ymd_H_i_s_', time());
                $backupPath = $this->config->get('chronometry_bundle_backup_path');
                $path = sprintf($backupPath, $strDatim);

                $this->csvWriter->saveToFile($path);
            }
        }

        $response = new JsonResponse($arrJson);

        return $response->send();
    }
}
