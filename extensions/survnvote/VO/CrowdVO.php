<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/**
 * User value object for crowd
 *
 * @package ValueObject
 */
class CrowdVO
{
    /** @var Integer */ public $crowdID;
    /** @var String  */ public $name;
    /** @var String  */ public $description = '';
    /** @var Integer */ public $ownerID = 0;
    /** @var Integer */ public $no_members = 0;
}
/**
 * User value object for crowd membership
 *
 * @package ValueObject
 */
class CrowdMemberVO
{
    /** @var Integer  */ public $crowdID;
    /** @var Integer  */ public $userID;
    /** @var Boolean  */ public $is_manager;
    /** @var Datetime */ public $date_added;
    /** @var Boolean*/   public $show_password;
}
/**
 * User value object for crowd logs
 *
 * @package ValueObject
 */
class CrowdLogVO
{
    /** @var Integer  */ public $crowdID;
    /** @var String   */ public $date_added;
    /** @var String   */ public $log;
    /** @var Boolean  */ public $printable = false;
}

