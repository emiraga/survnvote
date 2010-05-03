<?php
if (!defined('MEDIAWIKI')) die();

/**
* This package contains all value objects. 
* Basically, it contains Call,Page,Survey,Choice,Presentation and SurveyRecord
* @package ValueObject of survey
*/

/**
 * An value object of a choice which follows PHP MVC suggestion. 
 * @author Bai Qifeng
 * @version 1.0
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
	 * @return integer the ID of this choice
	 */
 	 public function getChoiceID()
  	 {
  	 	 return $this->choiceID;
  	 }
	 /**
	 * Get the survey ID which contains this choice
	 * @return integer the ID of a survey which contains this choice
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
	 * 
	 * @return integer $vote the number of votes
	 */
     public function getVote()
  	 {
  	 	 return $this->vote;
  	 }
 	 /**
	 * Get how many points this choice has
	 * @return integer $points the number of pointss
	 */
      public function getPoints()
  	 {
  	 	 return $this->points;
  	  }
  	  /**
  	   * Set choice ID
  	   * @param $choiceID
  	   */
  	  public function setChoiceID($choiceID)
  	  {
  	  	$this->choiceID = $choiceID;
  	  }
  	 /**
	 * Set choice content of this choice
	 * @param $choice
	 */
  	  public function setChoice($choice)
 	 {
 	 	 $this->choice = $choice;
 	 }
 	 /**
	 * Set survey ID which includes this choice
	 * @param $surveyID
	 */
  	 public function setSurveyID($surveyID)
  	 {
  	 	 $this->surveyID = $surveyID;
  	  }
	 /**
	 * Set receiving telepone number for this choice
	 * @param $receiver
	 */
  	  public function setReceiver($receiver)
  	 {
  	 	 $this->receiver = $receiver;
  	  }
  	 /**
	 * Set SMS for this choice
	 * @param $SMS
	 */
  	  public function setSMS($SMS)
  	  {
  	  	$this->SMS = $SMS;
  	  }
      /**
	 * Set vote for this choice
	 * 
	 * @param $vote
	 */
  	 public function setVote($vote)
  	 {
  	 	 $this->vote = $vote;
  	  }
       /**
	 * Set points for this choice
	 * @param $points
	 */
  	 public function setPoints($points)
  	 {
  	 	 $this->points = $points;
  	  }
 }

?>