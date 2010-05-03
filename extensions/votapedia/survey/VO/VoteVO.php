<?php
/**
 * Vote value object
 * @package ValueObject of survey
 *
 */
require_once("./survey/error.php");

class VoteVO
{
	private $surveyID;
	private $choiceID;
	private $presentationID = 1;
	private $voterID;
	private $voteDate;
	private $voteType;
	private $invalidAllowed = 0;
	private $votesAllowed = 1;

	/* @deprecated constructor
	function __construct($surveyID,$choiceID,$presentationID,$voterID,$voteDate,$voteType,$invalidAllowed=NULL,$votesAllowed=NULL)
	{
		$this->surveyID = $surveyID;
		$this->choiceID = $choiceID;
		$this->presentationID = $presentationID;
		$this->voterID = $voterID;
		$this->voteDate = $voteDate;
		$this->voteType = $voteType;
		$this->invalidAllowed = (is_null($invalidAllowed)? 0:$invalidAllowed);
		$this->votesAllowed=(is_null($votesAllowed)? 1:$votesAllowed);
	} */

	/**
	 * @return integer survey ID
	 */
	public function getSurveyID()
	{
		return $this->surveyID;
	}
	/**
	 * @return integer choice ID
	 */
	public function getChoiceID()
	{
		return $this->choiceID;
	}
	/**
	 * Return the presentation ID
	 * 
	 * @return integer presentation ID
	 */
	public function getPresentationID()
	{
		return $this->presentationID;
	}
	/**
	 * @return integer voter ID
	 */
	public function getVoterID()
	{
		return $this->voterID;
	}
	/**
	 * @return DateTime vot date
	 */
	public function getVoteDate()
	{
		return $this->voteDate;
	}
	/**
	 * @return string vote type
	 */
	public function getVoteType()
	{
		return $this->voteType;
	}
	/**
	 * @return boolean Are invalid votes allowed
	 */
	public function getInvalidAllowed()
	{
		return $this->invalidAllowed;
	}
	/**
	 * @return integer number of votes allowed per user
	 */
	public function getVotesAllowed()
	{
		return $this->votesAllowed;
	}

	/**
	 * @param $surveyid
	 */
	public function setSurveyID($surveyid)
	{
		$this->surveyID = $surveyid;
	}
	/**
	 *
	 * @param $choiceid
	 */
	public function setChoiceID($choiceid)
	{
		$this->choiceID = $choiceid;
	}
	/**
	 * 
	 * @param $presentationid
	 */
	public function setPresentationID ($presentationid)
	{
		$this->presentationID = $presentationid;
	}
	/**
	 * 
	 * @param $voterid
	 */
	public function setVoterID($voterid)
	{
		$this->voterID = $voterid;
	}
	/**
	 * 
	 * @param $votedate
	 */
	public function setVoteDate($votedate)
	{
		$this->voteDate = $votedate;
	}
	/**
	 * 
	 * @param $votetype usually voters vote by CAL,WEB or SMS
	 */
	public function setVoteType($votetype)
	{
		if( in_array($votetype, array( 'WEB', 'CALL', 'SMS' )) )
			$this->voteType = $votetype;
		else
			throw new SurveyException("Invalid vote type", 400);
	}
	/**
	 * 
	 * @param $allowinvalid
	 */
	public function setInvalidAllowed($allowinvalid)
	{
		$this->invalidAllowed = $allowinvalid;
	}
	/**
	 * Set number of allowed votes per user
	 * @param $numvotes
	 */
	public function setVotesAllowed($numvotes)
	{
		$this->votesAllowed = $numvotes;
	}
}

?>