<?php
if (!defined('MEDIAWIKI')) die();

not-used();
unused_codeee();

/**
 * This page includes classes which are used to access objects of Survey.
 *
 * @package DataAccessObject
 */

require_once("$vgPath/VO/CallVO.php");
require_once("$vgPath/DAO/VoteDAO.php");

/**
 * Class Usr includes functions which can vote surveys so far
 *
 * @author Bai Qifeng
 * @version 2.0
 */
class Usr
{
    private $usrID;
    /**
     * Initiate a voter
     *
     * @param $username ID of the user, leave blank if it is unknown
     */
    function __construct($username)
    {
        $this->usrID = $username;
    }
    /**
     * Get username of this user (usrID)
     *
     * @return string username
     */
    function getUsername()
    {
        return $this->usrID;
    }

}
?>