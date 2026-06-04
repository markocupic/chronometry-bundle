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

namespace Markocupic\ChronometryBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Markocupic\ChronometryBundle\Export\CsvWriter;
use Markocupic\ChronometryBundle\FrontendAjax\FrontendAjax;
use Markocupic\ChronometryBundle\Model\ChronometryModel;
use Markocupic\ChronometryBundle\PhpOffice\Certificate;
use Markocupic\ChronometryBundle\PhpOffice\RankingList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(ChronometryListController::TYPE, category: 'chronometry')]
class ChronometryListController extends AbstractFrontendModuleController
{
    public const string TYPE = 'chronometry_list';

    public const string ACTION_SAVE_ROW = 'updateRecord';

    public const string ACTION_CHECK_ONLINE_STATE = 'checkIsOnline';

    public const string ACTION_GET_DATA_ALL = 'fetchAppData';

    public const string ACTION_CSV_EXPORT = 'csvExport';

    public const string ACTION_PRINT_RANKING_LIST = 'printRankingList';

    public const string ACTION_PRINT_CERTIFICATE = 'printCertificate';

    public function __construct(
        private readonly Certificate $certificate,
        private readonly Connection $connection,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly CsvWriter $csvWriter,
        private readonly FrontendAjax $frontendAjax,
        private readonly RankingList $rankingList,
    ) {
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array|null $classes = null, PageModel|null $page = null): Response
    {
        // Handle requests
        if ($request->query->has('action')) {
            $strAction = $request->query->get('action');

            // Save data to the db
            if (self::ACTION_SAVE_ROW === $strAction) {
                $intId = (int) $request->request->get('id');
                $endtime = $request->request->get('endtime');
                $status = $request->request->get('status');

                $this->frontendAjax->persistRow($intId, $endtime, $status);
            }

            // Check is online
            if (self::ACTION_CHECK_ONLINE_STATE === $strAction) {
                $this->frontendAjax->checkIsOnline();
            }

            // Retrieve data for the vue.js instance
            if (self::ACTION_GET_DATA_ALL === $strAction) {
                $this->frontendAjax->fetchAppData();
            }

            // Print ranking list
            if (self::ACTION_PRINT_RANKING_LIST === $strAction) {
                if ($request->request->has('printRankingListCat')) {
                    $intCat = (int) $request->request->get('printRankingListCat');

                    if (isset($_POST['eternalListOfTheBestDownload'])) {
                        $file = $this->rankingList->generate($intCat, true);

                        throw new ResponseException($this->file($file->getRealPath()));
                    }

                    $file = $this->rankingList->generate($intCat, false);

                    throw new ResponseException($this->file($file->getRealPath()));
                }
            }

            // Print certificate
            if (self::ACTION_PRINT_CERTIFICATE === $strAction) {
                if ($request->query->has('id')) {
                    $chronometryModel = ChronometryModel::findById($request->query->get('id'));
                    $file = $this->certificate->generate($chronometryModel);

                    throw new ResponseException($this->file($file->getRealPath()));
                }
            }

            // Download csv spreadsheet
            if (self::ACTION_CSV_EXPORT === $strAction) {
                $file = $this->csvWriter->generate();

                throw new ResponseException($this->file($file->getRealPath()));
            }

            throw new \Exception('Couldn\'t find a matching function for the action "'.$strAction.'"');
        }

        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $template->set('request_token', $this->csrfTokenManager->getDefaultTokenValue());

        return $template->getResponse();
    }
}
