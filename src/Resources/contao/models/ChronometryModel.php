<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


/**
 * Reads and writes calendars
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property integer $jumpTo
 * @property boolean $protected
 * @property string  $groups
 * @property boolean $allowComments
 * @property string  $notify
 * @property string  $sortOrder
 * @property integer $perPage
 * @property boolean $moderate
 * @property boolean $bbcode
 * @property boolean $requireLogin
 * @property boolean $disableCaptcha
 *
 * @method static \CarModel|null findById($id, $opt=array())
 * @method static \CarModel|null findByPk($id, $opt=array())
 * @method static \CarModel|null findByIdOrAlias($val, $opt=array())
 * @method static \CarModel|null findOneBy($col, $val, $opt=array())
 * @method static \CarModel|null findOneByTstamp($val, $opt=array())
 * @method static \CarModel|null findOneByTitle($val, $opt=array())
 * @method static \CarModel|null findOneByJumpTo($val, $opt=array())
 * @method static \CarModel|null findOneByProtected($val, $opt=array())
 * @method static \CarModel|null findOneByGroups($val, $opt=array())
 * @method static \CarModel|null findOneByAllowComments($val, $opt=array())
 * @method static \CarModel|null findOneByNotify($val, $opt=array())
 * @method static \CarModel|null findOneBySortOrder($val, $opt=array())
 * @method static \CarModel|null findOneByPerPage($val, $opt=array())
 * @method static \CarModel|null findOneByModerate($val, $opt=array())
 * @method static \CarModel|null findOneByBbcode($val, $opt=array())
 * @method static \CarModel|null findOneByRequireLogin($val, $opt=array())
 * @method static \CarModel|null findOneByDisableCaptcha($val, $opt=array())
 *
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByTstamp($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByTitle($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByJumpTo($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByProtected($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByGroups($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByAllowComments($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByNotify($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findBySortOrder($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByPerPage($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByModerate($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByBbcode($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByRequireLogin($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findByDisableCaptcha($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findMultipleByIds($val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findBy($col, $val, $opt=array())
 * @method static \Model\Collection|\CarModel[]|\CarModel|null findAll($opt=array())
 *
 * @method static integer countById($id, $opt=array())
 * @method static integer countByTstamp($val, $opt=array())
 * @method static integer countByTitle($val, $opt=array())
 * @method static integer countByJumpTo($val, $opt=array())
 * @method static integer countByProtected($val, $opt=array())
 * @method static integer countByGroups($val, $opt=array())
 * @method static integer countByAllowComments($val, $opt=array())
 * @method static integer countByNotify($val, $opt=array())
 * @method static integer countBySortOrder($val, $opt=array())
 * @method static integer countByPerPage($val, $opt=array())
 * @method static integer countByModerate($val, $opt=array())
 * @method static integer countByBbcode($val, $opt=array())
 * @method static integer countByRequireLogin($val, $opt=array())
 * @method static integer countByDisableCaptcha($val, $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ChronometryModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_chronometry';

}
