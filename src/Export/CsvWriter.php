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

namespace Markocupic\ChronometryBundle\Export;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use League\Csv\Bom;
use League\Csv\Writer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\File;

class CsvWriter
{
    private Adapter $config;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly string $projectDir,
    ) {
        // Adapters
        $this->config = $this->framework->getAdapter(Config::class);
    }

    public function generate(bool $addHeadline = true): File
    {
        $strDatim = date('Ymd_H_i_s_', time());
        $targetPath = Path::join($this->projectDir, $this->config->get('chronometry_bundle_backup_path'));
        $targetPath = \sprintf($targetPath, $strDatim);

        return $this->saveToFile($targetPath, $addHeadline);
    }

    public function saveToFile(string $targetPath, bool $addHeadline = true): File
    {
        // Get the data array first
        $records = $this->getData($addHeadline);

        $fs = new Filesystem();

        // Create the parent directory
        if (!$fs->exists(\dirname($targetPath))) {
            $fs->mkdir(\dirname($targetPath));
        }

        // Remove old file
        $fs->remove($targetPath);

        $writer = Writer::fromString();
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setOutputBOM(Bom::Utf8);
        $writer->insertAll($records);

        $fs->dumpFile($targetPath, $writer->toString());

        return new File($targetPath);
    }

    private function getData(bool $addHeadline = true): array
    {
        $arrRows = [];
        $i = 0;

        // Get data
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM tl_chronometry WHERE published = ? ORDER BY starttime, stufe, teachername, gender', [1]);

        foreach ($rows as $row) {
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
