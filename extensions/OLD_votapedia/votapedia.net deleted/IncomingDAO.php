<?php
unusedsdfklsdf();
notuse();

if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/**
 * Abstract class for IncomingCALL and IncomingSMS
 *
 *
 * @author Emir Habul
 */
abstract class IncomingDAO
{
    protected $ID;
    protected $errorCode;
    /**
     * @return String type of action
     */
    abstract public function getType();
    /**
     * Set SMS or CALL id in database table
     *
     * @param $callsmsid
     */
    public function setID($callsmsid)
    {
        $this->ID = $callsmsid;
    }
    /**
     * @return SMS or CALL ID
     */
    public function getID()
    {
        return $this->ID;
    }
    /**
     * @return error code
     */
    public function getError()
    {
        return $this->errorCode;
    }
    /**
     * Update value of error
     *
     * @param $value Integer
     */
    abstract public function updateError($value);
}
/**
 *
 * @author Emir Habul
 *
 */
class SmsDAO extends IncomingDAO
{
    /**
     *
     * @param $value Integer error code
     */
    public function updateError($value)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("update {$vgDBPrefix}incomingsms set Errorcode = ? where ID = ?", array($value, $this->getID()));
        $this->errorCode = $value;
    }
    public function getType()
    {
        return 'SMS';
    }
}
/**
 *
 * @author Emir Habul
 */
class CallDAO extends IncomingDAO
{
    /**
     *
     * @param $value Integer error code
     */
    public function updateError($value)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("update {$vgDBPrefix}incomingcall set Errorcode = ? where ID = ?", array($value, $this->getID()));
        $this->errorCode = $value;
    }
    public function getType()
    {
        return 'CALL';
    }
}

/**
 * 
 */
class WebvoteDAO extends IncomingDAO
{
    /**
     *
     * @param $value Integer error code
     */
    public function updateError($value)
    {
        $this->errorCode = $value;
    }
    public function getType()
    {
        return 'WEB';
    }
}

?>