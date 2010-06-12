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
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}user (username, password) VALUES (?,?)",
                array($user->username, $user->password));
        $user->userID = $vgDB->Insert_ID();
        return $user->userID;
    }
    /**
     * Find a user by name.
     *
     * @return UserVO
     */
    function findByName($username)
    {
        global $vgDB, $vgDBPrefix;
        $r = $vgDB->Execute( "SELECT userID, password FROM {$vgDBPrefix}user WHERE username = ?", array($name) );
        if($r == false)
            throw new SurveyException("Cannot find user");
        $user = new UserVO();
        $user->userID = $r->fields['userID'];
        $user->username = $username;
        $user->password = $r->fields['password'];

        return $user;
    }
    /**
     * Create a new user by performing a POST request to the MediaWiki.
     * This is a very ugly hack. Needs to be improved. @todo Fix this.
     *
     * @return Boolean success true of false
     */
    function requestNewUser($username, $password, $realname)
    {
        //@todo *BUG* this part is very fragile, captcha extension can prevent this from working
        global $wgServer, $wgScriptPath, $wgScriptExtension;
        $url = "{$wgServer}{$wgScriptPath}/index$wgScriptExtension?title=Special:UserLogin&action=submitlogin&type=signup";

        $post = "wpName=".urlencode($username);
        $post .= "&wpPassword=".urlencode($password);
        $post .= "&wpRetype=".urlencode($password);
        $post .= "&wpRealName=".urlencode($realname);
        $post .= "&wpCreateaccount=Create+account";

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec ($ch);
        curl_close ($ch);
        return !strstr($data, 'errorbox');
    }
    /**
     * Pick a new username, create that account and send an SMS.
     *
     * @param String $phonenumber
     * @return UserVO
     */
    function newUserFromPhone($phonenumber, $send_sms = false)
    {
        global $vgDB, $vgDBPrefix;
        $password = rand(1000,9999);

        for($i=0;$i<50;$i++)
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
            if($this->requestNewUser($name, $password, $phonenumber))
            {
                if($send_sms)
                {
                    Sms::sendSMS($phonenumber, sprintf(Sms::$msgCreateUser, $name, $password));
                }
                $user = new UserVO();
                $user->username = $name;
                $user->password = $password;
                $this->insert($user);
                
                UserphonesDAO::addVerifiedPhone($name, $phonenumber);
                return $user;
            }
        }
        throw new SurveyException('Could not create a new user');
    }
}

