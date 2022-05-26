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

namespace Markocupic\ChronometryBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Template;
use Markocupic\ChronometryBundle\FrontendAjax\FrontendAjax;
use Markocupic\ChronometryBundle\PhpOffice\PrintCertificate;
use Markocupic\ChronometryBundle\PhpOffice\PrintRankingList;
use Markocupic\ExportTable\Config\Config;
use Markocupic\ExportTable\Export\ExportTable;
use Markocupic\ExportTable\Writer\ByteSequence;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(ChronometryListModuleController::TYPE, category="chronometry")
 */
class ChronometryListModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'chronometry_list';
    private ExportTable $exportTable;
    private FrontendAjax $frontendAjax;

    public function __construct(ExportTable $exportTable, FrontendAjax $frontendAjax)
    {
        $this->exportTable = $exportTable;
        $this->frontendAjax = $frontendAjax;
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        // Send Backup to Browser
        if ($request->request->has('downloadDatabaseDump')) {
            $config = new Config('tl_chronometry');
            $config->setOutputBom(ByteSequence::BOM['UTF-8']);

            $this->exportTable->run($config);
        }

        // Print certificate
        if ('true' === $request->query->get('printCertificate') && $request->query->has('id')) {
            $objCertificate = new PrintCertificate();
            $strTemplate = 'vendor/markocupic/chronometry-bundle/src/Resources/contao/templates/docx/certificate.docx';
            $objCertificate->sendToBrowser((int) $request->query->get('id'), $strTemplate);
        }

        // Print certificate
        if ($request->request->has('printRankingListCat')) {
            $objRanklist = new PrintRankingList();
            $strTemplate = 'vendor/markocupic/chronometry-bundle/src/Resources/contao/templates/docx/ranklist.docx';
            $objRanklist->sendToBrowser((int) $request->request->get('printRankingListCat'), $strTemplate);
        }

        // Handle ajax requests
        if ($request->request->has('action')) {
            if ('saveRow' === $request->request->get('action')) {
                $id = (int) $request->request->get('id');
                $endtime = $request->request->get('endtime');
                $dnf = (bool) $request->request->get('dnf');
                $this->frontendAjax->saveRow($id, $endtime, $dnf);
            } else {
                $this->frontendAjax->{$request->request->get('action')}();
            }
        }

        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        return $template->getResponse();
    }
}
