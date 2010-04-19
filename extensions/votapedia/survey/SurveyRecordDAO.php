<?php
/**
* SurveyRecordDAO includes functions which can access
* voting records of surveys from database system.
* @package DAO of Survey 
* @author Bai Qifeng
* @version 1.0
*/
class SurveyRecordDAO
{
  /** Check whether the voter is in its first voting
  * @param String $voterID ID of voter,could be wiki usrname, telephone number.
  * @return Boolean true represents it is the first time voting.
  */
  function isFirstVoting($voterID)
  {
     $cn = connectDatabase();
     $sql="select count(*) as num from incomingCall where caller = '$voterID'";
     $rs= &$cn->Execute($sql);
     $num =$rs->fields[0];
     if ($num>0)
     {
        $cn->Close();
        return false;
     }
     $sql="select count(*) as num from incomingsms where sender = '$voterID'";
     $rs= &$cn->Execute($sql);
     $num1 =$rs->fields[0];
     $cn->Close();
     
     if ($num1 > 0)
        return false;

     return true;
  }

  /** Check whether the voter has voted to this survey before
  * @param Integer $surveyID ID of this survey
  * @param String $username ID of voter
  * @return Boolean false represents this voter has voted this survey before.
  */
  function isMultipleVote($surveyID,$username)
  {
     $cn = connectDatabase();
     $sql="select count(surveyID) as num from surveyrecord where surveyID=$surveyID and voterID = '$username'";
     $rs= &$cn->Execute($sql);

     $num =$rs->fields[0];
     $cn->Close();
     if ($num == 0)
        return true;
     else
        return false;
  }
  /**
  * Insert a voting record of a survey into database
  * @param SurveyRecordVO $surveyRecordVO
  * @return Boolean true | false
  */
   function insertRecord($surveyRecordVO)
   {
     try{
   	 	$cn = connectDatabase();
     	$sql = "insert into surveyRecord(surveyID,choiceID,presentationID,voterID,voteDate,voteType) ";
     	$sql = $sql." values(".$surveyRecordVO->getSurveyID().",".$surveyRecordVO->getChoiceID().",".$surveyRecordVO->getPresentationID().",'".$surveyRecordVO->getVoterID()."','";
     	$sql = $sql.$surveyRecordVO->getVoteDate()."','WEB')";
     	$cn->Execute($sql);
     	$sql = "update surveyChoice set vote=vote+1 where surveyID =". $surveyRecordVO->getSurveyID()." and choiceID = ".$surveyRecordVO->getChoiceID();
     	$cn->Execute($sql);
    	$cn->Close();
     }
        catch (SurveyException $e)
           {
              $cn->Close(); 
           	  $e->showError();
              return true;
           }
		catch (Exception $e)
		 	{
             $cn->Close(); 
             $errorMsgs = $e->getTrace();
             errorLogger($errorMsgs);
             return true;
            }
   }
}

 /**
 * A value object of a voting record of surveys which follows PHP MVC suggestion.
 * @package ValueObject of survey
 * @author Bai Qifeng
 * @version 1.0
 * @copyright Free copy.
 */
class SurveyRecordVO
{
     private $surveyID;
     private $choiceID;
     private $presentationID = 0;
     private $voterID;
     private $voteDate;
     private $voteType;

     /**
     * set SurveyID
     * @param Integer $surveyID can get from SurveyVO->getSurveyID()
     */
     public function setSurveyID($surveyID)
     {
       $this->surveyID = $surveyID;
     }
     /**
     * set Which choice in this survey the voter chooses
     * @param Integer $choiceID
     */
     public function setChoiceID($choiceID)
     {
       $this->choiceID = $choiceID;
     }
     /**
     * set Which presentation in this survey the voter chooses
     * @param Integer $choiceID
     */
     public function setPresentationID($presentationID)
     {
       $this->presentationID = $presentationID;
     }
     /**
     * set who is voting
     * @param String $voteID
     */
     public function setVoterID($voterID)
     {
       $this->voterID = $voterID;
     }
     /**
     * set When the voter voted
     * @param Datetime $voteDate
     */
     public function setVoteDate($voteDate)
     {
       $this->voteDate = $voteDate;
     }
     /**
     * set Which way the voter voted by
     * @param String $voteType usually voters vote by CAL,WEB or SMS
     */
     public function setVoteType($voteType)
     {
       $this->voteType = $voteType;
     }
     /**
     * get the Survey ID
     * @return Integer $surveyID
     */
     public function getSurveyID()
     {
       return $this->surveyID;
     }
     /**
     * get Which choice in this survey the voter chose
     * @return Integer $choiceID
     */
     public function getChoiceID()
     {
       return $this->choiceID;
     }
     /**
     * get Which choice in this survey the voter chose
     * @return Integer $presentationID
     */
     public function getPresentationID()
     {
       return $this->presentationID;
     }
     
     /**
     * get who voted
     * @return String $voteID
     */
     public function getVoterID()
     {
       return $this->voterID;
     }
     /**
     * get When the voter voted
     * @return Datetime $voteDate
     */
     public function getVoteDate()
     {
       return $this->voteDate;
     }
      /**
     * get Which way the voter voted by
     * @return String $voteType usually voters vote by CAL,WEB or SMS
     */
     public function getVoteType()
     {
       return $this->voteType;
     }
}

?>