<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/VO/CrowdVO.php");
/**
 * Class for managing Crowds
 *
 * @package DataAccessObject
 */
class CrowdDAO
{
    /**
     * Add new crowd to database.
     *
     * @param CrowdVO $crowd
     * @return Integer user ID
     */
    function insert(CrowdVO &$crowd)
    {
        global $vgDB, $vgDBPrefix;
        if($this->findByName($crowd->name) !== false)
            throw new SurveyException('Crowd exists with same name, please choose a different one');
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}crowd (name, description, ownerID, no_members) VALUES (?,?,?,?)",
            array($crowd->name, $crowd->description, $crowd->ownerID, $crowd->no_members));
        $crowd->crowdID = $vgDB->Insert_ID();
        return $crowd->crowdID;
    }
    /**
     * Find a crowd by name.
     *
     * @return CrowdVO or Boolean false if it cannot be found.
     */
    function findByName($name)
    {
        global $vgDB, $vgDBPrefix;
        $r = $vgDB->Execute( "SELECT description, ownerID, crowdID, no_members FROM {$vgDBPrefix}crowd WHERE name = ?", array($name) );
        if($r->RecordCount() == 0)
            return false;
        $crowd = new CrowdVO();
        $crowd->crowdID = intval( $r->fields['crowdID'] );
        $crowd->ownerID = intval( $r->fields['ownerID'] );
        $crowd->name = $name;
        $crowd->description = $r->fields['description'];
        $crowd->no_members = $r->fields['no_members'];

        return $crowd;
    }
    /**
     * Find a crowd by crowdID.
     *
     * @return CrowdVO or Boolean false if it cannot be found.
     */
    function findByID($crowdID)
    {
        global $vgDB, $vgDBPrefix;
        $r = $vgDB->Execute( "SELECT description, ownerID, name, no_members FROM {$vgDBPrefix}crowd WHERE crowdID = ?", array($crowdID) );
        if($r->RecordCount() == 0)
            return false;
        $user = new CrowdVO();
        $user->crowdID = $crowdID;
        $user->name = $r->fields['name'];
        $user->description = $r->fields['description'];
        $user->ownerID = $r->fields['ownerID'];
        $user->no_members = $r->fields['no_members'];
        return $user;
    }
    /**
     * Get a list of crowds for a given user.
     * 
     * @param Integer $userID
     * @return Array of CrowdVO
     */
    function getCrowdsOfUser($userID)
    {
        global $vgDB, $vgDBPrefix;
        $pr = $vgDBPrefix;
        $r = $vgDB->GetALL( "SELECT * FROM {$vgDBPrefix}crowd_member LEFT JOIN {$vgDBPrefix}crowd "
        ."USING (crowdID) WHERE {$vgDBPrefix}crowd_member.userID = ?", array(intval($userID)));
        $result = array();
        foreach($r as $member)
        {
            $crowd = new CrowdVO();
            $crowd->crowdID = $member['crowdID'];
            $crowd->name = $member['name'];
            $crowd->description = $member['description'];
            $crowd->ownerID = $member['ownerID'];
            $crowd->no_members = $member['no_members'];
            $crowd->date_added = $member['date_added'];
            $crowd->isManager = $member['isManager'];
            $result[] = $crowd;
        }
        return $result;
    }
    /**
     * Get a list of members of a crowd.
     *
     * @param CrowdVO $crowd
     * @return Array of CrowdMemberVO
     */
    function getCrowdMembers(CrowdVO &$crowd)
    {
        $result = array();
        global $vgDB, $vgDBPrefix;
        $r = $vgDB->GetALL( "SELECT * FROM {$vgDBPrefix}crowd_member WHERE crowdID = ?", 
                array(intval($crowd->crowdID)));
        foreach($r as $member)
        {
            $mvo = new CrowdMemberVO();
            $mvo->crowdID = $member['crowdID'];
            $mvo->date_added = $member['date_added'];
            $mvo->is_manager = $member['isManager'];
            $mvo->show_password = $member['show_password'];
            $mvo->userID = $member['userID'];

            $result[] = $mvo;
        }
        return $result;
    }
    /**
     * Add user to crowd.
     * 
     * @param Integer $crowdID
     * @param Integer $userID
     * @param Boolean $isManager
     * @param Boolean $showpassword
     */
    function addUserToCrowd($crowdID, $userID, $isManager = false, $showpassword = false)
    {
        global $vgDB, $vgDBPrefix;
        $prev = $vgDB->GetOne("SELECT userID FROM {$vgDBPrefix}crowd_member WHERE crowdID = ? AND userID = ? ",
                array(intval($crowdID), intval($userID)));
        if($prev == $userID)
            return;
        $now = vfDate();
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}crowd_member (crowdID,userID,isManager,show_password,date_added) VALUES (?,?,?,?,?)",
                array( $crowdID, $userID, $isManager, $showpassword, $now));
        $vgDB->Execute("UPDATE {$vgDBPrefix}crowd SET no_members = no_members + 1 WHERE crowdID = ?",array($crowdID));
    }
    /**
     * Is user manager of this crowd.
     *
     * @param Integer $crowdID
     * @param Integer $userID
     * @return Boolean
     */
    function isManager($crowdID, $userID)
    {
        global $vgDB, $vgDBPrefix;
        return (bool) $vgDB->GetOne("SELECT isManager FROM {$vgDBPrefix}crowd_member WHERE crowdID = ? AND userID = ? ",
                array(intval($crowdID), intval($userID)));
    }
    /**
     * Is user member of a crowd.
     * 
     * @param Integer $crowdID
     * @param Integer $userID
     * @return Boolean
     */
    function isMember($crowdID, $userID)
    {
        global $vgDB, $vgDBPrefix;
        return (bool) $vgDB->GetOne("SELECT count(userID) FROM {$vgDBPrefix}crowd_member WHERE crowdID = ? AND userID = ? ",
                array(intval($crowdID), intval($userID)));
    }
    /**
     * Add message to the crowd log.
     * 
     * @param Integer $crowdID
     * @param String $text
     * @param Boolean $printable 
     */
    function addLog($crowdID, $text, $printable = false)
    {
        $log = new CrowdLogVO();
        $log->crowdID = $crowdID;
        $log->date_added = vfDate();
        $log->log = htmlspecialchars( $text );
        $log->printable = $printable;

        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}crowd_log (crowdID,date_added,log,printable) VALUES (?,?,?,?)",
                array( $log->crowdID, $log->date_added, $log->log, $log->printable));
    }
    /**
     * Get a list of logs for a crowd.
     *
     * @param Integer $crowdID
     * @param Boolean $only_printable
     * @return Array of CrowdLogVO
     */
    function getLogs($crowdID, $only_printable = false)
    {
        global $vgDB, $vgDBPrefix;
        if($only_printable)
        {
            $r = $vgDB->GetAll("SELECT * FROM {$vgDBPrefix}crowd_log WHERE crowdID = ? AND printable = 1",
                    array(intval($crowdID)));
        }
        else
        {
            $r = $vgDB->GetAll("SELECT * FROM {$vgDBPrefix}crowd_log WHERE crowdID = ?",
                    array(intval($crowdID)));
        }
        $result = array();
        foreach($r as $log)
        {
            $logvo = new CrowdLogVO();
            $logvo->crowdID = $crowdID;
            $logvo->date_added = $log['date_added'];
            $logvo->printable = $log['printable'];
            $logvo->log = $log['log'];
            $result[] = $logvo;
        }
        return $result;
    }
    /**
     *
     * @param Integer $crowdID
     * @return String HTML code for link to crowd
     */
    function makeLink($crowdID)
    {
        if($crowdID == 0)
        {
            return 'Everyone';
        }
        $crowd = $this->findByID($crowdID);
        if($crowd === false)
        {
            return 'Unknown crowd';
        }
        $showname = str_replace('_', ' ', $crowd->name);
        return '<a href="'.Skin::makeSpecialUrlSubpage('Crowd', $crowd->name).'">'.$showname.'</a>';
    }
}

