<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php");
require_once("$vgPath/VO/UserVO.php");
require_once("$vgPath/API/AutocreateUsers.php");

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
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}user (isAnon, username, password, smsConfirm) VALUES (?,?,?,?)",
                array($user->isAnon, $user->username, $user->password, $user->smsConfirm));
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
        $r = $vgDB->Execute( "SELECT * FROM {$vgDBPrefix}user WHERE username = ?", array($username) );
        if($r->RecordCount() == 0)
            return false;
        $user = new UserVO();
        $user->isAnon = $r->fields['isAnon'];
        $user->userID = intval( $r->fields['userID'] );
        $user->username = $username;
        $user->password = $r->fields['password'];
        $user->smsConfirm = $r->fields['smsConfirm'];
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
        $r = $vgDB->Execute( "SELECT * FROM {$vgDBPrefix}user WHERE userID = ?", array($userID) );
        if($r->RecordCount() == 0)
            return false;
        $user = new UserVO();
        $user->userID = $userID;
        $user->isAnon = $r->fields['isAnon'];
        $user->username = $r->fields['username'];
        $user->password = $r->fields['password'];
        $user->smsConfirm = $r->fields['smsConfirm'];
        return $user;
    }
    /**
     * Generate new user for Mediawiki.
     *
     * @param String $realname optional value for real name of user
     * @param String $email optional value for email of user
     * @return UserVO
     */
    function generateNewUser($realname='', $email='')
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
            if( AutocreateUsers::create($name, $password, $realname, $email) )
            {
                $user = new UserVO();
                $user->username = $name;
                $user->password = $password;
                $user->isAnon = false;
                $this->insert($user);
                return $user;
            }
        }
        throw new SurveyException('Could not create a new user');
    }
    /**
     * Pick a new username, create that account and add verified phone.
     *
     * @param String $phonenumber
     * @return UserVO
     */
    function newFromPhone($phonenumber)
    {
        $user =& $this->generateNewUser();
        $phonedao = new UserphonesDAO($user);
        $phonedao->addVerifiedPhone($phonenumber);
        return $user;
    }
    /**
     * Pick a new username, create that account and add email.
     *
     * @param String $email
     * @return UserVO
     */
    function newFromEmail($email)
    {
        return $this->generateNewUser('', $email);
    }
    /**
     * Invalidate password in database.
     *
     * @param String $username
     */
    static function invalidatePassword($username)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute( "UPDATE {$vgDBPrefix}user SET password = '' WHERE username = ?", array($username) );
    }
    /**
     * Hook function for MediaWiki PrefsPasswordAudit
     * When password is changed by user, this will be called
     * http://www.mediawiki.org/wiki/Manual:Hooks/PrefsPasswordAudit
     *
     * @param User $user
     * @param String $newPass
     * @param String $error
     * @return Boolean
     */
    static function PrefsPasswordAudit(User $user, $newPass, $error)
    {
        if($error == 'success')
            UserDAO::invalidatePassword( $user->getName() );
        return true;
    }
    /**
     * Check if code for SMS confirmation is valid.
     *
     * @param String $code SMS confirm code
     * @return UserVO or Boolean if value is not valid
     */
    public function checkValidConfirmCode($code)
    {
        global $vgConfirmCodeLen;
        $confirm = substr($code, 0, $vgConfirmCodeLen);
        $userID = intval(trim(substr($code, $vgConfirmCodeLen)));

        $user = $this->findByID($userID);
        if($user && $user->smsConfirm == $confirm)
            return $user;
        else
            return false;
    }
}

