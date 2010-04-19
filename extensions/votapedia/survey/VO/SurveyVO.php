<?php
/**
* This package contains all value objects. 
* Basically, it contains Call,Page,Survey,Choice,Presentation and SurveyRecord
* @package ValueObject of survey
*/
 //require_once("../error.php");
 require_once("ChoiceVO.php");

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
 	 private $answer=0;
 	 private $points=0;
  	 private $choices = array();
 	 private $presentations = array();
  	 private $isUpdated = false;
  	 private $votesAllowed=1;
  	 
  	 //Reduntant info from PageVO
 	 private $invalidAllowed = 1;
 	 private $surveyType = 1;
	 
	 function __construct()
	 {
	 }
 	 /**
	 * Set pageID
	 * @param integer $id
	 */
 	 function setPageID($id)
	 {
	    $this->pageID = $id;
	 }

	 /**
	 * Set an ID of this survey
	 * @param integer $id
	 */
 	 function setSurveyID($id)
	 {
	    $this->surveyID = $id;
	 }
	 /**
	 * Set question of this survey
	 * @param string $question
	 */
	 function setQuestion($question)
 	 {
          try {
                 $this->isUpdated = true;
                 if (trim($question)!="")
		     $this->question = $question;
		 else
		     throw new SurveyException("Question cannot be a empty!",102);
	    }
	   catch (SurveyException $e)
           {
              $e->showError();
              return true;
           }
 	 }
 	 /**
 	  * set the answer of survey
 	  * @param Integer $answer
 	  */
 	 function setAnswer($answer)
 	 {
 	 	   try{
            if (ereg("^[0-9]{1,}$", $answer))
			{ 
				$this->answer = $answer;
			 }
		    else 
		 	    throw new SurveyException("Answer of a question must be an Integer!",101);
         }
         catch (SurveyException $e)
         {
           $e->showError();
           return true;
         }
 	 	
 	 }
 	 /**
 	  * Set points of the question/survey
 	  *
 	  * @param Integer $points
 	  */
  	 function setPoints($points)
 	 {
 	 	   try{
            if (ereg("^[0-9]{1,}$", $points))
			{ 
				$this->points = $points;
			 }
		    else 
		 	    throw new SurveyException("Points of a question must be an Integer!",101);
         }
         catch (SurveyException $e)
         {
           $e->showError();
           return true;
         }
 	 	
 	 }
 	 
	 /**
	 * Set mulit choices of this survey
	 * @param array $choices
	 */
 	 function setChoices(array $choices)
 	 {
 	 	 $this->choices = $choices;
 	 }
 	 
 	 /**
	 * Set mulit presentation of this survey
	 * @param array $choices
	 */
 	 function setPresentations(array $presentations)
 	 {
 	 	 $this->presentations = $presentations;
 	 }
 	 
 	 /**
	  * set type of Survey
	  * @param integer $surveyType
	  */
 	 function setType($surveyType)
 	 {
 	 	$this->surveyType = $surveyType;
 	 }
  	  /**
 	  * set whether the suvey allows invalid calls (-1) to vote
 	  * @param boolean $invalidAllow
 	  */
 	  function setInvalidAllowed($invalidAllowed)
 	  {
             $this->invalidAllowed = $invalidAllowed;
          }
          
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
	 * @param int $i id of the choice which want to be retrieved
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
	 * get mulit presentations in this survey
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
	 * @param int $i id of the choice which want to be retrieved
	 * @return PresentationVO a presentation
	 */
	 function getPresentationByNum($i)
	 {
	   return ($this->presentations[$i]);
	  }
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
	  		if ($presentation->getActive() == 1 )
	  		   return  $presentation->getPresentationID();
	  	}
	  	return 0;
	  }

	   /**
	   *  Any Set methods are called, will set up $isUpdated = true
	   *  updateSurvey method checks $isUpdated whether need to update this survey.
	   *  Usually the function is used internal, such as retrieve a survey from database
	   *  ATTENTION: It does not matter with whether Choices is updated
	   */
	   function resetStatus()
	   {
  	      $this->isUpdated = false;
       }
       
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