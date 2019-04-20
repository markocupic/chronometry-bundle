<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Markocupic;

use Contao\Config;
use Contao\Controller;

/**
 * Front end module "car list".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
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
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['chronometryList'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Send Backup to Browser
        if (\Input::get('downloadCsv'))
        {
            // Send backup to browser (charset Windows-1252)
            ExportTable\ExportTable::exportTable('tl_chronometry', array('strDestinationCharset' => 'Windows-1252'));
            exit();
        }

        // Send Stats to Browser
        if (\Input::get('getStats'))
        {
            $arrStats = $this->getStats();
            echo json_encode($arrStats);
            exit();
        }

        // Do Backup
        if (\Input::get('ajaxRequest') && \Input::get('doBackup'))
        {
            // Do Backup (charset utf8)
            ExportTable\ExportTable::exportTable('tl_chronometry', array('strDestination' => 'files/chronometry'));
            exit();
        }

        // Do Backup
        if (\Input::get('ajaxRequest') && \Input::get('getAll'))
        {
            $arrItems = array();
            $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE published=? ORDER BY starttime ASC, stufe ASC, teachername, gender')->execute(1, 2);
            while ($row = $objChronometry->fetchAssoc())
            {
                $arrItems[] = $this->getRowAsObject($row);
            }

            $json['status'] = 'success';
            $json['stats'] = $this->getStats();
            $json['data'] = $arrItems;
            $json['categories'] = $this->getCategories();

            echo json_encode($json);
            exit();
        }

        // Save Endtime
        if (\Input::get('ajaxRequest') && \Input::get('id') && \Input::get('saveRow'))
        {
            $objChronometry = \ChronometryModel::findByPk(\Input::get('id'));
            if ($objChronometry !== null)
            {
                $objChronometry->endtime = \Input::get('endtime');

                // Athlet has abandoned the race
                $objChronometry->aufgegeben = \Input::get('aufgegeben');
                if (\Input::get('aufgegeben') == 1)
                {
                    $objChronometry->endtime = '';
                }
                $objChronometry->runningtime = Chronometry::getTimeDifference($objChronometry->starttime, $objChronometry->endtime);
                $objChronometry->runningtimeUnix = Chronometry::makeTimestamp($objChronometry->runningtime);
                $objChronometry->tstamp = time();
                $objChronometry->save();

                $arrItems = array();
                $json = array();
                $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE published=? ORDER BY starttime ASC, stufe ASC, teachername, gender')->execute(1, 2);
                while ($row = $objChronometry->fetchAssoc())
                {
                    $arrItems[] = $this->getRowAsObject($row);
                }

                $json['status'] = 'success';
                $json['stats'] = $this->getStats();
                $json['data'] = $arrItems;
                $json['categories'] = $this->getCategories();

                echo json_encode($json);
                // Do Backup (charset utf8)

                ExportTable\ExportTable::exportTable('tl_chronometry', array('strDestination' => 'files/chronometry'));
                exit;
            }
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        /** @var \PageModel $objPage */
        global $objPage;
        ExportTable\ExportTable::exportTable('tl_chronometry', array('strDestination' => 'files/chronometry'));

        $arrItems = array();
        $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE published=? ORDER BY starttime ASC, stufe ASC, teachername, gender')->execute(1, 2);
        while ($dataRecord = $objChronometry->fetchAssoc())
        {
            $dataRecord['fullname'] = $dataRecord['firstname'] . ' ' . $dataRecord['lastname'];
            $dataRecord['rank'] = $this->getRank($dataRecord['id']);
            $dataRecord['runningtimeUnix'] = Chronometry::makeTimestamp($dataRecord['runningtimeUnix']);
            $dataRecord['starttimeUnix'] = Chronometry::makeTimestamp($dataRecord['starttimeUnix']);
            $dataRecord['endtimeUnix'] = Chronometry::makeTimestamp($dataRecord['endtimeUnix']);

            $dataRecord['rank'] = $this->getRank($dataRecord['id']);

            $arrItems[] = $dataRecord;
        }

        $this->Template->rows = $arrItems;
    }

    /**
     * @param $row
     * @return \stdClass
     */
    protected function getRowAsObject($row)
    {
        $objRow = new \stdClass();
        foreach ($row as $k => $v)
        {
            $objRow->{$k} = $v;
        }
        $objRow->fullname = $row['firstname'] . ' ' . $row['lastname'];
        $objRow->rank = $this->getRank($row['id']);
        $objRow->runningtimeUnix = Chronometry::makeTimestamp($row['runningtime']);
        $objRow->starttimeUnix = Chronometry::makeTimestamp($row['starttime']);
        $objRow->endtimeUnix = Chronometry::makeTimestamp($row['endtime']);
        $objRow->rank = $this->getRank($row['id']);
        $objRow->requesting = false;

        return $objRow;
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE published=?')->execute(0);
        $dispensed = $objChronometry->numRows;

        $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry')->execute();
        $total = $objChronometry->numRows;

        $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE published=? AND aufgegeben=?')->execute(1, 1);
        $abandoned = $objChronometry->numRows;

        $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE published=? AND runningtimeUnix > 0 AND aufgegeben!=?')->execute(1, 1);
        $arrived = $objChronometry->numRows;

        $objChronometry = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE published=? AND runningtimeUnix = 0 AND aufgegeben!=?')->execute(1, 1);
        $running = $objChronometry->numRows;

        $runnerstotal = $total - $dispensed;

        $objStats = new \stdClass();
        $objStats->total = $total;
        $objStats->dispensed = $dispensed;
        $objStats->arrived = $arrived;
        $objStats->running = $running;
        $objStats->abandoned = $abandoned;
        $objStats->runnerstotal = $runnerstotal;

        return $objStats;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        Controller::loadLanguageFile('tl_chronometry');
        $aCat = array();
        $arrCats = Config::get('chronometry_categories');
        if(!empty($arrCats) && is_array($arrCats)){
            foreach($arrCats as $cat){
                $objCat = new \stdClass();
                $objCat->id = $cat;
                $objCat->label = $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$cat] != '' ? $GLOBALS['TL_LANG']['tl_chronometry']['categories'][$cat] : 'undefined';
                $aCat[] = $objCat;
            }
        }
        return $aCat;
    }

    /**
     * @param $id
     * @return int|string
     */
    public function getRank($id)
    {
        $objAthlete = \ChronometryModel::findByPk($id);

        if ($objAthlete !== null)
        {
            if ($objAthlete->runningtimeUnix < 1)
            {
                return 0;
            }

            $objDb = $this->Database->prepare('SELECT * FROM tl_chronometry WHERE runningtimeUnix > 0 AND published=? AND category=? ORDER BY runningtimeUnix ASC')->execute(1, $objAthlete->category);
            $values = $objDb->fetchEach('runningtimeUnix');

            $i = 1;
            $dupl = 0;
            $lastScore = '';
            foreach ($values as $k => $score)
            {
                if ($lastScore == $score)
                {
                    $dupl++;
                }
                else
                {
                    $dupl = 0;
                }
                if ($score == $objAthlete->runningtimeUnix)
                {
                    return $i - $dupl;
                }
                $i++;
                $lastScore = $score;
            }
        }
        return 0;
    }
}
