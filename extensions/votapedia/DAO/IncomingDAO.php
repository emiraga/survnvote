<?php
if (!defined('MEDIAWIKI')) die();

/**
 * Abstract class for IncomingCALL and IncomingSMS
 * 
 * 
 * @author Emir Habul
 * @abstract
 */
abstract class IncomingDAO
{
	private $type;
	private $ID;
	private $errorCode;
	/**
	 * Set type of incoming CALL/SMS
	 * 
	 * @param $type
	 */
	protected function setType($type)
	{
		if($type != 'CALL' && $type != 'SMS')
			throw new SurveyException("Invalid type");
		$this->type = $type;
	}
	/**
	 * @return type of phone action
	 */
	public function getType()
	{
		return $type;
	}
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
	 * @abstract
	 * @param $value
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
	 * @param $value error code
	 */
	public function updateError($value)
	{
		global $gvDB, $gvDBPrefix;
		$gvDB->Execute("update {$gvDBPrefix}incomingsms set Errorcode = ? where ID = ?", array($value, $this->getID()));
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
	 * @param $value error code
	 */
	public function updateError($value)
	{
		global $gvDB, $gvDBPrefix;
		$gvDB->Execute("update {$gvDBPrefix}incomingcall set Errorcode = ? where ID = ?", array($value, $this->getID()));
	}
}

?>