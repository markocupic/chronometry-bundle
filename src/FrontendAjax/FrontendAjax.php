<?php

/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */

namespace Markocupic\ChronometryBundle\FrontendAjax;

use Contao\ChronometryModel;
use Contao\Database;
use Contao\Folder;
use Contao\Input;
use Markocupic\Chronometry;
use Markocupic\ExportTable\ExportTable;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class FrontendAjax
 * @package Markocupic\ChronometryBundle\FrontendAjax
 */
class FrontendAjax
{

    /**
     * @return JsonResponse
     */
    public function checkOnlineStatus()
    {

        $arrJson = array();
        $arrJson['status'] = 'success';
        $response = new JsonResponse($arrJson);
        return $response->send();
    }

    /**
     * @return JsonResponse
     */
    public function getDataAll()
    {

            $arrItems = array();
            $arrJson = array();
            $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE published=? ORDER BY starttime ASC, stufe ASC, teachername, gender')->execute(1, 2);
            while ($row = $objChronometry->fetchAssoc())
            {
                $arrItems[] = Chronometry::getRowAsObject($row);
            }

            $arrJson['status'] = 'success';
            $arrJson['stats'] = Chronometry::getStats();
            $arrJson['data'] = $arrItems;
            $arrJson['categories'] = Chronometry::getCategories();

            $response = new JsonResponse($arrJson);
            return $response->send();
    }


    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function saveRow()
    {

        // Save Endtime
        if (Input::post('id') != '')
        {

            $objChronometry = ChronometryModel::findByPk(Input::post('id'));
            if ($objChronometry !== null)
            {
                $objChronometry->endtime = Input::post('endtime');

                // Athlet has given up the race
                $objChronometry->hasGivenUp = Input::post('hasGivenUp');
                if (Input::post('hasGivenUp') == 1)
                {
                    $objChronometry->endtime = '';
                }

                $objChronometry->runningtime = Chronometry::getTimeDifference($objChronometry->starttime, $objChronometry->endtime);
                $objChronometry->runningtimeUnix = Chronometry::makeTimestamp($objChronometry->runningtime);
                $objChronometry->tstamp = time();
                $objChronometry->save();

                $arrItems = array();
                $arrJson = array();
                $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE published=? ORDER BY starttime ASC, stufe ASC, teachername, gender')->execute(1, 2);
                while ($row = $objChronometry->fetchAssoc())
                {
                    $arrItems[] = Chronometry::getRowAsObject($row);
                }

                $arrJson['status'] = 'success';
                $arrJson['stats'] = Chronometry::getStats();
                $arrJson['data'] = $arrItems;
                $arrJson['categories'] = Chronometry::getCategories();

                // Do backup
                new Folder('files/chronometry-backup');
                ExportTable::exportTable('tl_chronometry', array('strDestination' => 'files/chronometry-backup'));
                $response = new JsonResponse($arrJson);
                return $response->send();
            }
        }
    }

}
