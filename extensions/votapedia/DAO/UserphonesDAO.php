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
 */
class UserphonesDAO
{
    /** @var MwUser */ private $user;
    private $statusdesc = array(
        vPHONE_NEW => 'New phone number',
        vPHONE_SENT_CODE => 'Confirmation code send by SMS',
        vPHONE_VERIFIED => 'Phone verified',
        vPHONE_DELETED => 'Phone has been cancelled',
    );
    /**
     * Construct this class
     *
     * @param $user MwUser
     */
    public function __construct(MwUser &$user)
    {
        $this->user =& $user;
        if($this->user->isAnon())
            throw new Exception("Cannot add phone numbers to anonymous user.");
    }
    /**
     * Add new phone number for this user
     *
     * @param $number String
     * @return Integer New insert ID
     */
    public function addNewPhone($number)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}userphones (username, phonenumber, status, dateadded) VALUES (?,?,?,?)",
                array($this->user->getName(), $number, vPHONE_NEW, $now));
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
        $p = $vgDB->GetALL("SELECT id, phonenumber, status, dateadded FROM {$vgDBPrefix}userphones WHERE username = ?", array($this->user->getName()));
        foreach($p as $phone)
        {
            $result[] = array(
                'id' => $phone['id'],
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
        $c = $vgDB->GetOne("SELECT count(id) FROM {$vgDBPrefix}userphones WHERE username = ? AND confirmsent > ?",
                array($this->user->getName(), $yesterday));
        return $c == 0;
    }
    /**
     * Set the new confirmation code for user's phone
     * You should call checkConfirmAllowed() to see if it is allowed to request
     * another code.
     *
     * @param $id Integer id of record
     * @return Strign conformation code
     */
    public function getConfirmCode($id)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $c = $vgDB->GetOne("SELECT count(id) FROM {$vgDBPrefix}userphones WHERE id = ? and username = ? AND status != ?",
                array($id, $this->user->getName(), vPHONE_VERIFIED));
        if($c == 0)
            throw new Exception("No such record in userphones.");

        $confirm = $this->getNewCode();
        $vgDB->Execute("UPDATE {$vgDBPrefix}userphones SET confirmsent = ?, confirmcode = ?, status = ?  WHERE id = ?",
                array($now, $confirm, vPHONE_SENT_CODE, $id));
        return $confirm;
    }
    /**
     * Get phone number
     * 
     * @param $id Integer record ID
     * @return String phone number
     */
    public function getPhoneNumber($id)
    {
        global $vgDB, $vgDBPrefix;
        $number = $vgDB->GetOne("SELECT phonenumber FROM {$vgDBPrefix}userphones WHERE id = ? and username = ?",
                array($id, $this->user->getName(), vPHONE_VERIFIED));
        if($number == false)
            throw new Exception("No such record in userphones.");
        return $number;
    }
    /**
     * Get new code that will be user for phone confirmation via SMS
     * 
     * @global $vgConfirmCodeLen Boolean
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
     * @param $id Integer id of a record
     * @param $code supplied code
     * @return true
     */
    public function verifyCode($id, $code)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $yesterday = vfDate( time() - 24*60*60 );
        $number = $vgDB->GetOne("SELECT phonenumber FROM {$vgDBPrefix}userphones WHERE id = ? AND username = ? AND confirmcode = ? AND confirmsent > ? AND status = ?",
                array($id, $this->user->getName(), $code, $yesterday, vPHONE_SENT_CODE));
        if(! $number)
            throw new Exception("Invalid confirmation code.");
        $c = $vgDB->GetOne("SELECT count(id) FROM {$vgDBPrefix}userphones WHERE status=? AND phonenumber = ?",
                array(vPHONE_VERIFIED, $number));
        if($c)
        {
            throw new Exception("That phone has already been verified by other user.");
        }
        $vgDB->Execute("UPDATE {$vgDBPrefix}userphones SET status = ?, confirmsent = NULL, confirmcode = NULL WHERE id = ?",
                array(vPHONE_VERIFIED, $id));
        return true;
    }
    /**
     * Mark phone as deleted.
     *
     * @param $id Integer record id
     * @return Boolean true
     */
    public function deletePhone($id)
    {
        global $vgDB, $vgDBPrefix;
        $c = $vgDB->GetOne("SELECT count(id) FROM {$vgDBPrefix}userphones WHERE username = ? AND id = ?",
                array($this->user->getName(), $id));
        if(! $c)
            throw new Exception("Invalid code or it has expired.");
        $vgDB->Execute("UPDATE {$vgDBPrefix}userphones SET status = ?, confirmsent = NULL, confirmcode = NULL WHERE id = ?",
                array(vPHONE_DELETED, $id));
        return true;
    }
    /**
     * Resolve a username from given phone number.
     *
     * @param $phone String telephone number
     * @return String username, or Boolean false if it does not exist.
     */
    static function getNameFromPhone($phone)
    {
        global $vgDB, $vgDBPrefix;
        return $vgDB->GetOne("SELECT username FROM {$vgDBPrefix}userphones WHERE phonenumber = ? AND status >= ?",
            array($phone, vPHONE_VERIFIED));
    }
    /**
     * Add a phone number to the user and
     * automatically mark this phone number as verified.
     * 
     * @param $username String
     * @param $phone String
     */
    static function addVerifiedPhone($username, $phone)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $vgDB->Execute("INSERT INTO {$vgDBPrefix}userphones (username, phonenumber, status, dateadded) VALUES (?,?,?,?)",
                array($username, $phone, vPHONE_VERIFIED, $now));
    }
}

