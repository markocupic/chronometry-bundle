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

namespace Markocupic\ChronometryBundle\Csv;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\Folder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use League\Csv\ByteSequence;
use League\Csv\InvalidArgument;
use League\Csv\Writer;

class CsvWriter
{
    private ContaoFramework $framework;
    private Connection $connection;
    private string $projectDir;

    // Adapters
    private Adapter $config;
    private Adapter $controller;

    public function __construct(ContaoFramework $framework, Connection $connection, string $projectDir)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->projectDir = $projectDir;

        // Adapters
        $this->config = $this->framework->getAdapter(Config::class);
        $this->controller = $this->framework->getAdapter(Controller::class);
    }

    /**
     * @throws InvalidArgument
     */
    public function download(bool $addHeadline = true): void
    {
        $strDatim = date('Ymd_H_i_s_', time());
        $backupPath = $this->config->get('chronometry_bundle_backup_path');
        $path = sprintf($backupPath, $strDatim);

        $this->saveToFile($path, $addHeadline);
        $this->controller->sendFileToBrowser($path, true);
    }

    /**
     * @throws InvalidArgument
     */
    public function saveToFile(string $strPath, bool $addHeadline = true): void
    {
        // Get the data array first
        $arrRows = $this->getData($addHeadline);

        if (!empty($arrRows)) {
            // Create folder
            new Folder(\dirname($strPath));

            // Create empty files/truncate file if exists
            $objFile = new File($strPath);
            $objFile->truncate();
            $objFile->close();

            $writer = Writer::createFromPath($this->projectDir.'/'.$objFile->path, 'w+');
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setOutputBOM(ByteSequence::BOM_UTF8);
            $writer->insertAll($arrRows);
        }
    }

    /**
     * @throws Exception
     */
    private function getData(bool $addHeadline = true): array
    {
        $arrRows = [];
        $i = 0;

        // Get data
        $result = $this->connection->executeQuery('SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime, stufe, teachername, gender', ['1']);

        while (false !== ($row = $result->fetchAssociative())) {
            if (0 === $i && $addHeadline) {
                // Add the headline first
                $arrRows[] = array_keys($row);
            }
            $arrRows[] = $row;
            ++$i;
        }

        return $arrRows;
    }
}
