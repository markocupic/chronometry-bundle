<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 15.05.2016
 * Time: 22:59
 */

namespace Markocupic;


class Chronometry extends \System
{
    /**
     * @param $strRegexp
     * @param $varValue
     * @param \Widget $objWidget
     * @return bool
     */
    public function customRegexp($strRegexp, $varValue, \Widget $objWidget)
    {
        if ($strRegexp == 'chronometryTime')
        {
            if (!self::isValidTimeFormat($varValue))
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
        \Database::getInstance()->prepare('UPDATE tl_chronometry %s WHERE endtime=? OR runningtime=? OR runningtime=?')->set($set)->execute('', '', 0);


        $objChronometry = \Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE endtime!=?')->execute('');
        while ($objChronometry->next())
        {
            $objChronometryModel = \ChronometryModel::findByPk($objChronometry->id);
            if ($objChronometryModel !== null)
            {
                $objChronometryModel->runningtime = self::getTimeDifference($objChronometry->starttime, $objChronometry->endtime);
                $objChronometryModel->save();
            }
        }

        $objChronometry = \Database::getInstance()->prepare('SELECT * FROM tl_chronometry WHERE runningtime!=?')->execute('');
        while ($objChronometry->next())
        {
            $objChronometryModel = \ChronometryModel::findByPk($objChronometry->id);
            if ($objChronometryModel !== null)
            {
                $objChronometryModel->runningtimeUnix = self::makeTimestamp($objChronometry->runningtime);
                $objChronometryModel->save();
            }
        }
    }
}
