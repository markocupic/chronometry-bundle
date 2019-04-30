<?php

/**
 * Chronometry Module for Contao CMS
 * Copyright (c) 2008-2019 Marko Cupic
 * @package chronometry-bundle
 * @author Marko Cupic m.cupic@gmx.ch, 2019
 * @link https://github.com/markocupic/chronometry-bundle
 */

namespace Markocupic;

use Contao\ChronometryModel;
use Contao\Controller;
use Contao\Config;
use Contao\Database;

/**
 * Class Chronometry
 * @package Markocupic
 */
class Chronometry
{

    /**
     * @param $row
     * @return \stdClass
     */
    public static function getRowAsObject($row)
    {
        $objRow = new \stdClass();
        foreach ($row as $k => $v)
        {
            $objRow->{$k} = $v;
        }
        $objRow->fullname = $row['firstname'] . ' ' . $row['lastname'];
        $objRow->runningtimeUnix = static::makeTimestamp($row['runningtime']);
        $objRow->starttimeUnix = static::makeTimestamp($row['starttime']);
        $objRow->endtimeUnix = static::makeTimestamp($row['endtime']);
        $objRow->rank = static::getRank($row['id']);
        $objRow->requesting = false;


        return $objRow;
    }

    /**
     * @param $id
     * @return int|string
     */
    public static function getRank($id)
    {
        $objAthlete = ChronometryModel::findByPk($id);

        if ($objAthlete !== null)
        {
            if ($objAthlete->runningtimeUnix < 1)
            {
                return 0;
            }

            $objDb = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE runningtimeUnix > 0 AND published=? AND category=? ORDER BY runningtimeUnix ASC')->execute(1, $objAthlete->category);
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

    /**
     * @return \stdClass
     */
    public static function getStats()
    {
        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE published=?')->execute(0);
        $dispensed = $objChronometry->numRows;

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry')->execute();
        $total = $objChronometry->numRows;

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE published=? AND hasGivenUp=?')->execute(1, 1);
        $hasGivenUp = $objChronometry->numRows;

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE published=? AND runningtimeUnix > 0 AND hasGivenUp!=?')->execute(1, 1);
        $haveFinished = $objChronometry->numRows;

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE published=? AND runningtimeUnix = 0 AND hasGivenUp!=?')->execute(1, 1);
        $running = $objChronometry->numRows;

        $runnerstotal = $total - $dispensed;

        $objStats = new \stdClass();
        $objStats->total = $total;
        $objStats->dispensed = $dispensed;
        $objStats->haveFinished = $haveFinished;
        $objStats->running = $running;
        $objStats->haveGivenUp = $hasGivenUp;
        $objStats->runnersTotal = $runnerstotal;

        return $objStats;
    }

    /**
     * @return array
     */
    public static function getCategories()
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
     * @param $strRegexp
     * @param $varValue
     * @param \Widget $objWidget
     * @return bool
     */
    public static function customRegexp($strRegexp, $varValue, \Widget $objWidget)
    {
        if ($strRegexp == 'chronometryTime')
        {
            if (!static::isValidTimeFormat($varValue))
            {
                $objWidget->addError('Field ' . $objWidget->label . ' should be a valid time like hh:mm:ss.');
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $strFormattedTime
     * @return bool
     */
    public static function isValidTimeFormat($strFormattedTime = '')
    {
        if ($strFormattedTime == '')
        {
            return true;
        }

        if (preg_match('/^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/', $strFormattedTime))
        {
            return true;
        }

        return false;

    }

    /**
     * @param string $time ('hh:mm:ss')
     * @return int
     */
    public static function makeTimestamp($time = '')
    {
        if (trim($time == ''))
        {
            return 0;
        }

        $time = explode(":", $time);
        if (count($time) != 3)
        {
            return 0;
        }
        return intval($time[0]) * 60 * 60 + intval($time[1]) * 60 + intval($time[2]);
    }

    /**
     * @param $start_time_o
     * @param $end_time_o
     * @return string
     */
    public static function getTimeDifference($start_time_o, $end_time_o)
    {
        if ($start_time_o == '' || $end_time_o == '')
        {
            return '';
        }
        $start_time = explode(":", $start_time_o);
        $end_time = explode(":", $end_time_o);

        $start_time_stamp = mktime($start_time[0], $start_time[1], $start_time[2], 0, 0, 0);
        $end_time_stamp = mktime($end_time[0], $end_time[1], $end_time[2], 0, 0, 0);

        $time_difference = $end_time_stamp - $start_time_stamp;

        return gmdate("H:i:s", $time_difference);
    }

    /**
     *
     */
    public static function synchronizeTime()
    {

        $set = array(
            'runningtimeUnix' => 0,
            'runningtime' => ''
        );

        // Set valid timestamps
        Database::getInstance()->prepare('UPDATE tl_chronometry %s WHERE endtime=? OR runningtime=? OR runningtime=?')->set($set)->execute('', '', 0);


        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE endtime!=?')->execute('');
        while ($objChronometry->next())
        {
            $objChronometryModel = ChronometryModel::findByPk($objChronometry->id);
            if ($objChronometryModel !== null)
            {
                $objChronometryModel->runningtime = static::getTimeDifference($objChronometry->starttime, $objChronometry->endtime);
                $objChronometryModel->save();
            }
        }

        $objChronometry = Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE runningtime!=?')->execute('');
        while ($objChronometry->next())
        {
            $objChronometryModel = ChronometryModel::findByPk($objChronometry->id);
            if ($objChronometryModel !== null)
            {
                $objChronometryModel->runningtimeUnix = static::makeTimestamp($objChronometry->runningtime);
                $objChronometryModel->save();
            }
        }
    }
}
