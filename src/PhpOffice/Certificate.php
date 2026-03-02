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

namespace Markocupic\ChronometryBundle\PhpOffice;

use Contao\Config;
use Contao\Controller;
use Markocupic\ChronometryBundle\Helper\ChronometryHelper;
use Markocupic\ChronometryBundle\Model\ChronometryModel;
use Markocupic\PhpOffice\PhpWord\MsWordTemplateProcessor;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\File\File;

readonly class Certificate
{
    public function __construct(
        private ChronometryHelper $chronometryHelper,
        private string $projectDir,
    ) {
    }

    public function generate(ChronometryModel $chronometryModel): File
    {
        // Load DCA
        Controller::loadDataContainer(ChronometryModel::getTable());

        // Load language file
        Controller::loadLanguageFile(ChronometryModel::getTable());

        // Get category
        $category = $GLOBALS['TL_LANG'][ChronometryModel::getTable()]['categories'][$chronometryModel->category] ?? $chronometryModel->category;

        // Get rank
        $rank = $this->chronometryHelper->getRank($chronometryModel->id);

        // Get time
        date_default_timezone_set('UTC');
        $time = date('H:i:s', (int) $chronometryModel->runningtimeUnix);
        date_default_timezone_set(Config::get('timeZone'));

        $strTemplateSrc = Path::join($this->projectDir, 'vendor/markocupic/chronometry-bundle/docx/certificate.docx');

        // Set the target filename
        $strTargetSrc = \sprintf(
            '%s/system/tmp/certificate_cat%s_rank%s_%s_%s.docx',
            $this->projectDir,
            $chronometryModel->category,
            $rank,
            $chronometryModel->firstname,
            $chronometryModel->lastname,
        );

        // Instantiate template processor
        $objPhpWord = new MsWordTemplateProcessor($strTemplateSrc, $strTargetSrc);

        $objPhpWord->replace('firstname', $chronometryModel->firstname, ['multiline' => false]);
        $objPhpWord->replace('lastname', $chronometryModel->lastname, ['multiline' => false]);
        $objPhpWord->replace('category', $category, ['multiline' => false]);
        $objPhpWord->replace('rank', $rank, ['multiline' => false]);
        $objPhpWord->replace('time', $time, ['multiline' => false]);

        // Generate the file
        return new File($objPhpWord->generate()->getRealPath());
    }
}
