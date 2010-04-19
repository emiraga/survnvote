<?php
/**
* This package contains all value objects. 
* Basically, it contains Call,Page,Survey,Choice,Presentation and SurveyRecord
* @package ValueObject of survey
*/

/**
 * An value object of a choice which follows PHP MVC suggestion. 
 * @author Bai Qifeng
 * @version 1.0
 * @copyright Free copy.
 */
 class ChoiceVO
 {
 	 private $choiceID;
 	 private $surveyID;
 	 private $choice;
 	 private $receiver;
 	 private $SMS;
 	 private $vote;
 	 private $points;
	 
	 /**
	 * Get the choice content of this choice
	 * @return string choice content of this coice
	 */
 	 public function getChoice()
 	 {
 	 	 return $this->choice;
 	 }
 	 /**
	 * Get the choice ID of this choice
	 * @return int the ID of this choice
	 */
 	 public function getChoiceID()
  	 {
  	 	 return $this->choiceID;
  	 }
	 /**
	 * Get the survey ID which contains this choice
	 * @return int the ID of a survey which contains this choice
	 * @internal it is used as a private method
	 */
  	 public function getSurveyID()
  	 { 
  	 	 return $this->surveyID;
  	  }
	 /**
	 * Get the SMS content for this choice
	 * The last two digitals of the receiver.
	 * @return string SMS content which indicates this choice
	 * EE represents error
	 */
     public function getSMS()
  	 {
  		return $this->SMS;
  	  }
	 /**
	 * Get the receiving telephone number for this choice
	 * @return string the receiving telephone number for this choice
	 */
  	  public function getReceiver()
  	 {
  	 	 return $this->receiver;
  	  }
	 /**
	 * Get how many votes this choice got
	 * @return int $vote the number of votes
	 * @todo this functionality has not been finished
	 */
      public function getVote()
  	 {
  	 	 return $this->vote;
  	  }
 	 /**
	 * Get how many points this choice has
	 * @return int $points the number of pointss
	 */
      public function getPoints()
  	 {
  	 	 return $this->points;
  	  }
  	  /**
  	   * Set choice ID
  	   * @param integer $choiceID
  	   */
  	  public function setChoiceID($choiceID)
  	  {
  	  	$this->choiceID = $choiceID;
  	  }
  	 /**
	 * Set choice content of this choice
	 * @param string $choice
	 */
  	  public function setChoice($choice)
 	 {
 	 	 $this->choice = $choice;
 	 }
 	 /**
	 * Set survey ID which includes this choice
	 * @param int $surveyID
	 */
  	 public function setSurveyID($surveyID)
  	 {
  	 	 $this->surveyID = $surveyID;
  	  }
	 /**
	 * Set receiving telepone number for this choice
	 * @param string $receiver
	 */
  	  public function setReceiver($receiver)
  	 {
  	 	 $this->receiver = $receiver;
  	  }
  	 /**
	 * Set SMS for this choice
	 * @param string $SMS
	 */
  	  public function setSMS($SMS)
  	  {
  	  	$this->SMS = $SMS;
  	  }
      /**
	 * Set vote for this choice
	 * @param int $vote
	 */
  	 public function setVote($vote)
  	 {
  	 	 $this->vote = $vote;
  	  }
       /**
	 * Set points for this choice
	 * @param int $points
	 */
  	 public function setPoints($points)
  	 {
  	 	 $this->points = $points;
  	  }
 }
 /**
  * Value Object of Presentation
  *
  */
 class PresentationVO
 {
 	private $surveyID;
 	private $presentationID=1;
 	private $presentation;
 	private $active = 0;
 	private $mark = 0;
 	/**
 	 * set Survey ID of presentation
 	 *
 	 * @param integer $surveyID
 	 */
 	public function setSurveyID($surveyID)
 	{
 		$this->surveyID = $surveyID;
 	}
 	/**
 	 * set presentation ID
 	 * 
 	 * @param integer $presentationID
 	 */
 	public function setPresentationID($presentationID)
 	{
 		$this->presentationID = $presentationID;
 	}
 	/**
 	 * set presentation
 	 * @param string $presentation
 	 */
 	public function setPresentation($presentation)
 	{
 		$this->presentation = $presentation;
 	}
 	/**
 	 * set whether the presentation is active
 	 *
 	 * @param bool $active
 	 */
 	public function setActive($active)
 	{
 		$this->active = $active;
 	}
 	/**
 	 * set how many points the presentation gets
 	 * @param integer $mark
 	 */
 	public function setMark($mark)
 	{
 		$this->mark = $mark;
 	}
 	/**
 	 * @return integer $surveyID
 	 */
  	public function getSurveyID()
 	{
 		return $this->surveyID;
 	}
 	/**
 	 * get ID of presentation
 	 *
 	 * @return integer $presentationID
 	 */
 	public function getPresentationID()
 	{
 		return $this->presentationID;
 	}
 	/**
 	 * get the presentation content
 	 *
 	 * @return string $presentation
 	 */
 	public function getPresentation()
 	{
 		return $this->presentation;
 	}
 	/**
 	 * get whether the presentation is acitved
 	 * 
 	 * @return bool $active
 	 */
 	public function getActive()
 	{
 		if ($this->active)
		   		return '1';
			else
				return '0';
 	}
 	
 	/**
 	 * get how many marks this presentation gets
 	 *
 	 * @return integer marks
 	 */
 	public function getMark()
 	{
 		return $this->mark;
 	}
 	
 }

?>