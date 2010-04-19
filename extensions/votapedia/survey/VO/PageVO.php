<?php
/**
* This package contains all value objects. 
* Basically, it contains Call,Page,Survey,Choice,Presentation and SurveyRecord  
* @package ValueObject of survey
*/
require_once("SurveyVO.php");
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
     private $author;
     
 	 private $startTime;
 	 private $endTime;
 	 private $duration;
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
 	 private $actived;


 	 private $surveys = array();
 	 
 	 private $isUpdated = false;
	 
	 function __construct()
	 {
	 	$this->duration = 10;

		// IMPORTANT: HANDLING TIME
		//date_default_timezone_set('Australia/Melbourne');
		$this->startTime  = "2000-01-01 00:00:00"; 
		$this->endTime = $this->renewEndTime();;
		$this->author = "CSIRO";
		$this->isUpdated = true;
	 }


	 /**
	 * Set an ID of this survey
	 * @param integer $id
	 */
 	 function setPageID($id)
	 {
	    $this->pageID = $id;
	 }
	 /**
	 * Set question of this survey
	 * @param string $question
	 */
	 function setTitle($title)
 	 {
          try {
                 $this->isUpdated = true;
                 if (trim($title)!="")
		   			  $this->title = $title;
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
	 * Set start time of this survey, must match the required date format
	 * 
	 * @param datatime $startTime yyyy-mm-dd hh:mm:ss
	 */
 	 function setStartTime($startTime)
 	 {
		 $this->startTime = $this->validateDate($startTime);
		 $this->endTime = $this->renewEndTime();
		 //echo "test endtime:".$this->endTime;
		 $this->isUpdated = true;
	 }
 	 /**
	 * Set end time of this survey
	 * @param datatime $endTime
	 */
	 function setEndTime($endTime)
 	 {
 	 	 $this->endTime = $endTime;
 	 	 $this->isUpdated = true;
 	 }
 	 /**
	 * Set duration of this survey, must be integer
	 * @param integer $duration
	 */
	 function setDuration($duration)
 	 {
		$this->duration = $duration;
		$this->endTime = $this->renewEndTime();
		$this->isUpdated = true;
 	 }
	 
	 /**
	 * get created time of this survey
	 * @param datatime created time of this survey
	 */
	 function setCreateTime($createTime)
	 {
	 	$this->createTime = $createTime;
	 	$this->isUpdated = true;
 	  }
 	 /**
	 * Set author of this survey
	 * @param string $author
	 */
	 function setAuthor($author)
 	 {
 	       $this->author = trim($author);
               $this->isUpdated = true;
 	 }
 	 
         /**
	 * Set phone of this survey , which is used to activate/deactivate survey
	 * @param string $phone
	 */
	 function setPhone($phone)
 	 {
 	       $this->phone = trim($phone);
               $this->isUpdated = true;
 	 }
 	  /**
 	  * set whether the suvey allows invalid calls (-1) to vote
 	  * @param boolean $invalidAllow
 	  */
 	  function setInvalidAllowed($invalidAllowed)
 	  {
             $this->invalidAllowed = $invalidAllowed;
          }
          /**
 	  * set whether the suvey needs to sms back to the voters
 	  * @param boolean $smsRequired
 	  */
 	  function setSMSRequired($smsRequired)
 	  {
             $this->smsRequired = $smsRequired;
          }
          
          /**
 	  * set whether the suvey allow telephone/sms voting
 	  * @param boolean $teleVoteAllowed
 	  */
 	  function setTeleVoteAllowed($teleVoteAllowed)
 	  {
             $this->teleVoteAllowed = $teleVoteAllowed;
      }
          /**
          *  set whether the suvey allow anonymous voting
          *  @param boolean $allowAnonymousVotes
          */
          function setAnonymousAllowed($allowAnonymousVotes)
          {
            $this->anonymousAllowed  = $allowAnonymousVotes;
          }
          /**
           * set whether allow graph is shown in voting
           * @param bool $showGraph
           */
          function setShowGraph($showGraph)
          {
          	$this->showGraph = $showGraph;
          }
     /**
	 * Set status of this survey. Does not get from Database directly
	 * but caculated by startTime, endTime with current Time
	 * @param boolean $activated
	 * @deprecated would never be used
	 */
	 function setActivated($activated)
 	 {
 	 	 $this->activated = $activated ;
 	 } 
	 /**
	  * Set Top n presentations would be displayed
	  * @param Integer $top
	  */
 	 function setDisplayTop($top)
 	 {
 	 	$this->displayTop = $top;
 	 }
 	 /**
	  * Set how many times of multi-votes
	  * @param Integer $times
	  */
 	 function setVotesAllowed($times)
 	 {
 	 	$this->votesAllowed = $times;
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
	 * Set mulit choices of this survey
	 * @param array $choices
	 */
 	 function setSurveys(array $surveys)
 	 {
 	 	 $this->surveys = $surveys;
 	 }
	 function setSubtractWrong($subtractWrong)
	 {
	 	$this->subtractWrong = $subtractWrong;
	 }
	 /**
	 * get survey ID of this survey
	 * @return integer ID of the survey which contains this choice
	 */
 	 function getPageID()
 	 {
 	 	 return $this->pageID;
 	 }
	 /**
	 * get question of this survey
	 * @return string quesion of this survey
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
	 * @return string author of this survey
	 */
	 function getAuthor()
 	 {
 	 	 return $this->author;
 	 }
 	 /**
	 * get phone of this survey
	 * @return string phone of this survey
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
         function isSubtractWrong()
         {
         	if($this->subtractWrong)
         		return '1';
         	else
         		return '0';
         }
         
         /**
          * @return integer $type Type of survey
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
	 * @todo functionality has not been finished yet
	 */
	 function isActivated()
 	 {
 	 	 return $this->activated;
 	 }
 	 /**
	 * check wether this survey is updated
	 * @return boolean
	 */
 	 function isUpdated()
 	 {
           return $this->isUpdated;
         }
         
	 /**
	 * get mulit choices in this survey
	 * @return array a array of choices in this survey
	 */ 	 
 	 function getSurveys()
 	 {
 	 	 return $this->surveys;
 	 }
	 /**
	 * get the number of choices in this survey
	 * @return integer the number of choices included in this survey
	 */
	 function getNumOfSurveys()
	 {
	  	return count($this->surveys);
	 }
	 /**
	 * get one survey by its surveyID
	 * @param int $id id of the survey
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
	  /*
	  * Validate whether matchs the requried data format
	  * @return return date if true, tigger a error if false
	  */
	  function validateDate($date)
 	  {
            try{
                  if (ereg("^[0-9]{4}\-[0|1][0-9]\-[0-3][0-9]\040[0-9]{2}\:[0-9]{2}:[0-9]{2}", $date))
					 return $date;
				  else 
		 			throw new SurveyException("Date/Time must follow yyyy-mm-dd hh:mm:ss format!",100);
                }
           catch (SurveyException $e)
           {
              $e->showError();
              return true;
           }
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

	   private function renewEndTime()
	   {
        $d1=substr($this->startTime,17,2); //Second
		$d2=substr($this->startTime,14,2); //Minute
		$d3=substr($this->startTime,11,2); //Hour
		$d4=substr($this->startTime,8,2);  //Day
		$d5=substr($this->startTime,5,2); //Month
		$d6=substr($this->startTime,0,4); //Year
		$start_S=mktime("$d3","$d2","$d1","$d5","$d4","$d6");
		$end_S = $start_S + $this->duration*60;
		return date("Y-m-d H:i:s",$end_S);
	    }

 }
?>