<?php
if (!defined('MEDIAWIKI')) die();
/**
 * This package contains all data access objects.
 *
 * @package DataAccessObject
 */

/**
 * UserphonesDAO maintains a relationship between a user and phone number.
 * One user can have multiple phones assigned.
 *
 * @author Emir Habul <emiraga@gmail.com>
 * @package DataAccessObject
 */
class UserphonesDAO
{
    /** @var UserVO */ private $user;
    private $statusdesc = array(
            vPHONE_NEW => 'New phone number',
            vPHONE_SENT_CODE => 'Confirmation code send by SMS',
            vPHONE_VERIFIED => 'Phone verified',
            vPHONE_DELETED => 'Phone has been cancelled',
    );
    /**
     * Construct this class
     *
     * @param MwUser $user
     */
    public function __construct(UserVO &$user)
    {
        $this->user =& $user;
        if($this->user->isAnon)
            throw new Exception('Must be logged in to manage phones.');
    }
    /**
     * Add new phone number for this user
     *
     * @param String $number
     * @return Integer New insert ID
     */
    public function addNewPhone($number)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}phone (userID, phonenumber, status, dateadded) VALUES (?,?,?,?)",
                array($this->user->userID, $number, vPHONE_NEW, $now));
        return $vgDB->Insert_ID();
    }
    /**
     * Get a list of phones for this user.
     *
     * @return Array that contains associative arrays
     * with keys 'id', 'dateadded', 'number', 'status', 'description'
     */
    public function getList()
    {
        $result = array();
        global $vgDB, $vgDBPrefix;
        $p = $vgDB->GetALL("SELECT phoneID, phonenumber, status, dateadded FROM {$vgDBPrefix}phone WHERE userID = ?", array($this->user->userID));
        foreach($p as $phone)
        {
            $result[] = array(
                    'id' => $phone['phoneID'],
                    'dateadded' => $phone['dateadded'],
                    'number' => $phone['phonenumber'],
                    'status' => $phone['status'],
                    'description' => $this->statusdesc [ $phone['status'] ],
            );
        }
        return $result;
    }
    /**
     * Check if user is allowed to request a confirmation code send to SMS.
     *
     * @return Boolean if user is allowed to request confirmation code
     */
    public function checkConfirmAllowed()
    {
        global $vgDB, $vgDBPrefix;
        $yesterday = vfDate( time() - 24*60*60 );
        $c = $vgDB->GetOne("SELECT phoneID FROM {$vgDBPrefix}phone WHERE userID = ? AND confirmsent > ?",
                array($this->user->userID, $yesterday));
        return $c === false;
    }
    /**
     * Set the new confirmation code for user's phone
     * You should call checkConfirmAllowed() to see if it is allowed to request
     * another code.
     *
     * @param Integer $phoneid id of record
     * @return String conformation code
     */
    public function getConfirmCode($phoneid)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $c = $vgDB->GetOne("SELECT count(phoneID) FROM {$vgDBPrefix}phone WHERE phoneID = ? and userID = ? AND status != ?",
                array($phoneid, $this->user->userID, vPHONE_VERIFIED));
        if($c == 0)
            throw new Exception("No such record in phone.");

        $confirm = $this->getNewCode();
        $vgDB->Execute("UPDATE {$vgDBPrefix}phone SET confirmsent = ?, confirmcode = ?, status = ?  WHERE phoneID = ?",
                array($now, $confirm, vPHONE_SENT_CODE, $phoneid));
        return $confirm;
    }
    /**
     * Get phone number.
     *
     * @param Integer $phoneid record ID
     * @return String phone number
     */
    public function getPhoneNumber($phoneid)
    {
        global $vgDB, $vgDBPrefix;
        $number = $vgDB->GetOne("SELECT phonenumber FROM {$vgDBPrefix}phone WHERE phoneID = ? and userID = ?",
                array($phoneid, $this->user->userID, vPHONE_VERIFIED));
        if($number == false)
            throw new Exception("No such record in phone.");
        return $number;
    }
    /**
     * Get new code that will be user for phone confirmation via SMS.
     *
     * @return String confirmation code
     */
    private function getNewCode()
    {
        global $vgConfirmCodeLen;
        $code = '';
        for($i = 0; $i < $vgConfirmCodeLen; $i++)
            $code .= rand(0, 9);
        return $code;
    }
    /**
     * Verify if suplied code is valid
     *
     * @param Integer $phoneid id of a record
     * @param String $code supplied code
     * @return Boolean true
     */
    public function verifyCode($phoneid, $code)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $yesterday = vfDate( time() - 24*60*60 );
        $number = $vgDB->GetOne("SELECT phonenumber FROM {$vgDBPrefix}phone WHERE phoneID = ? AND userID = ? AND confirmcode = ? AND confirmsent > ? AND status = ?",
                array($phoneid, $this->user->userID, $code, $yesterday, vPHONE_SENT_CODE));
        if(! $number)
            throw new Exception("Invalid confirmation code.");

        $this->addVerifiedPhone($number);
        return true;
    }
    /**
     * Mark phone as deleted.
     *
     * @param Integer $phoneid record id
     * @return Boolean true
     */
    public function deletePhone($phoneid)
    {
        global $vgDB, $vgDBPrefix;
        $c = $vgDB->GetOne("SELECT count(phoneID) FROM {$vgDBPrefix}phone WHERE userID = ? AND phoneID = ?",
                array($this->user->userID, $phoneid));
        if(! $c)
            throw new Exception("Invalid code or it has expired.");
        $vgDB->Execute("UPDATE {$vgDBPrefix}phone SET status = ?, confirmsent = NULL, confirmcode = NULL WHERE phoneID = ?",
                array(vPHONE_DELETED, $phoneid));
        return true;
    }
    /**
     * Resolve a userID from given phone number.
     *
     * @param String $phone telephone number
     * @return String userID, or Boolean false if it does not exist.
     */
    static function getUserIDFromPhone($phone)
    {
        global $vgDB, $vgDBPrefix;
        return $vgDB->GetOne("SELECT userID FROM {$vgDBPrefix}phone WHERE phonenumber = ? AND status >= ?",
                array($phone, vPHONE_VERIFIED));
    }
    /**
     * Add a phone number to the user and
     * automatically mark this phone number as verified.
     * Delete if this number was added previously.
     *
     * @param String $phone
     */
    function addVerifiedPhone($phone)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("DELETE FROM {$vgDBPrefix}phone WHERE phonenumber = ?",
                array( $phone ));
        
        $now = vfDate();
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}phone (userID, phonenumber, status, dateadded) VALUES (?,?,?,?)",
                array($this->user->userID, $phone, vPHONE_VERIFIED, $now));
    }
}

