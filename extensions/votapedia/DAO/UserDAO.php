<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/VO/UserVO.php");
require_once("$vgPath/SMS.php");

/**
 * Class for managing users in database
 *
 * @package DataAccessObject
 */
class UserDAO
{
    /**
     * Add new user to database.
     *
     * @param UserVO $user
     * @return Integer user ID
     */
    function insert(UserVO &$user)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}user (isAnon, username, password) VALUES (?,?,?)",
                array($user->isAnon, $user->username, $user->password));
        $user->userID = $vgDB->Insert_ID();
        return $user->userID;
    }
    /**
     * Find a user by name.
     *
     * @return UserVO or Boolean false if it cannot be found.
     */
    function findByName($username)
    {
        global $vgDB, $vgDBPrefix;
        $r = $vgDB->Execute( "SELECT isAnon, userID, password FROM {$vgDBPrefix}user WHERE username = ?", array($username) );
        if($r->RecordCount() == 0)
            return false;
        $user = new UserVO();
        $user->isAnon = $r->fields['isAnon'];
        $user->userID = intval( $r->fields['userID'] );
        $user->username = $username;
        $user->password = $r->fields['password'];
        return $user;
    }
    /**
     * Find a user by userID.
     *
     * @return UserVO or Boolean false if it cannot be found.
     */
    function findByID($userID)
    {
        global $vgDB, $vgDBPrefix;
        $r = $vgDB->Execute( "SELECT isAnon, username, password FROM {$vgDBPrefix}user WHERE userID = ?", array($userID) );
        if($r->RecordCount() == 0)
            return false;
        $user = new UserVO();
        $user->userID = $userID;
        $user->isAnon = $r->fields['isAnon'];
        $user->username = $r->fields['username'];
        $user->password = $r->fields['password'];
        return $user;
    }
    /**
     * Create a new user by performing a GET request to the MediaWiki API.
     *
     * @return Boolean success true of false
     */
    function requestNew($username, $password, $realname)
    {
        global $wgServer, $wgScriptPath, $wgScriptExtension, $wgSecretKey;

        $secretkey = sha1($wgSecretKey);
        
        $url = "{$wgServer}{$wgScriptPath}/api$wgScriptExtension?action=vpAutoUser";
        $url .= "&secretkey=".$secretkey;
        $url .= "&format=php";
        $url .= "&name=".urlencode($username);
        $url .= "&password=".urlencode($password);
        $url .= "&realname=".urlencode($realname);

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec ($ch);
        curl_close ($ch);

        $data = unserialize( $data );
        return isset($data['success']);
    }
    /**
     * Pick a new username, create that account and send an SMS.
     * 
     * @param String $phonenumber
     * @return UserVO
     */
    function newFromPhone($phonenumber, $send_sms = false)
    {
        global $vgDB, $vgDBPrefix;
        $password = rand(100000,999999);

        for($i=0;$i<500;$i++)
        {
            $name = $vgDB->GetOne("SELECT name FROM {$vgDBPrefix}names WHERE taken = 0");
            if($name == false)
                $name = rand(100000, 999999);
            else
            {
                $vgDB->Execute("UPDATE {$vgDBPrefix}names SET taken = 1 WHERE name = ?", array($name));
                //wiki names start with capital letter
                $name[0] = strtoupper($name[0]);
            }

            if($this->requestNew($name, $password, $phonenumber))
            {
                if($send_sms)
                {
                    Sms::sendSMS($phonenumber, sprintf(Sms::$msgCreateUser, $name, $password));
                }
                $user = new UserVO();
                $user->username = $name;
                $user->password = $password;
                $user->isAnon = false;
                $this->insert($user);
                $phonedao = new UserphonesDAO($user);
                $phonedao->addVerifiedPhone($user->userID, $phonenumber);
                return $user;
            }
        }
        throw new SurveyException('Could not create a new user');
    }
}

