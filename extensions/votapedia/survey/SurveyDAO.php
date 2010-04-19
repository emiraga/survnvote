<?php
/**
* This page includes class SurveyDAO which is used to 
* save/retreive data of a Survey. It contains Read/Create/Update/Delete
* and other relevant funtionalities
*
* @package DAO of Survey 
*/

require_once("VO/PageVO.php");
require_once("connection.php");
require_once("error.php");
/**
* SurveyDAO includes functions which can access and set
* (a) Survey(s)' info into or from database system.
*  
* @author Bai Qifeng
* @version 2.0 Beta
*/
class SurveyDAO
{
	/**
	 * Find surveys which are related with wiki page
	 *
	 * @param string $title title of wiki page
	 * @return PageVO $page an Instance of PageVO
	 * @version 2.0
	 */
	function findByPage($title)
	{
	try
		{
		  $cn = connectDatabase();
		  
		  $sql ="select * from page where title = '".$title."'";
		  //echo $sql;
		  $cn->SetFetchMode(ADODB_FETCH_ASSOC);
          $rs= &$cn->Execute($sql);
		  $page = new PageVO();
		  
		  if ($rs->RecordCount()>0)
		     {
		       $page->setPageID($rs->fields["pageID"]);
		 	   $page->setTitle($rs->fields["title"]);
		 	   
			   $page->setStartTime($rs->fields["startTime"]);
			   $page->setDuration($rs->fields["duration"]);
			   $page->setEndTime($rs->fields["endTime"]);
			   $page->setAuthor(trim($rs->fields["author"]));
			   $page->setCreateTime($rs->fields['createTime']);
			   $page->setInvalidAllowed($rs->fields['invalidAllowed']);
			   $page->setAnonymousAllowed($rs->fields['anonymousAllowed']);
			   $page->setTeleVoteAllowed($rs->fields['teleVoteAllowed']);
			   $page->setShowGraph($rs->fields['showGraph']);
			   $page->setDisplayTop($rs->fields['displayTop']);
			   $page->setVotesAllowed($rs->fields['votesAllowed']);
			   $page->setType($rs->fields['surveyType']);
			   $page->setSubtractWrong($rs->fields['subtractWrong']);
       
			   if ($page->getStartTime()< date("Y-m-d H:i:s") && $page->getEndTime()>date("Y-m-d H:i:s"))
			     { $page->setActivated(true); }
			   else
		   	     { $page->setActivated(false); }
		   	   $rs->Close();  
		   	   //$surveys = array();
		   	   //$surveys = $this->getSurveys($cn,$page->getPageID());
		   	   //$page->setSurveys($surveys);

		   	   $page->setSurveys($this->getSurveys($cn,$page));  
			  }
		  else
		  {
		  	 throw new SurveyException("Cannot find corresponding page.",201);
		  }
		  $cn->Close();
		  return $page;
		 }
		 catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
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
	} //findByPage end.
	
	/**
	 * Find surveys which are related with wiki page
	 *
	 * @param integer $id ID of wiki page
	 * @return PageVO $page An instance of PageVO
	 * @version 2.0
	 */
	function findByPageID($id)
	{
	try
		{
		  $cn = connectDatabase();
		  $sql ="select * from page where pageID = '".$id."'";
		  //echo $sql;
		  $cn->SetFetchMode(ADODB_FETCH_ASSOC);
          $rs= &$cn->Execute($sql);
		  $page = new PageVO();
		  
		  if ($rs->RecordCount()>0)
		     {
		       $page->setPageID($rs->fields["pageID"]);
		 	   $page->setTitle($rs->fields["title"]);
		 	   
			   $page->setStartTime($rs->fields["startTime"]);
			   $page->setDuration($rs->fields["duration"]);
			   $page->setEndTime($rs->fields["endTime"]);
			   $page->setAuthor(trim($rs->fields["author"]));
			   $page->setCreateTime($rs->fields['createTime']);
			   $page->setInvalidAllowed($rs->fields['invalidAllowed']);
			   $page->setAnonymousAllowed($rs->fields['anonymousAllowed']);
			   $page->setTeleVoteAllowed($rs->fields['teleVoteAllowed']);
			   $page->setShowGraph($rs->fields['showGraph']);
			   $page->setDisplayTop($rs->fields['displayTop']);
			   $page->setVotesAllowed($rs->fields['votesAllowed']);
			   $page->setType($rs->fields['surveyType']);
			   $page->setSubtractWrong($rs->fields['subtractWrong']);
       
			   if ($page->getStartTime()< date("Y-m-d H:i:s") && $page->getEndTime()>date("Y-m-d H:i:s"))
			     { $page->setActivated(true); }
			   else
		   	     { $page->setActivated(false); }
		  	   
		   	   $rs->Close();  
		   	   //$surveys = array();
		   	   //$surveys = $this->getSurveys($cn,$page->getPageID());
		   	   //$page->setSurveys($surveys);

		   	   $page->setSurveys($this->getSurveys($cn,$page));  
			  }
		  else
		  {
		  	 throw new SurveyException("Cannot find corresponding page.",201);
		  }
		  $cn->Close();
		  return $page;
		 }
		 catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
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
		
	} //findByPage end.

 /**
  * Insert Page into database, which does not include
  * survey /surveys, which survey type is Quiz),
  * choices,and presentations(if survey type is Presentation)
  * @param PageVO $pageVO
  * 
  */
 public function insertPage(PageVO $pageVO)
 {
 
	try{
		$cn = connectDatabase();
		 
		// Check wether the page exists
		$sql="select count(*) as num from page where title ='".$pageVO->getTitle()."'";
		//echo "InsertPage()</br>";
		$rs= $cn->Execute($sql);
		
		if ($rs->fields[0] == 0)
		 {
			$cn->StartTrans();
            
			$sql = "insert into page(title,author,phone,startTime,duration,endTime,invalidAllowed,smsRequired,teleVoteAllowed,
                                     anonymousAllowed,showGraph,surveyType,displayTop,subtractWrong) ";
			$sql = $sql."values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $resPage = $cn->Prepare($sql);
			$param = array( $pageVO->getTitle(),
			                $pageVO->getAuthor(),
			                $pageVO->getPhone(),
			                $pageVO->getStartTime(),
			                $pageVO->getDuration(),
			                $pageVO->getEndTime(),
			                $pageVO->isInvalidAllowed(),
			                $pageVO->isSMSRequired(),
			                $pageVO->getTeleVoteAllowed(),
			                $pageVO->isAnonymousAllowed(),
			                $pageVO->isShowGraph(),
			                $pageVO->getType(),
			                $pageVO->getDisplayTop(),
			                $pageVO->isSubtractWrong()
			              );
           $cn->Execute($resPage,$param);
           // Get pageID from database
           $sql = "select pageID from page where title ='".$pageVO->getTitle()."'";
           $rsID = $cn->Execute($sql);
           $pageVO->setPageID($rsID->fields[0]);
           $rsID->Close();
		  
           //The following program is used to insert survey.
           if ($pageVO->getNumOfSurveys()>0)
		    {
		    	$surveys = $pageVO->getSurveys();
		    	
		    	foreach ($surveys as $survey)
		    	{
		    		$survey->setPageID($pageVO->getPageID());
		    		$this->insertSurvey($cn,$survey);
		   		 }
			}
		     
		    $cn->CompleteTrans();
            if ($cn->HasFailedTrans()) {
                         // Something went wrong
               $message = $cn->ErrorMsg();
               $cn->Close();
               throw new SurveyException("ODBC Commit error:.$message",400);
              }
             else
               {
                  $cn->Close();
                  return true;
               }
		  }
	     else
	        throw new SurveyException("This page exists.",202); 
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
	
 /**
  * update a page and 
  * Insert survey(s) into database, which includes
  * survey /surveys, which survey type is Quiz),
  * choices,and presentations(if survey type is Presentation)
  * @param PageVO $pageVO
  * 
  */
 public function updatePage(PageVO $pageVO)
 {//echo "update()</br>";
	try{
		$cn = connectDatabase();

		// Check wether the page exists
		$sql="select pageID from page where title ='".$pageVO->getTitle()."'";
		$cn->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs= $cn->Execute($sql);
		
		if ($rs->RecordCount() >= 1)
		 {
			$pageID = $rs->fields["pageID"];
     		$pageVO->setPageID($pageID);
		 	
     		$cn->StartTrans();
            
			$sql = "update page set startTime=?,duration=?,endTime=?,";
			$sql =$sql."invalidAllowed=?,smsRequired=?,teleVoteAllowed=?,";
			$sql =$sql." anonymousAllowed=?,showGraph=?,surveyType=?,displayTop=?,votesallowed=?,subtractWrong=? ";
			$sql = $sql."where pageID = $pageID";
            $resPage = $cn->Prepare($sql);
			$param = array( $pageVO->getStartTime(),
			                $pageVO->getDuration(),
			                $pageVO->getEndTime(),
			                $pageVO->isInvalidAllowed(),
			                $pageVO->isSMSRequired(),
			                $pageVO->getTeleVoteAllowed(),
			                $pageVO->isAnonymousAllowed(),
			                $pageVO->isShowGraph(),
			                $pageVO->getType(),
			                $pageVO->getDisplayTop(),
			                $pageVO->getVotesAllowed(),
			                $pageVO->isSubtractWrong()
			              );
           $cn->Execute($resPage,$param);
         
           if ($pageVO->getNumOfSurveys()>0)
		    {
		    	$surveys = $pageVO->getSurveys();
		    	
		    	foreach ($surveys as $survey)
		    	{
		    		$survey->setPageID($pageVO->getPageID());
		    		$this->insertSurvey($cn,$survey);
		   		 }
			}
		     
		    $cn->CompleteTrans();
            if ($cn->HasFailedTrans()) {
                         // Something went wrong
               $message = $cn->ErrorMsg();
               $cn->Close();
               throw new SurveyException("ODBC Commit error:.$message",400);
              }
             else
               {
                  $cn->Close();
                  return true;
               }
		  }
	     else
	        throw new SurveyException("This page does not exist.",201); 
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
	

	/**
	* Get a survey(SurveyVO) by surveyID
	* @param integer $id an ID which want to be retreived
	* @return SurveyVO $survey a survey which matches ID
	* @version 2.0
	*/
	function findSurveyByID($id)
	{
		try
		{
	 	  $survey = new SurveyVO();
		  $cn = connectDatabase();
		  $sql = "select * from survey where surveyID=$surveyID";
	
          $cn->SetFetchMode(ADODB_FETCH_ASSOC);
		   
          $rsSurveys = &$cn->Execute($sql);
		   
		   if ($rsSurveys->RecordCount()>0)
		   {
		   	   $survey->setPageID($rsSurveys->fields["pageID"]);
		   	   $survey->setSurveyID($rsSurveys->fields["surveyID"]);
		 	   $survey->setQuestion(trim($rsSurveys->fields["question"]));
			   $survey->setAnswer(trim($rsSurveys->fields["answer"]));
			   $survey->setPoints($rsSurveys->fields["points"]);
	           
			   $choices = array();
	           $choices = $this->getChoices($cn,$survey->getSurveyID());
	           $survey->setChoices($choices);

	           $presentations = array();
			   $presentations = $this->getPresentations($cn,$survey->getSurveyID());
			   $survey->setPresentations($presentations);
			 }
			 $rsSurveys->Close();
		     $cn->Close();
		     return $survey;
		 }
		 catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
			   $cn->Close();
            $e->showError();
			return true;
		 }
	     catch (Exception $e)
		 {
             $errorMsgs = $e->getTrace();
             errorLogger($errorMsgs);
             return true;
          }

  } 
  
  function findCurrentSurveys($num)
	{
		try
		{
	 	  $cn = connectDatabase();
		  $sql = "select * from page where starttime < now() and endtime>now() and surveytype=1 order by starttime desc";
          $cn->SetFetchMode(ADODB_FETCH_ASSOC);
          $rs = &$cn->Execute($sql);
          
		  $surveys = array();
		  $surveyIDs = array();
		  $votesAllowed = array();
		  
		  if ($num == null || $num <=0)
		  		$num = 1;

		  for($i=0;$i<$num;$i++)
		  {
		  		if(!$rs->EOF)
		  		{
		  			$surveyIDs[]= $rs->fields["pageID"];
		  			$votesAllowed[] = $rs->fields["votesAllowed"];
		  			$rs->MoveNext();
		  		}
		  		else
		  		{
		  			break;	
		  		}
		  }
	      
		  $j=0;
          foreach($surveyIDs as $id)
          {
         	   $survey = new SurveyVO();
          	   $sql = "select * from survey where pageID=$id";
  	           $cn->SetFetchMode(ADODB_FETCH_ASSOC);
               $rsSurveys = &$cn->Execute($sql);
    
			   if ($rsSurveys->RecordCount()>0)
			   {
			   	   $survey->setPageID($rsSurveys->fields["pageID"]);
			   	   $survey->setSurveyID($rsSurveys->fields["surveyID"]);
		 		   $survey->setQuestion(trim($rsSurveys->fields["question"]));
		     	   $survey->setAnswer(trim($rsSurveys->fields["answer"]));
			   	   $survey->setPoints($rsSurveys->fields["points"]);
				   $survey->setVotesAllowed($votesAllowed[$j]);	
			   	   
			       $choices = array();
	           	   $choices = $this->getChoices($cn,$survey->getSurveyID());
	           	   $survey->setChoices($choices);

	               //$presentations = array();
			       //$presentations = $this->getPresentations($cn,$survey->getSurveyID());
			       //$survey->setPresentations($presentations);
			       $rsSurveys->Close();
			       
			   }
			   $j++;
			   $surveys[]=$survey ;
			 
          }
			 
		     $cn->Close();
		     return $surveys;
		 }
		 catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
			   $cn->Close();
            $e->showError();
			return true;
		 }
	     catch (Exception $e)
		 {
             $errorMsgs = $e->getTrace();
             errorLogger($errorMsgs);
             return true;
          }
  } 
  /**
   * Reset all surveys in this page. 
   * Votes on choices,marks on presentation will be set to 0
   * All survey records would be deleted.
   * 
   * @param PageVO $pageVO
   * @return boolean true
   */
  function resetSurveys(PageVO $pageVO)
  {
  	$surveys = $pageVO->getSurveys();
  	try
  	{
  		$cn = connectDatabase();
  		$finalSql = array();
  		$res = array();
  		$finalSql[] = "delete from surveyRecord where surveyID = ?";
  		$finalSql[] = "update surveyChoice set vote = 0 where surveyID = ?";
  		foreach($finalSql as $sql)
  	   		$res[] = $cn->Prepare($sql);
  	
  		foreach($surveys as $survey)
  		{
  			$surveyID = $survey->getSurveyID();
  			foreach($res as $resSql)
  		   		$cn->Execute($resSql,$surveyID);
 		}
 		$cn->Close();
 		return true;
  	}
    catch (Exception $e)
	{
       $errorMsgs = $e->getTrace();
       errorLogger($errorMsgs);
       return false;
    }
  }
  
  /**
   * Reset a survey. 
   * Votes on choices,marks on presentation will be set to 0
   * All survey records would be deleted.
   * 
   * @param SurveyVO $surveyVO
   * @return boolean true
   */
  function resetSurvey(SurveyVO $surveyVO)
  {
  	$surveyID = $survey->getSurveyID();
  	try
  	{
  		$cn = connectDatabase();
  		$finalSql = array();
  		$finalSql[] = "delete from surveyRecord where surveyID = ?";
  		$finalSql[] = "update surveyChoice set vote = 0 where surveyID = ?";
  		foreach($finalSql as $sql)
  		{
  	   		$res = $cn->Prepare($sql);
  	   		$cn->Execute($resSql,$surveyID);
  		}
  		
 		$cn->Close();
 		return true;
  	}
    catch (Exception $e)
	{
       $errorMsgs = $e->getTrace();
       errorLogger($errorMsgs);
       return false;
    }
  }
	 /**
	 * Insert a new survey contains multi choices,presentations
	 * @param Connection $cn
	 * @param SurveyVO $survey an instance of SurveyVO 
	 * @version 2.0
	 */
     private function insertSurvey($cn,SurveyVO $survey)
     {//echo "insertSurvey()</br>";
		    		$sql="insert into survey(pageID,question,answer,points) values (?,?,?,?)";
		    	    $res=$cn->Prepare($sql);
                     
        			$paramSurvey = array($survey->getPageID(),
         			 			  		 $survey->getQuestion(),
         			  					 $survey->getAnswer(),
         			  					 $survey->getPoints()
										  );
       				
					 $cn->Execute($res,$paramSurvey);

					if ($survey->getNumOfChoices()>0)
		  		 		{
		   			 	 // Get SurveyID from database.
		   			 	 $sql = "select surveyID from survey where question = '".$survey->getQuestion()."'";
		    		 	 $sql = $sql." and pageID = ".$survey->getPageID()." order by surveyid desc";
           	 		 	 $rsSurveyID = $cn->Execute($sql);
            			 $survey->setSurveyID($rsSurveyID->fields["surveyID"]);
                    	 $rsSurveyID->Close();   
		 	          	//Insert Choices begin
    	  	          	 $sql = "insert into surveyChoice(surveyID,choiceID,choice,points)";
			          	$sql = $sql."values(".$survey->getSurveyID().",?,?,?)";
     	   	          	$resChoice = $cn->Prepare($sql);
			          	$choiceID = 0;
		              	foreach($survey->getChoices() as $surveyChoice)
	   		               	{
	 	        	         $choiceID++;
	 	        	         $param = array($choiceID,
				   				            $surveyChoice->getChoice(),
				                            $this->evaluatePoints($choiceID,$survey->getNumOfChoices())
				                   );
              	         	 $cn->Execute($resChoice,$param);
			           		}
		           		}  
		   
      	 			if ($survey->getNumOfPresentations()>0)
		  			  {
		 				 //Insert presentations begin
    	  				 $sql = "insert into presentation (surveyID,presentationID,presentation,active)";
						 $sql = $sql."values(?,?,?,?)";
     	  	 			 $resPre = $cn->Prepare($sql);
						 $presentationID = 0;
		   				  foreach($survey->getPresentations()as $presentation)
   		    			   {
 	        				 $presentationID++;
              				 $cn->Execute($resPre,array($survey->getSurveyID(),
              	                         $presentationID,
              	                         $presentation->getPresentation(),
                                         $presentation->getActive()
                                        )
                              		);
			   		 		}
		 		  		} 
     }


	 /**
	 * Delete a page which includes tables of page, 
	 * Survey,SuveyChoice,Presentation,SurveyRecord.
	 * @param string $title title of a wiki page
	 * @version 2.0
	 */
	 function deletePage($title)
	 {
	 	$this->deleteSurvey($title);
	 	$cn=connectDatabase();
	 	$sql = "delete from page where title ='$title'";
	    $cn->Execute($sql);
	    $cn->Close();
	 	return true;
	 }
	 /**
	 * Delete suveys in a page which includes the data items in 
	 * Survey,SuveyChoice,Presentation,SurveyRecord.
	 * Page table would still be saved.
	 * @param string $title title of a wiki page
	 * @version 2.0
	 */
	 function deleteSurvey($title)
	 {
	    try{
			$cn = connectDatabase();
			$sql = "select pageID from page where title = '".$title."'";
			$rs = $cn->Execute ($sql);
			$row =$rs->RecordCount();
			if ($row>0)
			 {
				$cn->StartTrans();
                $id = $rs->fields[0];
                $rs->Close();
                $sql = "select surveyID from survey where pageID=".$id;
				$rs = $cn->Execute($sql);
			    while (!$rs->EOF)
                    {
                        $surveyID = $rs->fields[0];
                    	$sql = "delete from presentation where surveyID =".$surveyID;
						$cn->Execute($sql);
						$sql = "delete from surveychoice where surveyID =".$surveyID;
						$cn->Execute($sql);  
                    	$sql = "delete from surveyrecord where surveyID=".$surveyID;
                    	$cn->Execute($sql);
                    	
						$rs->MoveNext();
                    }
				
                $sql = "delete from survey where pageID =".$id;
				$cn->Execute($sql);
				//$sql = "delete from page where pageID =".$id;
				//$cn->Execute($sql);
				$cn->CompleteTrans();
			    if ($cn->HasFailedTrans()) {
                     // Something went wrong
                     $message = $cn->ErrorMsg();
                     $cn->Close();
                     throw new Exception("ODBC Commit error:.$message");
                   }
                  else
                   {
                       $cn->Close();
                       return true;
                   }
			  }
			 else
			 {
				$cn->Close();
				throw new SurveyException("No survey matches this question!",201);
              }

	        }
		catch (SurveyException $e)
		{
		   $e->showError();
		   return false;
		}
	    catch (Exception $e)
		 	{
             $cn->Close(); 
             $errorMsgs = $e->getTrace();
             errorLogger($errorMsgs);
             return false;
            }
	 }
	 /**
	 * private method. Using database record to fill in a SurveyVO.
	 * 
	 * @param DababaseConnection $cn connection string with database
	 * @param integer $pageID ID of wiki page.
	 * @return Array $surveys 
	 * @version 2.0
	 */
	 private function getSurveys($cn,$page)
	 {
           $surveys = array();
           $sql = "select * from survey where pageID=";
		   $sql = $sql . $page->getPageID()." order by surveyID";
	
           $cn->SetFetchMode(ADODB_FETCH_ASSOC);
		   
           $rsSurveys = &$cn->Execute($sql);
		   
		   if ($rsSurveys->RecordCount()>0)
		   {
			 while(!$rsSurveys->EOF)
			 {  
			   $survey = new SurveyVO();

		   	   $survey->setPageID($rsSurveys->fields["pageID"]);
		   	   $survey->setSurveyID($rsSurveys->fields["surveyID"]);
		 	   $survey->setQuestion(trim($rsSurveys->fields["question"]));
			   $survey->setAnswer(trim($rsSurveys->fields["answer"]));
			   $survey->setPoints($rsSurveys->fields["points"]);
			   
			   //Redundant info from PageVO for simplify further development
			   $survey->setInvalidAllowed($page->isInvalidAllowed());
			   $survey->setType($page->getType());
    		   
	           $choices = array();
	           
	           $choices = $this->getChoices($cn,$survey->getSurveyID());
	           $survey->setChoices($choices);

	           //get choices in this survey 
			   //$survey->setChoices($this->getChoices($cn,$survey->getSurveyID()));
			   //$survey->setPresentations($this->getPresentations($cn,$survey->getSurveyID()));
			   $presentations = array();

			   $presentations = $this->getPresentations($cn,$survey->getSurveyID());
			   $survey->setPresentations($presentations);
		   
			   $surveys[]=$survey;
		       $rsSurveys->MoveNext();
			 }
			 $rsSurveys->Close();
			}
		return $surveys	;
	 }
 	 /**
 	  * private functin. Get choices of a survey
 	  *
 	  * @param DababaseConnection $cn connection string with database
 	  * @param integer $surveyID
 	  * @return array $choices
 	  * @version 2.0
 	  */
      private function getChoices($cn,$surveyID)
      {
      	$sql = "select * from surveyChoice where surveyID=";
		$sql = $sql . $surveyID." order by choiceID";
		$choices = array();
		
		$cn->SetFetchMode(ADODB_FETCH_ASSOC);
		$rsChoice = &$cn->Execute($sql);
        if ($rsChoice->RecordCount()>0)
		 {
		     
		     while(!$rsChoice->EOF)
               {
	             //Access by name, some database may not support this
                 //small case
                   $choice = new ChoiceVO();
                   $choice->setSurveyID($rsChoice->fields['surveyID']);
                   $choice->setChoiceID($rsChoice->fields['choiceID']);
			   	   $choice->setChoice(trim($rsChoice->fields['choice']));
			   	   $choice->setReceiver(trim($rsChoice->fields['receiver']));
			   	   $choice->setSMS(trim($rsChoice->fields['SMS']));
			   	   $choice->setVote($rsChoice->fields['vote']);
			   	   $choice->setPoints($rsChoice->fields['points']);

			   	   $choices[] = $choice;
                   $rsChoice->MoveNext();
	           }

              $rsChoice->Close();
           }
           return $choices;
      }
 	 /**
 	  * Private function.Get presentations of a survey
 	  *
 	  * @param DababaseConnection $cn connection string with database
 	  * @param integer $surveyID
 	  * @return array $presentations
 	  * @version 2.0
 	  */
      private function getPresentations($cn,$surveyID)
      {
     /*
      	$sqlRecord = "SELECT presentationID, sum( votes * points )as mark 
		   FROM (
		
			SELECT result.presentationID, result.choiceID, result.votes, choice.points
			FROM (

				SELECT surveyID, surveyRecord.presentationID AS presentationID, surveyRecord.choiceID AS choiceID, count( choiceID ) AS votes
					FROM surveyRecord
					WHERE surveyRecord.surveyID =$surveyID
					GROUP BY presentationID, choiceID
					) AS result
				LEFT JOIN (

					SELECT choiceID, points
						FROM surveychoice
						WHERE surveyID =$surveyID
					) AS choice ON result.choiceID = choice.choiceID
			) AS stat
				GROUP BY presentationid"; */
		//Collect vote data,complicated SQL
      	$sqlRecord = "select * from view_presentation_survey_mark where surveyid = $surveyID";
      	$cn->SetFetchMode(ADODB_FETCH_ASSOC);
     	$rsVote=&$cn->Execute($sqlRecord);
    	$marks = array();
    	
     	while(!$rsVote->EOF)
     	{

     		$marks[$rsVote->fields['presentationID']] = $rsVote->fields['marks'];
     		$rsVote->MoveNext();
     	}
      	
     	// print_r($marks);
      	
      	$sql = "select * from presentation where surveyID=";
		$sql = $sql . $surveyID." order by presentationID";
		
		$presentations = array();
		
		$cn->SetFetchMode(ADODB_FETCH_ASSOC);
		$rsPresentation = &$cn->Execute($sql);
        if ($rsPresentation->RecordCount()>0)
		 {
            
		     while(!$rsPresentation->EOF)
               {
	             //Access by name, some database may not support this
                 //small case
                   $presentation = new PresentationVO();
                   
				   $presentation->setSurveyID($surveyID);
				   $presentation->setPresentationID($rsPresentation->fields['presentationID']);	
			   	   $presentation->setPresentation($rsPresentation->fields['presentation']);
			   	   $presentation->setActive($rsPresentation->fields['active']);
			   	   
			   	   $presentation->setMark(is_null($marks[$presentation->getPresentationID()])? 0:$marks[$presentation->getPresentationID()]);
			   				   	   
			   	   $presentations[] = $presentation;
                   $rsPresentation->MoveNext();
	           }

              $rsPresentation->Close();
           }
           return $presentations;
      	
      }

   /**
    * Calcuate the mark which choice gets
    *
    * @param integer $choiceID
    * @param integer $numOfChoices
    * @return integer $mark
    */
   private function evaluatePoints($choiceID,$numberOfChoices)
   {
   	//reversing the marks simplely
    	return $numberOfChoices - $choiceID + 1;
   }
  /**
   * Activate a presentation in a survey
   *
   * @param Integer $surveyID 
   * @param integer $presentationID
   */
  function activatePresentation($surveyID,$presentationID)
	 {
	    //require_once("connection.php");
         try{

            $cn=connectDatabase();
            
            $cn->StartTrans();

            $sql = "update presentation set active = 0 where 
                    surveyID = $surveyID";
            $cn->Execute($sql);
			$sql = "update presentation set active = 1 where 
                    surveyID = $surveyID and presentationID = $presentationID";
            $cn->Execute($sql);

	        $cn->CompleteTrans();
            $cn->Close();
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
  /**
  * This is function is to start a survey.
  * So, it requires the starting time of survey is set up before it is started.
  * Basically, the following stages are used:
  * @param PageVO $pageVO;
  * @version 2.0
  */
  function startSurvey(PageVO $pageVO)
	 {
	    //require_once("connection.php");
         try{

            $cn=connectDatabase();
            $startDate = date("Y-m-d H:i:s");
            $pageVO->setStartTime($startDate);
            $cn->StartTrans();

            $sql = "update page set starttime = '".$pageVO->getStartTime()."'";
            $sql= $sql.",endtime='".$pageVO->getEndTime()."' where pageID =".$pageVO->getPageID();
            $cn->Execute($sql);


	        $cn->CompleteTrans();
            if ($cn->HasFailedTrans()) {
                   $message = $cn->ErrorMsg();
                   $cn->Close();
                   throw new SurveyException("ODBC Commit error:.$message",400);
                   }
            else
              {
                $cn->Close();
                return true;
              }
         }
	 catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
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

/**
  * This is function is to continue a survey.
  * It requires the time which the survey continue to run.
  * @param PageVO $pageVO;
  * @version 2.0
  */
  function continueSurvey(PageVO $pageVO)
	 {
	    //require_once("connection.php");
         try{

            $cn=connectDatabase();
            $duration= $pageVO->getDuration();
            $endTime=time()+$duration*60;
            $endDate = date("Y-m-d H:i:s",$endTime);
            $pageVO->setEndTime($endDate);
            $cn->StartTrans();

            $sql = "update page set endtime = '".$pageVO->getEndTime()."' where pageID =".$pageVO->getPageID();
            $cn->Execute($sql);


	        $cn->CompleteTrans();
            if ($cn->HasFailedTrans()) {
                   $message = $cn->ErrorMsg();
                   $cn->Close();
                   throw new SurveyException("ODBC Commit error:.$message",400);
                   }
            else
              {
                $cn->Close();
                return true;
              }
         }
	 catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
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
	 

  /**
  * It is different with starting a survey. 
  * Starting a survey requires we set up startint time before we are able to start it
  * In finishing procedure, we automatically set current time as finishing time.
  * @param PageVO $pageVO
  * @version 2.0
  */
  function finishSurvey(PageVO $pageVO)
	 {
            try{
                $cn = connectDatabase();

                $expiredDate = date("Y-m-d H:i:s");
                $sqlChoice = "update page set endtime = '".$expiredDate."' where pageID =".$pageVO->getPageID();
                $cn->StartTrans();
                $cn->Execute($sqlChoice);
                $cn->CompleteTrans();
                if ($cn->HasFailedTrans()) {
                     $message = $cn->ErrorMsg();
                     $cn->Close();
                     throw new SurveyException("ODBC Commit error:.$message",400);
                   }
                 else
                   return true;
		}
	 catch (SurveyException $e)
		 {
			if ($cn->IsConnected())
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
         /**
         * Request telephone numbers .
         * It requires the starting time of survey is set up before request.
         * which represents the duration which the receivers are used is same with duration of survey
         * Basically, the following stages are used:
         * $surveyVO->setStartTime(now());
         * $surveyDAO->requestReceivers($surveyVO);
         * @param PageVO $page
         */
	 public function requestReceivers(PageVO $page)
	 {
	   require_once("telephone.php");
	   try{
		 $telephone = new Telephone();
         return $telephone->setupReceivers($page);
	   }
	   catch (SurveyException $e)
		{
		   $e->showError();
		   return false;
		}
	  }

}
?>
