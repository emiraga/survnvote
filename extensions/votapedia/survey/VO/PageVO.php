<?php
if (!defined('MEDIAWIKI')) die();

/**
 * This package contains all value objects.
 * Basically, it contains Call,Page,Survey,Choice,Presentation and SurveyRecord
 * @package ValueObject of survey
 */
require_once("$vpPath/SurveyVO.php");

/**
 * Value Object of Page which contains survey array etc.
 *
 * Page 1-->1..* survey
 * survey 1-->1..* choice
 * survey 1-->0..* presentation.
 */
class PageVO
{
	private $pageID;
	private $title;
	private $phone='000';
	private $author = "UnknownUser";
	private $startTime = "2000-01-01 00:00:00";
	private $endTime;
	private $duration = 60;
	private $createTime;
	private $invalidAllowed = 1;
	private $smsRequired = 0;
	private $teleVoteAllowed = 1;
	private $anonymousAllowed = 1;
	private $showGraph = 1;
	private $surveyType = 1;
	private $displayTop = 0;
	private $votesAllowed = 1;
	private $subtractWrong = 0;
	private $surveys = array();
	//private $isUpdated = false;

	function __construct()
	{
		$this->endTime = $this->renewEndTime();
		$this->setCreateTime(date("Y-m-d H:i:s"));
	}
	/**
	 * Set an ID of this survey
	 * @param $id
	 */
	function setPageID($id)
	{
		$this->pageID = $id;
	}
	
	/**
	 * Set question of this survey
	 * 
	 * @param $title
	 */
	function setTitle($title)
	{
		$title = trim($title);
		if ($title)
			$this->title = $title;
		else
			throw new SurveyException("Question cannot be a empty!",102);
	}
	/**
	 * Set start time of this survey, must match the required date format
	 *
	 * @param $startTime yyyy-mm-dd hh:mm:ss
	 */
	function setStartTime($startTime)
	{
		$this->startTime = $this->validateDate($startTime);
		$this->endTime = $this->renewEndTime();
	}
	/**
	 * Set end time of this survey
	 * @param $endTime
	 */
	function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		$this->duration = $this->renewDuration();
	}
	/**
	 * Set duration of this survey, must be Integer
	 * @param $duration
	 */
	function setDuration($duration)
	{
		$this->duration = $duration;
		$this->endTime = $this->renewEndTime();
	}

	/**
	 * get created time of this survey
	 * @param $createTime create time of this survey
	 */
	function setCreateTime($createTime)
	{
		$this->createTime = $createTime;
	}
	/**
	 * Set author of this survey
	 * @param $author
	 */
	function setAuthor($author)
	{
		$this->author = trim($author);
	}

	/**
	 * Set phone of this survey , which is used to activate/deactivate survey
	 * @param $phone
	 */
	function setPhone($phone)
	{
		$this->phone = trim($phone);
	}
	/**
	 * set whether the suvey allows invalid calls (-1) to vote
	 * @param $invalidAllowed
	 */
	function setInvalidAllowed($invalidAllowed)
	{
		$this->invalidAllowed = $invalidAllowed;
	}
	/**
	 * set whether the suvey needs to sms back to the voters
	 * @param $smsRequired
	 */
	function setSMSRequired($smsRequired)
	{
		$this->smsRequired = $smsRequired;
	}
	/**
	 * set whether the suvey allow telephone/sms voting
	 * @param $teleVoteAllowed
	 */
	function setTeleVoteAllowed($teleVoteAllowed)
	{
		$this->teleVoteAllowed = $teleVoteAllowed;
	}
	/**
	 *  set whether the suvey allow anonymous voting
	 *  @param $allowAnonymousVotes
	 */
	function setAnonymousAllowed($allowAnonymousVotes)
	{
		$this->anonymousAllowed  = $allowAnonymousVotes;
	}
	/**
	 * set whether allow graph is shown in voting
	 * @param $showGraph
	 */
	function setShowGraph($showGraph)
	{
		$this->showGraph = $showGraph;
	}
	/**
	 * Set Top n presentations would be displayed
	 * 
	 * @param top
	 */
	function setDisplayTop($top)
	{
		$this->displayTop = $top;
	}
	/**
	 * Set how many times of multi-votes
	 * @param $times
	 */
	function setVotesAllowed($times)
	{
		$this->votesAllowed = $times;
	}
	/**
	 * set type of Survey
	 * @param $surveyType
	 */
	function setType($surveyType)
	{
		$this->surveyType = $surveyType;
	}
	/**
	 * Set multi choices of this survey
	 * 
	 * @param $surveys
	 */
	function setSurveys(array $surveys)
	{
		$this->surveys = $surveys;
	}
	/**
	 * Subtract wrong answers from points
	 * @param $subtractWrong boolean
	 */
	function setSubtractWrong($subtractWrong)
	{
		$this->subtractWrong = $subtractWrong;
	}
	/**
	 * get survey ID of this survey
	 * @return Integer ID of the survey which contains this choice
	 */
	function getPageID()
	{
		return $this->pageID;
	}
	/**
	 * get question of this survey
	 * @return String quesion of this survey
	 */
	function getTitle()
	{
		return $this->title;
	}
	/**
	 * get starting time of this survey
	 * @return datetime start time of this survey
	 */
	function getStartTime()
	{
		return $this->startTime;
	}
	/**
	 * get finishing time of this survey
	 * @return datetime finishing time of this survey
	 */
	function getEndTime()
	{
		return $this->endTime;
	}
	/**
	 * get duration of this survey
	 * @return int duration of this survey
	 */
	function getDuration()
	{
		return $this->duration;
	}
	/**
	 * get author of this survey
	 * @return String author of this survey
	 */
	function getAuthor()
	{
		return $this->author;
	}
	/**
	 * get phone of this survey
	 * @return String phone of this survey
	 */
	function getPhone()
	{
		return $this->phone;
	}
	/**
	 * get created time of this survey
	 * @return datatime created time of this survey
	 */
	function getCreateTime()
	{
		return $this->createTime;
	}

	/**
	 * Check wether the survey allows invalid calls (-1) to vote
	 * @return allow as 1, forbid as 0
	 *
	 */
	function isInvalidAllowed()
	{
		if ($this->invalidAllowed)
			return '1';
		else
			return '0';
	}
	/**
	 * Check wether the survey needs to sms back to voters
	 * @return allow as 1, forbid as 0
	 *
	 */
	function isSMSRequired()
	{
		if($this->smsRequired)
			return '1';
		else
			return '0';
	}

	/**
	 * Check wether the survey allows telephone/sms voting
	 * 0 for Web voting Only
	 * 1 Both allowed
	 * 2 Telephone Voting only
	 * @return allow as 1, forbid as 0
	 *
	 */
	function getTeleVoteAllowed()
	{
		return $this->teleVoteAllowed;
	}
	/**
	 * Check wether the survey allows anonymouse voting
	 * @return allow as 1, forbid as 0
	 *
	 */
	function isAnonymousAllowed()
	{
		if($this->anonymousAllowed)
			return '1';
		else
			return '0';
	}
	/**
	 * Check wether the survey allows to show graph in voting
	 * @return allow as 1, forbid as 0
	 *
	 */
	function isShowGraph()
	{
		if($this->showGraph)
			return '1';
		else
			return '0';
	}
	/**
	 * Should incorrect answers be subtracted from points
	 * @return boolean
	 */
	function isSubtractWrong()
	{
		if($this->subtractWrong)
			return '1';
		else
			return '0';
	}
	/**
	 * @return Integer $type Type of survey
	 */
	function getType()
	{
		return $this->surveyType;
	}
	/**
	 * Get how many top presentations would be displayed
	 * @return Integer $displayTop
	 */
	function getDisplayTop()
	{
		return $this->displayTop;
	}
	/**
	 * get how many times of multi-votes
	 * @return Integer $times
	 */
	function getVotesAllowed()
	{
		return $this->votesAllowed;
	}
	/**
	 * get status of this survey
	 * @return boolean true is for survey is running, false is for survey stopped
	 */
	/*function isActivated()
	{
		return $this->activated;
	}*/
	/*
	 * check wether this survey is updated
	 * @return boolean
	 */
	/*function isUpdated()
	{
		return $this->isUpdated;
	}*/

	/**
	 * get mulit choices in this survey
	 * @return array a array of choices in this survey
	 */
	function &getSurveys()
	{
		return $this->surveys;
	}
	/**
	 * get the number of choices in this survey
	 * @return Integer the number of choices included in this survey
	 */
	function getNumOfSurveys()
	{
		return count($this->surveys);
	}
	/**
	 * get one survey by its surveyID
	 * @param $id id of the survey
	 * @return SurveyVO $surveyVO
	 */
	function getSurveyBySurveyID($id)
	{
		foreach($this->surveys as $survey)
		{
			if ($survey->getSurveyID()== $id)
			return $survey;
		}
		return null;
	}
	/**
	 * Validate whether matchs the requried data format
	 * @param $date String date
	 * @return return date if true, tigger a error if false
	 */
	function validateDate($date)
	{
		if (ereg("^[0-9]{4}\-[0|1][0-9]\-[0-3][0-9]\040[0-9]{2}\:[0-9]{2}:[0-9]{2}", $date))
			return $date;
		else
			throw new SurveyException("Date/Time must follow yyyy-mm-dd hh:mm:ss format!",100);
	}
	/*
	 *  Any Set methods are called, will set up $isUpdated = true
	 *  updateSurvey method checks $isUpdated whether need to update this survey.
	 *  Usually the function is used internal, such as retrieve a survey from database
	 *  ATTENTION: It does not matter with whether Choices is updated
	 */
	/*private function resetStatus()
	{
		$this->isUpdated = false;
	}*/

	/**
	 * Recompute the end time based in the startTime and duration
	 * @return new value of end time
	 */
	private function renewEndTime()
	{
		$start_S=strtotime($this->startTime);
		$end_S = $start_S + $this->duration*60;
		return date("Y-m-d H:i:s",$end_S);
	}
	/**
	 * Recompute the duration based on startTime and endtime
	 * @return duration of the survey
	 */
	private function renewDuration()
	{
		$start=strtotime($this->startTime);
		$end=strtotime($this->endTime);
		return intval( ($end - $start) / 60 );
	}
}
?>