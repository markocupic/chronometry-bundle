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
use Markocupic\ChronometryBundle\Csv\CsvWriter;
use Markocupic\ChronometryBundle\FrontendAjax\FrontendAjax;
use Markocupic\ChronometryBundle\PhpOffice\PrintCertificate;
use Markocupic\ChronometryBundle\PhpOffice\PrintRankingList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(ChronometryListModuleController::TYPE, category="chronometry")
 */
class ChronometryListModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'chronometry_list';
    public const ACTION_SAVE_ROW = 'saveRow';
    public const ACTION_CHECK_ONLINE_STATE = 'checkOnlineStatus';
    public const ACTION_GET_DATA_ALL = 'getDataAll';
    public const ACTION_CSV_EXPORT = 'csvExport';
    public const ACTION_PRINT_RANKING_LIST = 'printRankingList';
    public const ACTION_PRINT_CERTIFICATE = 'printCertificate';

    private CsvWriter $csvWriter;
    private FrontendAjax $frontendAjax;
    private PrintRankingList $printRankingList;
    private PrintCertificate $printCertificate;

    public function __construct(CsvWriter $csvWriter, FrontendAjax $frontendAjax, PrintRankingList $printRankingList, PrintCertificate $printCertificate)
    {
        $this->csvWriter = $csvWriter;
        $this->frontendAjax = $frontendAjax;
        $this->printRankingList = $printRankingList;
        $this->printCertificate = $printCertificate;
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        // Handle requests
        if ($request->query->has('action')) {
            $strAction = $request->query->get('action');

            // Save data to the db
            if (self::ACTION_SAVE_ROW === $strAction) {
                $intId = (int) $request->request->get('id');
                $endtime = $request->request->get('endtime');
                $dnf = (bool) $request->request->get('dnf');

                return $this->frontendAjax->saveRow($intId, $endtime, $dnf);
            }

            // Check is online
            if (self::ACTION_CHECK_ONLINE_STATE === $strAction) {
                return $this->frontendAjax->checkOnlineStatus();
            }

            // Retrieve data for the vue.js instance
            if (self::ACTION_GET_DATA_ALL === $strAction) {
                return $this->frontendAjax->getDataAll();
            }

            // Print ranking list
            if (self::ACTION_PRINT_RANKING_LIST === $strAction) {
                if ($request->query->has('printRankingListCat')) {
                    $intCat = (int) $request->query->get('printRankingListCat');
                    $strTemplate = 'vendor/markocupic/chronometry-bundle/src/Resources/contao/templates/docx/ranklist.docx';
                    $this->printRankingList->sendToBrowser($intCat, $strTemplate);
                }
            }

            // Print certificate
            if (self::ACTION_PRINT_CERTIFICATE === $strAction) {
                if ($request->query->has('id')) {
                    $strTemplate = 'vendor/markocupic/chronometry-bundle/src/Resources/contao/templates/docx/certificate.docx';
                    $intId = (int) $request->query->get('id');
                    $this->printCertificate->sendToBrowser($intId, $strTemplate);
                }
            }

            // Download csv spreadsheet
            if (self::ACTION_CSV_EXPORT === $strAction) {
                $this->csvWriter->download();
            }

            throw new \Exception('Couldn\'t find a matching function for the action "'.$strAction.'"');
        }

        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        return $template->getResponse();
    }
}
