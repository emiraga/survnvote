<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/**
 * A value object of a voting record of surveys which follows PHP MVC suggestion.
 * @author Bai Qifeng
 * @version 1.0
 */
class SurveyRecordVO
{
	private $surveyID;
	private $choiceID;
	private $presentationID = 1;
	private $voterID;
	private $voteDate;
	private $voteType;

	/**
	 * Construct an object survey record VO
	 * 
	 */
	public function __construct()
	{
		$this->voteDate = vfDate();
	}
	/**
	 * Set SurveyID
	 * 
	 * @param $surveyID that can be retrieved from SurveyVO->getSurveyID()
	 */
	public function setSurveyID($surveyID)
	{
		$this->surveyID = $surveyID;
	}
	/**
	 * Set Which choice in this survey the voter chooses
	 * 
	 * @param $choiceID
	 */
	public function setChoiceID($choiceID)
	{
		$this->choiceID = $choiceID;
	}
	/**
	 * Set Which presentation in this survey the voter chooses
	 * 
	 * @param $presentationID
	 */
	public function setPresentationID($presentationID)
	{
		$this->presentationID = $presentationID;
	}
	/**
	 * Set who is voting
	 * 
	 * @param $voterID
	 */
	public function setVoterID($voterID)
	{
		$this->voterID = $voterID;
	}
	/**
	 * Set When the voter voted
	 * 
	 * @param $voteDate
	 */
	public function setVoteDate($voteDate)
	{
		$this->voteDate = $voteDate;
	}
	/**
	 * Set Which way the voter voted by
	 * 
	 * @param $voteType usually voters vote by CALL, WEB or SMS
	 */
	public function setVoteType($voteType)
	{
		if( in_array($voteType, array( 'WEB', 'CALL', 'SMS' )) )
			$this->voteType = $voteType;
		else
			throw new SurveyException( "Invalid vote type", 400 );
	}
	/**
	 * Get the Survey ID
	 * 
	 * @return Integer $surveyID
	 */
	public function getSurveyID()
	{
		return $this->surveyID;
	}
	/**
	 * Get Which choice in this survey the voter chose
	 * 
	 * @return Integer $choiceID
	 */
	public function getChoiceID()
	{
		return $this->choiceID;
	}
	/**
	 * Get Which choice in this survey the voter chose
	 * 
	 * @return Integer $presentationID
	 */
	public function getPresentationID()
	{
		return $this->presentationID;
	}
	 
	/**
	 * Get who voted
	 * 
	 * @return String $voteID
	 */
	public function getVoterID()
	{
		return $this->voterID;
	}
	/**
	 * Get When the voter voted
	 * 
	 * @return Datetime $voteDate
	 */
	public function getVoteDate()
	{
		return $this->voteDate;
	}
	/**
	 * Get Which way the voter voted by
	 * 
	 * @return String $voteType usually voters vote by CALL ,WEB or SMS
	 */
	public function getVoteType()
	{
		return $this->voteType;
	}
}

?>