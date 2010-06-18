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
        $user = new CrowdVO();
        $user->crowdID = intval( $r->fields['crowdID'] );
        $user->ownerID = intval( $r->fields['ownerID'] );
        $user->name = $name;
        $user->description = $r->fields['description'];
        $user->no_members = $r->fields['no_members'];

        return $user;
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

    function getCrowdsOfUser($userID)
    {
        global $vgDB, $vgDBPrefix;
        $pr = $vgDBPrefix;
        $r = $vgDB->GetALL( "SELECT crowdID, isManager, date_added FROM {$vgDBPrefix}crowd_member WHERE userID = ?", array(intval($userID)));
        $result = array();
        foreach($r as $member)
        {
            $crowd = $this->findByID( $member['crowdID'] );
            $crowd->date_added = $member['date_added'];
            $crowd->isManager = $member['isManager'];
            $result[] = $crowd;
        }
        return $result;
    }
    function addUserToCrowd($crowdID, $userID, $isManager = false, $showpassword = false)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}crowd_member (crowdID,userID,isManager,show_password,date_added) VALUES (?,?,?,?,?)",
                array( $crowdID, $userID, $isManager, $showpassword, $now));
    }
}
