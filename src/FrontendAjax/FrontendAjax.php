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

use Contao\Database;
use Contao\File;
use Contao\Folder;
use Doctrine\DBAL\Connection;
use League\Csv\ByteSequence;
use League\Csv\Writer;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;
use Markocupic\ChronometryBundle\Model\ChronometryModel;
use Markocupic\ExportTable\Export\ExportTable;
use Symfony\Component\HttpFoundation\JsonResponse;

class FrontendAjax
{
    private Connection $connection;
    private ExportTable $exportTable;
    private string $projectDir;

    public function __construct(Connection $connection, ExportTable $exportTable, string $projectDir)
    {
        $this->connection = $connection;
        $this->exportTable = $exportTable;
        $this->projectDir = $projectDir;
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
        $arrItems = [];
        $arrJson = [];

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime ASC, stufe ASC, teachername, gender')->execute(1, 2);

        while ($row = $objChronometry->fetchAssoc()) {
            $arrItems[] = ChronometryHelper::getRowAsObject($row);
        }

        $arrJson['status'] = 'success';
        $arrJson['stats'] = ChronometryHelper::getStats();
        $arrJson['data'] = $arrItems;
        $arrJson['categories'] = ChronometryHelper::getCategories();

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
            $objChronometry = ChronometryModel::findByPk($id);

            if (null !== $objChronometry) {
                $objChronometry->endtime = $endtime;

                // Athlete did not finish the challenge
                $objChronometry->dnf = $dnf ? '1' : '';

                if ($dnf) {
                    $objChronometry->endtime = '';
                }

                $objChronometry->runningtime = ChronometryHelper::getTimeDifference($objChronometry->starttime, $objChronometry->endtime);
                $objChronometry->runningtimeUnix = ChronometryHelper::makeTimestamp($objChronometry->runningtime);
                $objChronometry->tstamp = time();
                $objChronometry->save();

                $arrItems = [];
                $arrJson = [];
                $arrRows = [];
                $i = 0;

                // Get data
                $result = $this->connection->executeQuery('SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime ASC, stufe ASC, teachername, gender', ['1']);

                while (false !== ($row = $result->fetchAssociative())) {
                    $arrItems[] = ChronometryHelper::getRowAsObject($row);

                    if (0 === $i) {
                        // Add the headline first
                        $arrRows[] = array_keys($row);
                    }
                    $arrRows[] = $row;
                    ++$i;
                }

                $arrJson['status'] = 'success';
                $arrJson['stats'] = ChronometryHelper::getStats();
                $arrJson['data'] = $arrItems;
                $arrJson['categories'] = ChronometryHelper::getCategories();

                // Do backup
                if (!empty($arrRows)) {
                    new Folder('files/chronometry-backup');
                    $strDatim = date('Ymd_H_i_s', time());
                    $objFile = new File('files/chronometry-backup/'.$strDatim.'_chronometry.csv');

                    $writer = Writer::createFromPath($this->projectDir.'/'.$objFile->path, 'w+');
                    $writer->setDelimiter(';');
                    $writer->setEnclosure('"');
                    $writer->setOutputBOM(ByteSequence::BOM_UTF8);
                    $writer->insertAll($arrRows);
                }
            }
        }

        $response = new JsonResponse($arrJson);

        return $response->send();
    }
}
