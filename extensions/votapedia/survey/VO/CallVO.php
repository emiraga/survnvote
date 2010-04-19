<?php
/**
* This package contains all value objects. 
* Basically, it contains Call,Page,Survey,Choice,Presentation and SurveyRecord
* @package ValueObject of survey
*/
/**
 *  A value object of a call
 */
class CallVO
{
	public $id;
	public $from;
	public $to;
	public $dt;
	public $errorCode;
	
	function __construct($id,$from,$to,$dt,$errorCode=NULL)
	{
		$this->id = $id;
		$this->from = $from;
		$this->to = $to;
		$this->dt = $dt;
		$this->errorCode = (is_null($errorCode)? 0:$errorCode);
	}
	
}

class SMSVO
{
	public $id;
	public $from;
	public $text;
	public $dt;
	public $errorCode;
	
	function __construct($id,$from,$text,$dt,$errorCode=NULL)
	{
		$this->id = $id;
		$this->from = $from;
		$this->text = $text;
		$this->dt = $dt;
		$this->errorCode = (is_null($errorCode)? 0:$errorCode);
	}
	
}

class VoteVO
{
     public $surveyID;
   	 public $choiceID;
     public $presentationID = 0;
     public $voterID;
     public $voteDate;
     public $voteType;
     public $invalidAllowed;
     public $votesAllowed=1;
     
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
	}
}

?>