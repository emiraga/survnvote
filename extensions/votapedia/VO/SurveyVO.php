<?php
if (!defined('MEDIAWIKI')) die();

/**
 * This package contains all value objects.
 * Basically, it contains Call,Page,Survey,Choice,Presentation and SurveyRecord
 * @package ValueObject of survey
 */
global $vpPath;
require_once("$vpPath/ChoiceVO.php");

/**
 * An value object of a survey.
 * @author Bai Qifeng
 * @version 2.0
 */
class SurveyVO
{
	private $pageID;
	private $surveyID;
	private $question;
	private $answer = 0;
	private $points = 0;
	private $choices = array();
	private $presentations = array();
	//private $isUpdated = false;
	private $votesAllowed=1;

	//Reduntant info from PageVO
	private $invalidAllowed = 1;
	private $surveyType = 1;

	/**
	 * Set pageID
	 * @param $id
	 */
	function setPageID($id)
	{
		$this->pageID = $id;
	}
	/**
	 * Set an ID of this survey
	 * @param $id
	 */
	function setSurveyID($id)
	{
		$this->surveyID = $id;
	}
	/**
	 * Set question of this survey
	 * @param $question
	 */
	function setQuestion($question)
	{
		$question = trim($question);
		if ($question)
			$this->question = $question;
		else
			throw new SurveyException("SurveyVO: Question cannot be a empty!",102);
	}
	/**
	 * set the answer of survey
	 * @param $answer
	 */
	function setAnswer($answer)
	{
		if (ereg("^[0-9]{1,}$", $answer))
		{
			$this->answer = $answer;
		}
		else
		{
			throw new SurveyException("SurveyVO: Answer of a question must be an Integer!",101);
		}
	}
	/**
	 * Set points of the question/survey
	 *
	 * @param $points
	 */
	function setPoints($points)
	{
		if (ereg("^[0-9]{1,}$", $points))
		{
			$this->points = $points;
		}
		else
			throw new SurveyException("SurveyVO: Points of a question must be an Integer!",101);
	}
	/**
	 * Set mulit choices of this survey
	 * @param $choices
	 */
	function setChoices(array $choices)
	{
		$this->choices = $choices;
	}
	/**
	 * Generate choices from the array of strings
	 * 
	 * @param $values array of strings
	 */
	function generateChoices(array $values)
	{
		$choices = array();
		foreach($values as $value)
		{
			$value = trim($value);
			if(strlen($value)<1)
				continue;
			$choice = new ChoiceVO();
			$choice->setSurveyID( $this->getSurveyID() );
			$choice->setChoice($value);
			$choice->setVote(0);
			$choice->setPoints(0);
			$choices[] = $choice;
		}
		$this->setChoices($choices);
	}
	/**
	 * Set multiple presentations of this survey
	 * 
	 * @param $presentations
	 */
	function setPresentations(array $presentations)
	{
		$this->presentations = $presentations;
	}
	/**
	 * Set type of Survey
	 * 
	 * @param $surveyType
	 */
	function setType($surveyType)
	{
		$this->surveyType = $surveyType;
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
	 * Set number of votes allowed per one user
	 * 
	 * @param $votesAllowed
	 */
	function setVotesAllowed($votesAllowed)
	{
		$this->votesAllowed = $votesAllowed;
	}
	/**
	 * get page ID
	 * @return Integer $pageID
	 */
	function getPageID()
	{
		return $this->pageID;
	}
	/**
	 * get survey ID of this survey
	 * @return integer ID of the survey which contains this choice
	 */
	function getSurveyID()
	{
		return $this->surveyID;
	}
	/**
	 * get question of this survey
	 * @return string quesion of this survey
	 */
	function getQuestion()
	{
		return $this->question;
	}
	/**
	 * Get the answer of survey
	 *
	 * @return integer $answer
	 */
	function getAnswer()
	{
		return $this->answer;
	}
	/**
	 * get the points of this survey
	 *
	 * @return integer $points
	 */
	function getPoints()
	{
		return $this->points;
	}

	/**
	 * @return integer $type Type of survey
	 */
	function getType()
	{
		return $this->surveyType;
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
	 * get mulit choices in this survey
	 * @return array $choices
	 */
	function getChoices()
	{
		return $this->choices;
	}
	/**
	 * get the number of choices in this survey
	 * @return integer the number of choices included in this survey
	 */
	function getNumOfChoices()
	{
		return count($this->choices);
	}
	/**
	 * get one choice in this survey based on ID of this choice
	 * @param $i id of the choice which want to be retrieved
	 * @return ChoiceVO a choice
	 */
	function getChoiceByNum($i)
	{
		return ($this->choices[$i]);
	}
	/**
	 * @param $receiver telephone number of the receiver
	 * @return integer choiceID
	 */
	function getChoiceIDByReceiver($receiver)
	{
		foreach($this->choices as $choice )
		{
			if ($choice->getReceiver() == $receiver)
				return $choice->getChoiceID();
		}
		return null;
	}
	/**
	 * get multi presentations in this survey
	 * @return array a array of presentations in this survey
	 */
	function getPresentations()
	{
		return $this->presentations;
	}
	/**
	 * get the number of presentations in this survey
	 * @return integer the number of presentations included in this survey
	 */
	function getNumOfPresentations()
	{
		return count($this->presentations);
	}
	/**
	 * get one choice in this survey based on ID of this choice
	 * @param $i id of the choice which want to be retrieved
	 * @return PresentationVO a presentation
	 */
	function getPresentationByNum($i)
	{
		return ($this->presentations[$i]);
	}
	/**
	 * @return integer number of votes allowed
	 */
	function getVotesAllowed()
	{
		return $this->votesAllowed;
	}
	/**
	 * Get the current active presentation
	 *
	 * @return PresentationVO $presentation
	 */
	function getActivePresentationID()
	{
		foreach($this->presentations as $presentation)
		{
			if ($presentation->getActive() == '1' )
			return  $presentation->getPresentationID();
		}
		return 0;
	}

	/*
	 *  Any Set methods are called, will set up $isUpdated = true
	 *  updateSurvey method checks $isUpdated whether need to update this survey.
	 *  Usually the function is used internal, such as retrieve a survey from database
	 *  ATTENTION: It does not matter with whether Choices is updated
	 */
	/*function resetStatus()
	{
		$this->isUpdated = false;
	}*/

	/**
	 * Convert to XML
	 */
	function toXML()
	{
		 
		$xml="<survey>";
		$xml=$xml."<id>".$this->surveyID."</id>";
		$xml=$xml."<question>".str_ireplace('+',' ',urldecode($this->question))."</question>";
		$xml=$xml."<votesAllowed>".$this->votesAllowed."</votesAllowed>";

		$xml=$xml."<choices>";
		$votes  = 0;
		foreach($this->choices as $choice)
		$votes +=$choice->getVote();

		foreach($this->choices as $choice)
		{
			$xml=$xml."<choice>";

			$xml=$xml."<value>".str_ireplace('+',' ',urldecode(htmlspecialchars($choice->getChoice())))."</value>";
			/*
			 if ($choice->getSMS()=="none" || is_null($choice->getSMS()))
			 $xml=$xml."<sms></sms>";
			 else
			 $xml=$xml."<sms>$choice->getSMS()</sms>";
			 */
			$xml=$xml."<receiver>".$choice->getReceiver()."</receiver>";
			if ($votes == 0)
			$xml=$xml."<vote>".$choice->getVote()."</vote>";
			else
			$xml=$xml."<vote>".round($choice->getVote()/$votes,2)."</vote>";
			$xml=$xml."</choice>";
		}
		$xml=$xml."</choices>";
		$xml=$xml."</survey>";
		return $xml;
	}
}

?>