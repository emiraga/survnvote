<?php
if (!defined('MEDIAWIKI')) die();
/**
 * This page includes class SurveyDAO which is used to
 * save/retreive data of a Survey. It contains Read/Create/Update/Delete
 * and other relevant funtionalities
 *
 * @package DAO of Survey
 */
global $gvPath;
require_once("$gvPath/DAO/Telephone.php");
require_once("$gvPath/VO/PageVO.php");
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
	 * Execute query and return one result PageVO
	 *
	 * @param $where where statement in SQL
	 * @param $param array ov values which will be included in sql query
	 * @return PageVO on success, false in does not exist
	 */
	private function getOnePage($where, $param)
	{
		global $gvDB, $gvDBPrefix;

		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$sql ="select * from {$gvDBPrefix}page $where";
		$rs= &$gvDB->Execute($sql, $param);
		$page = new PageVO();

		if ($rs->RecordCount()==0)
		{
			$rs->Close();
			return false;
		}
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
		$page->setPhone( $rs->fields['phone'] );
		$page->setSMSRequired( $rs->fields['smsRequired'] );
		$rs->Close();
		$page->setSurveys($this->loadSurveys($page));
		return $page;
	}

	/**
	 * Find surveys which are related with wiki page
	 *
	 * @param $title title of wiki page
	 * @return PageVO $page an Instance of PageVO
	 * @version 2.0
	 */
	function findByPage($title)
	{
		$page = $this->getOnePage("where title = ?", array($title));
		if(!$page)
			throw new SurveyException("Cannot find corresponding page.", 201);
		return $page;
	}
	/**
	 * Find surveys which are related with wiki page
	 *
	 * @param $id ID of wiki page
	 * @return PageVO $page An instance of PageVO
	 * @version 2.0
	 */
	function findByPageID($id)
	{
		$page = $this->getOnePage("where pageID = ?", array($id));
		if(!$page)
			throw new SurveyException("Cannot find corresponding page.", 201);
		return $page;
	}
	/**
	 * Insert Page into database, optionally it includes
	 * survey /surveys, choices,and presentations (if survey type is Presentation)
	 * 
	 * @param $pageVO PageVO
	 * @param $insertSurveys should surveys be inserted as well
	 */
	public function insertPage(PageVO &$pageVO, $insertSurveys = false)
	{
		global $gvDB, $gvDBPrefix;
		$gvDB->StartTrans();

		$sql = "insert into {$gvDBPrefix}page (title,author,phone,startTime,duration,endTime,invalidAllowed,smsRequired,teleVoteAllowed,
                       anonymousAllowed,showGraph,surveyType,displayTop,subtractWrong) ";
		//@todo some fields from page are missing
		$sql = $sql."values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$resPage = $gvDB->Prepare($sql);
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
		$gvDB->Execute($resPage,$param);
		$pageVO->setPageID($gvDB->Insert_ID());

		if($insertSurveys)
		{
			$surveys = $pageVO->getSurveys();
			foreach ($surveys as $survey)
			{
				$survey->setPageID($pageVO->getPageID());
				$this->insertSurvey($survey);
			}
		}
		$gvDB->CompleteTrans();
		if ($gvDB->HasFailedTrans()) {
			throw new SurveyException("Erro while inserting a page: ".$gvDB->ErrorMsg(), 400);
		}
		return true;
	}
	/**
	 * Update a page and
	 * Insert survey(s) into database, which includes
	 * survey /surveys, which survey type is Quiz),
	 * choices,and presentations(if survey type is Presentation)
	 * 
	 * @param $pageVO PageVO
	 */
	public function updatePage(PageVO &$pageVO)
	{
		global $gvDB, $gvDBPrefix;
		// Check wether the page exists
	 	$pageID = $pageVO->getPageID();
		assert($pageID > 0);

		$gvDB->StartTrans();
	 	$sql = "update {$gvDBPrefix}page set title=?,startTime=?,duration=?,endTime=?,"
		 	. "invalidAllowed=?,smsRequired=?,teleVoteAllowed=?,"
		 	. " anonymousAllowed=?,showGraph=?,surveyType=?,displayTop=?,votesallowed=?,subtractWrong=? "
		 	. "where pageID = ?";
	 	$resPage = $gvDB->Prepare($sql);
	 	$param = array(
	 		$pageVO->getTitle(),
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
		 	$pageVO->getVotesAllowed(),
		 	$pageVO->isSubtractWrong(),
		 	$pageID
	 	);
	 	//@todo some fields here are missing
	 	$gvDB->Execute($resPage,$param);
		
	 	$this->deleteSurveys($pageID);
	 	
	 	$refsurveys =& $pageVO->getSurveys();
 		foreach ($refsurveys as &$survey)
 		{
 			$survey->setPageID($pageVO->getPageID());
 			$this->insertSurvey($survey);
 		}

 		$gvDB->CompleteTrans();
	 	if ($gvDB->HasFailedTrans()) {
	 		throw new SurveyException("ODBC Commit error: ".$gvDB->ErrorMsg(),400);
	 	}
 		return true;
	}
	/**
	 * Get array of surveys selected with an SQL statement
	 * 
	 * @param $sql select SQL statement 
	 * @param $params arrays of parameters to SQL statement
	 */
	private function getSurveysSQL($sql, $params)
	{
		global $gvDB, $gvDBPrefix;
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rsSurveys = &$gvDB->Execute($sql, $params);
			
		$surveys = array();
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
			$survey->setVotesAllowed( $page->getVotesAllowed() );
			//choices
			$choices = $this->getChoices($survey->getSurveyID());
			$survey->setChoices($choices);
			//presentations
			$presentations = $this->getPresentations($survey->getSurveyID());
			$survey->setPresentations($presentations);

			$surveys[]=$survey;
			$rsSurveys->MoveNext();
		}
		$rsSurveys->Close();
		return $surveys	;
	}
	/**
	 * Get a survey(SurveyVO) by surveyID
	 * @param $id an ID which want to be retreived
	 * @return SurveyVO $survey a survey which matches ID
	 * @version 2.0
	 */
	function findSurveyByID($id)
	{
		$surveys = getSurveysSQL("select * from {$gvDBPrefix}survey where surveyID = ?", array($surveyID));
		if(count($surveys) == 0)
			throw new SurveyException("Survey not found", 400);
		return $surveys[0];
	}
	/**
	 * Get an array of current surveys
	 * 
	 * @param $num number of surveys required, can be empty for all surveys
	 */
	function findCurrentSurveys($num = NULL)
	{
		global $gvDB, $gvDBPrefix;
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$sql = "select pageID from {$gvDBPrefix}page where starttime <= now() and endtime >= now() and surveytype = 1 order by starttime desc";
		$param = array();
		if($num)
		{
			$sql .= " limit ?";
			$param = array($num);
		}
		$rs = &$gvDB->Execute($sql, $param);
		$surveyIDs = array();
		$votesAllowed = array();

		while(!$rs->EOF)
		{
			$surveyIDs[]= $rs->fields["pageID"];
			//$votesAllowed[] = $rs->fields["votesAllowed"];
			$rs->MoveNext();
		}

		$surveys = array();
		foreach($surveyIDs as $id)
		{
			$survey = $this->getSurveysSQL("select * from {$gvDBPrefix}survey where pageID = ?", $id);
			if(count($survey) == 0)
				throw new SurveyException("findCurrentSurveys, survey not found.");
			$surveys[] = $survey[0] ;
		}
		return $surveys;
	}
	/**
	 * Reset all surveys in this page.
	 * Votes on choices,marks on presentation will be set to 0
	 * All survey records would be deleted.
	 *
	 * @param $pageVO PageVO
	 * @return boolean true
	 */
	function resetSurveys(PageVO $pageVO)
	{
		$surveys = $pageVO->getSurveys();
		global $gvDB, $gvDBPrefix;
		$res1= $gvDB->Prepare("delete from {$gvDBPrefix}surveyRecord where surveyID = ?");
		$res2= $gvDB->Prepare("update {$gvDBPrefix}surveyChoice set vote = 0 where surveyID = ?");
		
		foreach($surveys as $survey)
		{
			$surveyID = $survey->getSurveyID();
			$gvDB->Execute($res1,array($surveyID));
			$gvDB->Execute($res2,array($surveyID));
		}
		return true;
	}
	/**
	 * Reset a survey.
	 * Votes on choices,marks on presentation will be set to 0
	 * All survey records would be deleted.
	 *
	 * @param $surveyVO SurveyVO
	 * @return boolean true
	 */
	function resetSurvey(SurveyVO $surveyVO)
	{
		$surveyID = $survey->getSurveyID();
		global $gvDB, $gvDBPrefix;
		$gvDB->Execute("delete from {$gvDBPrefix}surveyRecord where surveyID = ?", array($surveyID));
		$gvDB->Execute("update {$gvDBPrefix}surveyChoice set vote = 0 where surveyID = ?", array($surveyID));
		return true;
	}
	/**
	 * Insert a new survey contains multi choices,presentations
	 * @param SurveyVO $survey an instance of SurveyVO
	 * @version 2.0
	 */
	private function insertSurvey(SurveyVO $survey)
	{
		global $gvDB, $gvDBPrefix;
		$sql="insert into {$gvDBPrefix}survey (pageID,question,answer,points) values (?,?,?,?)";
		$res=$gvDB->Prepare($sql);
		$paramSurvey = array(
			$survey->getPageID(),
			$survey->getQuestion(),
			$survey->getAnswer(),
			$survey->getPoints()
		);
		$gvDB->Execute($res,$paramSurvey);

		if ($survey->getNumOfChoices()>0)
		{
			// Get SurveyID from database.
			$sql = "select surveyID from {$gvDBPrefix}survey where question = ? and pageID = ? order by surveyid desc";
			$rsSurveyID = $gvDB->Execute($sql, array($survey->getQuestion(), $survey->getPageID()));
			$survey->setSurveyID($rsSurveyID->fields["surveyID"]);
			$rsSurveyID->Close();
			//Insert Choices begin
			$sql = "insert into {$gvDBPrefix}surveyChoice (surveyID, choiceID, choice, points) values (?,?,?,?)";
			$resChoice = $gvDB->Prepare($sql);
			$choiceID = 0;
			foreach($survey->getChoices() as $surveyChoice)
			{
				$choiceID++;
				$param = array(
					$survey->getSurveyID(),
					$choiceID,
					$surveyChoice->getChoice(),
					$this->evaluatePoints($choiceID,$survey->getNumOfChoices())
				);
				$gvDB->Execute($resChoice,$param);
			}
		}
		if ($survey->getNumOfPresentations()>0)
		{
			//Insert presentations begin
			$sql = "insert into {$gvDBPrefix}presentation (surveyID,presentationID,presentation,active)";
			$sql = $sql."values(?,?,?,?)";
			$resPre = $gvDB->Prepare($sql);
			$presentationID = 1;
			foreach($survey->getPresentations()as $presentation)
			{
				$gvDB->Execute($resPre,array(
					$survey->getSurveyID(),
					$presentationID,
					$presentation->getPresentation(),
					$presentation->getActive()
				));
				$presentationID++;
			}
		}
	}

	/**
	 * Delete a page which includes tables of page,
	 * Survey,SuveyChoice,Presentation,SurveyRecord.
	 * 
	 * @param $id id of a page
	 * @version 2.0
	 */
	function deletePage($id)
	{
		$this->deleteSurvey($id);
		global $gvDB, $gvDBPrefix;
		$gvDB->Execute("delete from {$gvDBPrefix}page where pageID = ?",array($id));
		return true;
	}
	/**
	 * Delete suveys in a page which includes the data items in
	 * Survey,SuveyChoice,Presentation,SurveyRecord.
	 * Page table would still be saved.
	 * 
	 * @param $is id of a wiki page
	 * @version 2.0
	 */
	function deleteSurveys($id)
	{
		global $gvDB, $gvDBPrefix;
		$sql = "select pageID from {$gvDBPrefix}page where pageID = ?";
		$rs = $gvDB->Execute ($sql, array($id));
		if ($rs->RecordCount() == 0)
			throw new SurveyException("No survey matches this question!",201);
		$gvDB->StartTrans();
		$id = $rs->fields[0];
		$rs->Close();
		$sql = "select surveyID from {$gvDBPrefix}survey where pageID = ?";
		$rs = $gvDB->Execute($sql, array($id));
		while (!$rs->EOF)
		{
			$surveyID = $rs->fields[0];
			$sql = "delete from {$gvDBPrefix}presentation where surveyID = ?";
			$gvDB->Execute($sql, array($surveyID));
			$sql = "delete from {$gvDBPrefix}surveychoice where surveyID = ?";
			$gvDB->Execute($sql, array($surveyID));
			$sql = "delete from {$gvDBPrefix}surveyrecord where surveyID = ?";
			$gvDB->Execute($sql, array($surveyID));
			$rs->MoveNext();
		}
		$sql = "delete from {$gvDBPrefix}survey where pageID = ?";
		$gvDB->Execute($sql, array($id));

		$gvDB->CompleteTrans();
		if ($gvDB->HasFailedTrans()) {
			$message = $gvDB->ErrorMsg();
			throw new Exception("ODBC Commit error:.$message");
		}
		return true;
	}
	/**
	 * private method. Using database record to fill in a SurveyVO.
	 *
	 * @param $pageID ID of wiki page.
	 * @return Array $surveys
	 * @version 2.0
	 */
	private function loadSurveys($page)
	{
		global $gvDB, $gvDBPrefix;
		$sql = "select * from {$gvDBPrefix}survey where pageID = ? order by surveyID";
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rsSurveys = &$gvDB->Execute($sql, array($page->getPageID()));
			
		$surveys = array();
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
				
			$choices = $this->getChoices($survey->getSurveyID());
			$survey->setChoices($choices);

			$presentations = $this->getPresentations($survey->getSurveyID());
			$survey->setPresentations($presentations);
				
			$surveys[]=$survey;
			$rsSurveys->MoveNext();
		}
		$rsSurveys->Close();
	
		return $surveys;
	}
	/**
	 * private functin. Get choices of a survey
	 *
	 * @param $surveyID
	 * @return array $choices
	 * @version 2.0
	 */
	private function getChoices($surveyID)
	{
		global $gvDB, $gvDBPrefix;
		$sql = "select * from {$gvDBPrefix}surveyChoice where surveyID=? order by choiceID";
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rsChoice = &$gvDB->Execute($sql, array($surveyID));
		
		$choices = array();
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
		return $choices;
	}
	/**
	 * Private function.Get presentations of a survey
	 *
	 * @param $surveyID
	 * @return array $presentations
	 * @version 2.0
	 */
	private function getPresentations($surveyID)
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
		return array(); //@todo implement this
		global $gvDB, $gvDBPrefix;
		$sqlRecord = "select * from {$gvDBPrefix}view_presentation_survey_mark where surveyid = ?";
		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$rsVote=&$gvDB->Execute($sqlRecord, array($surveyID));
		$marks = array();
		while(!$rsVote->EOF)
		{
			$marks[$rsVote->fields['presentationID']] = $rsVote->fields['marks'];
			$rsVote->MoveNext();
		}

		$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
		$sql = "select * from {$gvDBPrefix}presentation where surveyID = ? order by presentationID";
		$rsPresentation = &$gvDB->Execute($sql, array($surveyID));
		
		$presentations = array();

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
		
		return $presentations;
	}
	/**
	 * Calcuate the mark which choice gets
	 *
	 * @param $choiceID
	 * @param $numOfChoices
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
	 * @param $surveyID
	 * @param $presentationID
	 */
	function activatePresentation($surveyID,$presentationID)
	{
		global $gvDB, $gvDBPrefix;

		$gvDB->StartTrans();

		$sql = "update {$gvDBPrefix}presentation set active = 0 where surveyID = ?";
		$gvDB->Execute($sql, array($surveyID));
		$sql = "update {$gvDBPrefix}presentation set active = 1 where surveyID = ? and presentationID = ?";
		$gvDB->Execute($sql, array($surveyID, $presentationID));

		$gvDB->CompleteTrans();

		return true;
	}
	/**
	 * This is function is to start a survey.
	 * So, it requires the starting time of survey is set up before it is started.
	 * Basically, the following stages are used:
	 * @param $pageVO PageVO;
	 * @version 2.0
	 */
	function startSurvey(PageVO $pageVO)
	{
		global $gvDB, $gvDBPrefix;
		$startDate = date("Y-m-d H:i:s");
		$pageVO->setStartTime($startDate);

		$sql = "update {$gvDBPrefix}page set starttime = ?, endtime=? where pageID = ?";
		$gvDB->Execute($sql, array($pageVO->getStartTime(), $pageVO->getEndTime(), $pageVO->getPageID()));

		return true;
	}
	/**
	 * This is function is to continue a survey.
	 * It requires the time which the survey continue to run.
	 * @param $pageVO PageVO
	 * @version 2.0
	 */
	function continueSurvey(PageVO $pageVO)
	{
		global $gvDB, $gvDBPrefix; //@todo perhaps reduce the duration
		$duration= $pageVO->getDuration();
		$endTime=time()+$duration*60;
		$endDate = date("Y-m-d H:i:s",$endTime);
		$pageVO->setEndTime($endDate);

		$sql = "update {$gvDBPrefix}page set endtime = ? where pageID = ?";
		$gvDB->Execute($sql, array($pageVO->getEndTime(),$pageVO->getPageID()));
		return true;
	}
	/**
	 * It is different with starting a survey.
	 * Starting a survey requires we set up startint time before we are able to start it
	 * In finishing procedure, we automatically set current time as finishing time.
	 * @param $pageVO PageVO
	 * @version 2.0
	 */
	function finishSurvey(PageVO $pageVO)
	{
		global $gvDB, $gvDBPrefix;
		$expiredDate = date("Y-m-d H:i:s");
		$sqlChoice = "update {$gvDBPrefix}page set endtime = ? where pageID = ?";
		$gvDB->Execute($sqlChoice, array($expiredDate, $pageVO->getPageID()));
		return true;
	}
	/**
	 * Request telephone numbers .
	 * It requires the starting time of survey is set up before request.
	 * which represents the duration which the receivers are used is same with duration of survey
	 * Basically, the following stages are used:
	 * $surveyVO->setStartTime(now());
	 * $surveyDAO->requestReceivers($surveyVO);
	 * @param $page PageVO
	 */
	public function requestReceivers(PageVO $page)
	{
		$telephone = new Telephone();
		return $telephone->setupReceivers($page);
	}
}
?>