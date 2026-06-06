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

namespace Markocupic\ChronometryBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use Markocupic\ChronometryBundle\Export\CsvWriter;
use Markocupic\ChronometryBundle\FrontendAjax\FrontendAjax;
use Markocupic\ChronometryBundle\Model\ChronometryModel;
use Markocupic\ChronometryBundle\PhpOffice\Certificate;
use Markocupic\ChronometryBundle\PhpOffice\RankingList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(ChronometryListController::TYPE, category: 'chronometry')]
class ChronometryListController extends AbstractContentElementController
{
    public const string TYPE = 'chronometry_list';

    public const array ACTIONS = [
        'UPDATE_RECORD' => 'updateRecord',
        'CHECK_ONLINE_STATE' => 'checkOnlineState',
        'FETCH_APP_DATA' => 'fetchAppData',
        'CSV_EXPORT' => 'csvExport',
        'PRINT_RANKING_LIST' => 'printRankingList',
        'PRINT_CERTIFICATE' => 'printCertificate',
    ];

    public function __construct(
        private readonly Certificate $certificate,
        private readonly CsvWriter $csvWriter,
        private readonly FrontendAjax $frontendAjax,
        private readonly RankingList $rankingList,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    public function __invoke(Request $request, ContentModel $model, string $section, array|null $classes = null, PageModel|null $page = null): Response
    {
        // Handle requests
        if ($this->scopeMatcher->isFrontendRequest($request) && $request->query->has('action')) {
            $action = $request->query->get('action');

            // Save data to the db
            if (self::ACTIONS['UPDATE_RECORD'] === $action) {
                $id = (int) $request->request->get('id');
                $endtime = $request->request->get('endtime');
                $status = $request->request->get('status');

                $response = $this->frontendAjax->persistRow($id, $endtime, $status);

                throw new ResponseException($response);
            }

            // Check is online
            if (self::ACTIONS['CHECK_ONLINE_STATE'] === $action) {
                $response = $this->frontendAjax->checkOnlineState();

                throw new ResponseException($response);
            }

            // Retrieve data for the vue.js instance
            if (self::ACTIONS['FETCH_APP_DATA'] === $action) {
                $response = $this->frontendAjax->fetchAppData();

                throw new ResponseException($response);
            }

            // Print ranking list
            if (self::ACTIONS['PRINT_RANKING_LIST'] === $action) {
                if ($request->request->has('printRankingListCat')) {
                    $catId = (int) $request->request->get('printRankingListCat');

                    if (isset($_POST['eternalListOfTheBestDownload'])) {
                        $file = $this->rankingList->generate($catId, true);

                        throw new ResponseException($this->file($file->getRealPath()));
                    }

                    $file = $this->rankingList->generate($catId, false);

                    throw new ResponseException($this->file($file->getRealPath()));
                }
            }

            // Print certificate
            if (self::ACTIONS['PRINT_CERTIFICATE'] === $action) {
                if ($request->query->has('id')) {
                    $chronometryModel = ChronometryModel::findById($request->query->get('id'));
                    $file = $this->certificate->generate($chronometryModel);

                    throw new ResponseException($this->file($file->getRealPath()));
                }
            }

            // Download csv spreadsheet
            if (self::ACTIONS['CSV_EXPORT'] === $action) {
                $file = $this->csvWriter->generate();

                throw new ResponseException($this->file($file->getRealPath()));
            }

            throw new \Exception(\sprintf('Could not find a matching function for the action "%s".', $action));
        }

        // Call the parent method
        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        return $template->getResponse();
    }
}
