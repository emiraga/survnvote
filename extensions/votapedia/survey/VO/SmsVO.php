<?php
if (!defined('MEDIAWIKI')) die();

/**
 * A value object of SMS.
 * 
 * @author Emir Habul
 * @package ValueObject of survey
 */

/**
 *  A value object of SMS
 */
class SmsVO
{
	private $smsid; 
	private $from;
	private $text;
	private $date;
	private $errorCode;

	/**
	 * Set SMSid
	 * @param $smsid
	 */
	function setSmsID($smsid)
	{
		$this->smsid = $smsid;
	}
	/**
	 * Set the telephone number from where does SMS originate
	 * @param $from
	 */
	function setFrom($from)
	{
		$this->from = $from;
	}
	/**
	 * Set text
	 * @param $text
	 */
	function setText($text)
	{
		$this->text = $text;
	}
	/**
	 * Set Date
	 * @param $date
	 */
	function setDate($date)
	{
		$this->date = $date;
	}
	/**
	 * Set Error code
	 * @param $errorCode
	 */
	function setErrorCode($errorCode)
	{
		$this->errorCode = (is_null($errorCode)? 0:$errorCode);
	}
	/**
	 * @return SmsID
	 */
	function getSmsID()
	{
		return $this->smsid; 
	}
	/**
	 * @return the telephone number from where does SMS originate
	 */
	function getFrom()
	{
		return $this->from;
	}
	/**
	 * @return text
	 */
	function getText()
	{
		return $this->text;
	}
	/**
	 * @return Date
	 */
	function getDate()
	{
		return $this->date;
	}
	/**
	 * @return ErrorCode
	 */
	function getErrorCode()
	{
		return $this->errorCode;
	}
}
?>