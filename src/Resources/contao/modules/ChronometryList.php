<?php

/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */

namespace Markocupic;

use Contao\Environment;
use Contao\Input;
use Markocupic\ChronometryBundle\FrontendAjax\FrontendAjax;
use Markocupic\SacEventToolBundle\Services\PhpOffice\PrintCertificate;
use Markocupic\SacEventToolBundle\Services\PhpOffice\PrintRankingList;
use Patchwork\Utf8;

/**
 * Class ChronometryList
 * @package Markocupic
 */
class ChronometryList extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_chronometry_list';

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['chronometryList'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Send Backup to Browser
        if (\Input::post('downloadDatabaseDump'))
        {
            // Send backup to browser (charset Windows-1252)
            ExportTable\ExportTable::exportTable('tl_chronometry', array('strDestinationCharset' => 'Windows-1252'));
            exit();
        }

        // Print certificate
        if (\Input::get('printCertificate') == 'true' && \Input::get('id') != '')
        {
            $objCertificate = new PrintCertificate();
            $strTemplate = 'vendor/markocupic/chronometry-bundle/src/Resources/contao/templates/docx/certificate.docx';
            $objCertificate->sendToBrowser(\Input::get('id'), $strTemplate);
            exit;
        }

        // Print certificate
        if (\Input::post('printRankingListCat') != '')
        {
            $objRanklist = new PrintRankingList();
            $strTemplate = 'vendor/markocupic/chronometry-bundle/src/Resources/contao/templates/docx/ranklist.docx';
            $objRanklist->sendToBrowser(\Input::post('printRankingListCat'), $strTemplate);
            exit;
        }

        // Handle ajax requests
        if (Environment::get('isAjaxRequest') && Input::post('action') !== '')
        {
            $controller = new FrontendAjax();
            $controller->{Input::post('action')}();
            exit;
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
    }

}
