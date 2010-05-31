<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/**
 *  A value object of a call
 */
class CallVO
{
	private $callid;
	private $from;
	private $to;
	private $date;
	private $errorCode;
	
	/**
	 * Set call ID
	 * @param $callid
	 */
	function setCallID($callid)
	{
		$this->callid = $callid;
	}
	/**
	 * Set from
	 * @param $from
	 */
	function setFrom($from)
	{
		$this->from = $from;
	}
	/**
	 * Set to
	 * @param $to
	 */
	function setTo($to)
	{
		$this->to = $to;
	}
	/**
	 * Set date
	 * @param $date
	 */
	function setDate($date)
	{
		$this->date = $date;
	}
	/**
	 * Set error code
	 * @param $errorCode
	 */
	function setErrorCode($errorCode)
	{
		$this->errorCode = $errorCode;
	}
	/**
	 * @return callid
	 */
	public function getCallID()
	{
		return $this->callid;
	}
	/**
	 * @return from which phone call originates
	 */
	public function getFrom()
	{ 
		return $this->from; 
	}
	/**
	 * @return to which number is SMS directed to
	 */
	public function getTo() 
	{ 
		return $this->to; 
	}
	/**
	 * @return date of the call
	 */
	public function getDate() 
	{ 
		return $this->date; 
	}
	/**
	 * @return get the error code
	 */
	public function getErrorCode() 
	{ 
		return $this->errorCode;
	}
}

?>