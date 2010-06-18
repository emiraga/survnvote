<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/**
 * User value object
 *
 * @package ValueObject
 */
class CrowdVO
{
    /** @var Integer */ public $crowdID;
    /** @var String  */ public $name;
    /** @var String  */ public $description = '';
    /** @var Integer  */ public $ownerID = 0;
    /** @var Integer  */ public $no_members = 0;

}

