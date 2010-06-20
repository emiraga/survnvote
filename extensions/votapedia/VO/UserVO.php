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
class UserVO
{
    /** @var Integer */ public $userID;
    /** @var String  */ public $username;
    /** @var String  */ public $password;
    /** @var Boolean */ public $isAnon;
    /** @var String  */ public $smsConfirm;
    public function __construct()
    {
        global $vgConfirmCodeLen;
        $this->smsConfirm = '';
        for($i = 0; $i < $vgConfirmCodeLen; $i++)
            $this->smsConfirm .= rand(0, 9);
    }
    public function getConfirmCode()
    {
        return $this->smsConfirm.$this->userID;
    }
}

