<?php
/**
* This page includes class USR, SurveyRecordDAO and SurveyRecordVO which are used to
* vote for a Survey.
*
* @package DAO of Survey 
*/

require_once("connection.php");
//require_once("error.php");
require_once("VO/CallVO.php");
/**
* Class Usr includes functions which can vote surveys so far
*
* @author Bai Qifeng
* @version 2.0
*/
class Usr
{
  private $usrID;
  /**
  * Initiate a voter
  * @param string $username
  * ATTENTION: USING "DEFAULT" AS USERNAME CAUSES NO MUTIPLE-VOTING CHECK
  */
  function __construct($username)
  {
     $this->usrID = $username;
  }


  /**
  * Vote a survey
  * @param integer $surveyID surveyID of a survey which want to be voted
  * @param integer $choiceID choice of a survey which voter wants to vote
  * @param integer $presenationID presentationID of a survey, could be NULL
  * @param integer $pageID pageID of this survey which is contained, could be NULL
  * @return boolean
  */
  
  public function vote($surveyID,$choiceID,$presentationID = NULL,$pageID = NULL)
  {
        $finalSql = array();
        $presentationID = is_null($presentationID) ? 0 : $presentationID;
        $username = $this->usrID;
        
        /*
        * IMPORTANT: JUMP VOTING PROCESS TO NEXT STEP
        */
       try{
         $cn = connectDatabase();
         $sql = "select pageID,surveyID, choiceID,presentationID,invalidAllowed,votesAllowed from view_current_survey where surveyID = $surveyID";
         $cn->SetFetchMode(ADODB_FETCH_ASSOC);
         $rs = &$cn->Execute($sql);
         
         
         $vote = new VoteVO($surveyID,$choiceID,$presentationID,$username,date("Y-m-d H:i:s"),'WEB',$rs->fields['invalidAllowed'],$rs->fields['votesAllowed']); 
       	 include("VoteProcess.php"); 

         $cn->StartTrans();
          foreach ($finalSql as $sql)
          {
              if ( $cn->Execute($sql)==false)
                throw new Exception($cn->ErrorMsg(),400) ;
          }
          if ($cn->HasFailedTrans()) {
               // Something went wrong
               $cn->Close();
               throw new Exception("ODBC Commit error:.$message",400);
           }
          else
          {
              $cn->Close();
              return true;
           }

       }
      catch (Exception $e)
       {
         if ($cn->IsConnected())
           {
              $cn->Close();
              print $e->getMessage();
             //$e->showError();
              return false;
           }
       }

  }
  
  
   /**
    * Calcuate the mark which choice gets
    *
    * @param integer $choiceID
    * @param integer $numOfChoices
    * @return integer $mark
    */
   private function evaluatePresentationMark($choiceID,$numOfChoices)
   {
   	//reversing the marks simplely
   	 
   	return $numberOfchoices - $choiceID + 1;
   }
  
  
  /**
   * Get the list of phones which are used by this author, order by used times 
   * @return Array $usedPhones
   */
  public function getUsedPhoneLists()
  {

   $cn = connectDatabase();

   
   $sql = "SELECT surveychoice.receiver, count(surveychoice.receiver) as number ";
   $sql =$sql."FROM page INNER JOIN survey ON page.pageID = survey.pageID INNER JOIN ";
   $sql = $sql."surveychoice ON survey.surveyID = surveychoice.surveyID WHERE ";
   $sql = $sql."(page.author = '$this->usrID') group by surveychoice.receiver order by number desc";
   
   $cn->SetFetchMode(ADODB_FETCH_ASSOC);
   $rs= &$cn->Execute($sql);
   $usedPhones = array();
    while(!$rs->EOF)
    {
	//$list = array();
        //$list[]=  $rs->fields["receiver"];
        //$list[]=  $rs->fields["number"];
        //$usedPhones[] = $list;
        $usedPhones[] =  $rs->fields["receiver"];
        $rs->MoveNext();
    }
    $rs->Close();
    $cn->Close();
    return $usedPhones;
   // select author, count(author) from survey where survey.surveyID in (select surveyID from surveychoice where receiver = '7301') group by author
  }
}
?>