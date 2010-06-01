<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/Telephone.php");
require_once("$vgPath/VO/PageVO.php");
/**
 * This page includes class SurveyDAO which is used to
 * save/retreive data of a Survey. It contains Read/Create/Update/Delete
 * and other relevant funtionalities
 *
 * SurveyDAO includes functions which can access and set
 * (a) Survey(s)' info into or from database system.
 *
 * @author Bai Qifeng
 * @version 2.0 Beta
 */
class SurveyDAO
{
    private function getPages($where, $param, $loadSurveys = true)
    {
        global $vgDB, $vgDBPrefix;

        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $sql ="select * from {$vgDBPrefix}page $where";
        $rs= &$vgDB->Execute($sql, $param);
        $pages = array();
        while(!$rs->EOF)
        {
            $page = new PageVO();
            $page->setPageID($rs->fields["pageID"]);
            $page->setTitle($rs->fields["title"]);
            $page->setStartTime($rs->fields["startTime"]);
            $page->setDuration($rs->fields["duration"]);
            $page->setEndTime($rs->fields["endTime"]);
            $page->setAuthor(trim($rs->fields["author"]));
            $page->setCreateTime($rs->fields['createTime']);
            $page->setShowGraph($rs->fields['showGraph']);
            $page->setDisplayTop($rs->fields['displayTop']);
            $page->setVotesAllowed($rs->fields['votesAllowed']);
            $page->setType($rs->fields['surveyType']);
            $page->setSubtractWrong($rs->fields['subtractWrong']);
            $page->setPhone( $rs->fields['phone'] );
            $page->setSMSRequired( $rs->fields['smsRequired'] );
            $page->setPrivacy($rs->fields['privacy']);
            $page->setPhoneVoting($rs->fields['phonevoting']);
            $page->setWebVoting($rs->fields['webvoting']);
            if($loadSurveys)
                $page->setSurveys($this->loadSurveys($page));

            $pages[] = $page;
            $rs->MoveNext();
        }

        $rs->Close();
        return $pages;
    }
    /**
     * Execute query and return one result PageVO
     *
     * @param $where where statement in SQL
     * @param $param array ov values which will be included in sql query
     * @return PageVO on success, false in does not exist
     */
    private function getOnePage($where, $param, $loadSurveys = true)
    {
        global $vgDB, $vgDBPrefix;
        $pages = $this->getPages($where, $param, $loadSurveys);
        if (count($pages)==0)
            return false;
        return $pages[0];
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
            throw new SurveyException("Cannot find this survey.", 201);
        return $page;
    }
    /**
     * Find surveys which are related with wiki page
     *
     * @param $id IntegerID of wiki page
     * @param $loadsurveys Boolean should we load all surveys
     * @return PageVO $page An instance of PageVO
     * @version 2.0
     */
    function findByPageID($id, $loadsurveys = true)
    {
        $page = $this->getOnePage("where pageID = ?", array($id), $loadsurveys);
        if(!$page)
            throw new SurveyException("Cannot find this survey.", 201);
        return $page;
    }
    /**
     * Find a page object based on the vote from SMS
     * Assume that SMS is properly formated.
     * @param $sms String properly formated SMS voting string
     * @return PageVO PageVO object, or Boolean false if error.
     */
    function findPageBySMS($sms)
    {
        global $vgDB, $vgDBPrefix;
    }
    /**
     * Insert Page into database, optionally it includes
     * survey /surveys, choices,and presentations (if survey type is Presentation)
     *
     * @param $pageVO PageVO
     * @param $insertSurveys should surveys be inserted as well
     */
    public function insertPage(PageVO &$pageVO, $insertSurveys = true)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->StartTrans();

        $sql = "insert into {$vgDBPrefix}page (title,author,phone,startTime,duration,endTime,"
                ."smsRequired,showGraph,surveyType,"
                ."displayTop,subtractWrong,privacy,phonevoting,webvoting)values"
                ."(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        //@todo some fields from page are missing
        $resPage = $vgDB->Prepare($sql);
        $param = array( $pageVO->getTitle(),
                $pageVO->getAuthor(),
                $pageVO->getPhone(),
                $pageVO->getStartTime(),
                $pageVO->getDuration(),
                $pageVO->getEndTime(),
                $pageVO->isSMSRequired(),
                $pageVO->isShowGraph(),
                $pageVO->getType(),
                $pageVO->getDisplayTop(),
                $pageVO->isSubtractWrong(),
                $pageVO->getPrivacy(),
                $pageVO->getPhoneVoting(),
                $pageVO->getWebVoting(),
        );
        $vgDB->Execute($resPage,$param);
        $pageVO->setPageID($vgDB->Insert_ID());

        if($insertSurveys)
        {
            $surveys =& $pageVO->getSurveys();
            foreach ($surveys as &$survey)
            {
                $survey->setPageID($pageVO->getPageID());
                $this->insertSurvey($survey);
            }
        }
        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            throw new SurveyException("Erro while inserting a page: ".$vgDB->ErrorMsg(), 400);
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
        global $vgDB, $vgDBPrefix;
        // Check wether the page exists
        $pageID = $pageVO->getPageID();
        assert($pageID > 0);

        $vgDB->StartTrans();
        $sql = "update {$vgDBPrefix}page set title=?,startTime=?,duration=?,endTime=?,"
                . "smsRequired=?,"
                . " showGraph=?,surveyType=?,displayTop=?,votesallowed=?,"
                . "subtractWrong=?,privacy=?, phonevoting=?, webvoting=?"
                . "where pageID = ?";
        $resPage = $vgDB->Prepare($sql);
        $param = array(
                $pageVO->getTitle(),
                $pageVO->getStartTime(),
                $pageVO->getDuration(),
                $pageVO->getEndTime(),
                $pageVO->isSMSRequired(),
                $pageVO->isShowGraph(),
                $pageVO->getType(),
                $pageVO->getDisplayTop(),
                $pageVO->getVotesAllowed(),
                $pageVO->isSubtractWrong(),
                $pageVO->getPrivacy(),
                $pageVO->getPhoneVoting(),
                $pageVO->getWebVoting(),
                $pageID
        );
        //@todo some fields here are missing
        $vgDB->Execute($resPage,$param);

        $this->deleteSurveys($pageID);

        $refsurveys =& $pageVO->getSurveys();
        foreach ($refsurveys as &$survey)
        {
            $survey->setPageID($pageVO->getPageID());
            $this->insertSurvey($survey);
        }

        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            throw new SurveyException("ODBC Commit error: ".$vgDB->ErrorMsg(),400);
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
        global $vgDB, $vgDBPrefix;
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $rsSurveys = &$vgDB->Execute($sql, $params);

        $surveys = array();
        while(!$rsSurveys->EOF)
        {
            $survey = new SurveyVO();

            $survey->setPageID($rsSurveys->fields["pageID"]);
            $survey->setSurveyID($rsSurveys->fields["surveyID"]);
            $survey->setQuestion(trim($rsSurveys->fields["question"]));
            $survey->setAnswer(trim($rsSurveys->fields["answer"]));
            $survey->setPoints($rsSurveys->fields["points"]);
            //Redundant info from PageVO
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
     * 
     * @param $id an ID which want to be retreived
     * @return SurveyVO $survey a survey which matches ID
     */
    function findSurveyByID($id)
    {
        $surveys = $this->getSurveysSQL("select * from {$vgDBPrefix}survey where surveyID = ?", array($surveyID));
        if(count($surveys) == 0)
            throw new SurveyException("Survey not found", 400);
        return $surveys[0];
    }
    /**
     *
     * @param $surveyid
     * @param $presentationid
     * @return SurveyVO
     */
    function getActiveSurveyById($surveyid, $presentationid = 0)
    {
        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $sql = "select * from {$vgDBPrefix}survey join {$vgDBPrefix}page "
        ."on {$vgDBPrefix}survey.pageID = {$vgDBPrefix}page.pageID "
        ."where {$vgDBPrefix}survey.surveyID = ? and "
        ."{$vgDBPrefix}page.startTime <= '$now' and {$vgDBPrefix}page.endTime <= '$now'";

        $surveys = $this->getSurveysSQL($sql, array($surveyID));
        if(count($surveys) == 0)
            throw new SurveyException("Active survey not found", 400);
        return $surveys[0];
    }
    /**
     * Get an array of current surveys
     *
     * @param $num number of surveys required, can be empty for all surveys
     */
    function findCurrentSurveys($num = NULL)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $now = vfDate();
        $sql = "select pageID from {$vgDBPrefix}page where starttime <= '$now' and endtime >= '$now' and surveytype = 1 order by starttime desc";
        $param = array();
        if($num)
        {
            $sql .= " limit ?";
            $param = array($num);
        }
        $rs = &$vgDB->Execute($sql, $param);
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
            $survey = $this->getSurveysSQL("select * from {$vgDBPrefix}survey where pageID = ?", $id);
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
        global $vgDB, $vgDBPrefix;
        $res1= $vgDB->Prepare("delete from {$vgDBPrefix}surveyRecord where surveyID = ?");
        $res2= $vgDB->Prepare("update {$vgDBPrefix}surveyChoice set vote = 0 where surveyID = ?");

        foreach($surveys as $survey)
        {
            $surveyID = $survey->getSurveyID();
            $vgDB->Execute($res1,array($surveyID));
            $vgDB->Execute($res2,array($surveyID));
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
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("delete from {$vgDBPrefix}surveyRecord where surveyID = ?", array($surveyID));
        $vgDB->Execute("update {$vgDBPrefix}surveyChoice set vote = 0 where surveyID = ?", array($surveyID));
        return true;
    }
    /**
     * Insert a new survey contains multi choices,presentations
     *
     * @param $survey SurveyVO an instance of SurveyVO
     * @version 2.0
     */
    private function insertSurvey(SurveyVO &$survey)
    {
        global $vgDB, $vgDBPrefix;
        $sql="insert into {$vgDBPrefix}survey (pageID,question,answer,points) values (?,?,?,?)";
        $res=$vgDB->Prepare($sql);
        $paramSurvey = array(
                $survey->getPageID(),
                $survey->getQuestion(),
                $survey->getAnswer(),
                $survey->getPoints()
        );
        $vgDB->Execute($res,$paramSurvey);
        $survey->setSurveyID( $vgDB->Insert_ID() );

        if ($survey->getNumOfChoices() > 0)
        {
            //Insert Choices begin
            $sql = "insert into {$vgDBPrefix}surveyChoice (surveyID, choiceID, choice, points) values (?,?,?,?)";
            $resChoice = $vgDB->Prepare($sql);
            $choiceID = 0;
            $choices =& $survey->getChoices();
            foreach($choices as &$surveyChoice)
            {
                $choiceID++;
                $param = array(
                        $survey->getSurveyID(),
                        $choiceID,
                        $surveyChoice->getChoice(),
                        $this->evaluatePoints($choiceID,$survey->getNumOfChoices())
                );
                $vgDB->Execute($resChoice,$param);

                $surveyChoice->setChoiceID( $choiceID );
            }
        }
        if ($survey->getNumOfPresentations()>0)
        {
            //Insert presentations begin
            $sql = "insert into {$vgDBPrefix}presentation (surveyID,presentationID,presentation,active)";
            $sql = $sql."values(?,?,?,?)";
            $resPre = $vgDB->Prepare($sql);
            $presentationID = 0;
            $presentations =& $survey->getPresentations();
            foreach($presentations as &$presentation)
            {
                $presentationID++;
                $vgDB->Execute($resPre,array(
                        $survey->getSurveyID(),
                        $presentationID,
                        $presentation->getPresentation(),
                        $presentation->getActive()
                ));
                $presentation->SetPresentationID( $vgDB->Insert_ID() );
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
        global $vgDB, $vgDBPrefix;
        $vgDB->Execute("delete from {$vgDBPrefix}page where pageID = ?",array($id));
        return true;
    }
    /**
     * Delete suveys in a page which includes the data items in
     * Survey,SuveyChoice,Presentation,SurveyRecord.
     * Page table would still be saved.
     *
     * @param $id id of a wiki page
     */
    function deleteSurveys($id)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->StartTrans();
        $sql = "select surveyID from {$vgDBPrefix}survey where pageID = ?";
        $rs = $vgDB->Execute($sql, array($id));
        while (!$rs->EOF)
        {
            $surveyID = $rs->fields['surveyID'];
            $sql = "delete from {$vgDBPrefix}presentation where surveyID = ?";
            $vgDB->Execute($sql, array($surveyID));
            $sql = "delete from {$vgDBPrefix}surveychoice where surveyID = ?";
            $vgDB->Execute($sql, array($surveyID));
            $sql = "delete from {$vgDBPrefix}surveyrecord where surveyID = ?";
            $vgDB->Execute($sql, array($surveyID));
            $rs->MoveNext();
        }
        $sql = "delete from {$vgDBPrefix}survey where pageID = ?";
        $vgDB->Execute($sql, array($id));

        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            $message = $vgDB->ErrorMsg();
            throw new Exception("Commit error: $message");
        }
        return true;
    }
    /**
     * private method. Using database record to fill in a SurveyVO.
     *
     * @param $page PageVO page object
     * @return Array $surveys
     * @version 2.0
     */
    private function loadSurveys(&$page)
    {
        global $vgDB, $vgDBPrefix;
        $sql = "select * from {$vgDBPrefix}survey where pageID = ? order by surveyID";
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $rsSurveys = &$vgDB->Execute($sql, array($page->getPageID()));

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
        global $vgDB, $vgDBPrefix;
        $sql = "select * from {$vgDBPrefix}surveyChoice where surveyID=? order by choiceID";
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $rsChoice = &$vgDB->Execute($sql, array($surveyID));

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
        global $vgDB, $vgDBPrefix;
        $sqlRecord = "select * from {$vgDBPrefix}view_presentation_survey_mark where surveyid = ?";
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $rsVote=&$vgDB->Execute($sqlRecord, array($surveyID));
        $marks = array();
        while(!$rsVote->EOF)
        {
            $marks[$rsVote->fields['presentationID']] = $rsVote->fields['marks'];
            $rsVote->MoveNext();
        }

        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $sql = "select * from {$vgDBPrefix}presentation where surveyID = ? order by presentationID";
        $rsPresentation = &$vgDB->Execute($sql, array($surveyID));

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
     * @param $numberOfChoices
     */
    private function evaluatePoints($choiceID, $numberOfChoices)
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
        global $vgDB, $vgDBPrefix;

        $vgDB->StartTrans();

        $sql = "update {$vgDBPrefix}presentation set active = 0 where surveyID = ?";
        $vgDB->Execute($sql, array($surveyID));
        $sql = "update {$vgDBPrefix}presentation set active = 1 where surveyID = ? and presentationID = ?";
        $vgDB->Execute($sql, array($surveyID, $presentationID));

        $vgDB->CompleteTrans();

        return true;
    }
    /**
     * This is function is to start a survey. It will set the start time in PageVO
     *
     * @param $pageVO PageVO;
     * @version 2.0
     */
    function startSurvey(PageVO &$pageVO)
    {
        global $vgDB, $vgDBPrefix;
        $startDate = vfDate();
        $pageVO->setStartTime($startDate);

        $sql = "update {$vgDBPrefix}page set starttime = ?, endtime = ? where pageID = ?";
        $vgDB->Execute($sql, array($pageVO->getStartTime(), $pageVO->getEndTime(), $pageVO->getPageID()));

        return true;
    }
    /**
     * This is function is to continue a survey.
     * It requires the time which the survey continue to run.
     *
     * @param $pageVO PageVO
     * @version 2.0
     */
    function continueSurvey(PageVO $pageVO)
    {
        global $vgDB, $vgDBPrefix; //@todo perhaps reduce the duration
        $duration= $pageVO->getDuration();
        $endTime=time()+$duration*60;
        $endDate = vfDate($endTime);
        $pageVO->setEndTime($endDate);

        $sql = "update {$vgDBPrefix}page set endtime = ? where pageID = ?";
        $vgDB->Execute($sql, array($pageVO->getEndTime(),$pageVO->getPageID()));
        return true;
    }
    /**
     * In finishing procedure, we automatically set current time as finishing time.
     *
     * @param $pageVO PageVO
     */
    function stopSurvey(PageVO $pageVO)
    {
        global $vgDB, $vgDBPrefix;
        $expiredDate = vfDate(time()-1);
        $sqlChoice = "update {$vgDBPrefix}page set endtime = ? where pageID = ?";
        $vgDB->Execute($sqlChoice, array($expiredDate, $pageVO->getPageID()));
        return true;
    }
    /**
     * Request telephone numbers .
     * It requires the starting time of survey is set up before request.
     * which represents the duration which the receivers are used is same with duration of survey
     *
     * @param $page PageVO
     */
    public function requestReceivers(PageVO &$page)
    {
        $this->releaseReceivers(); //try to delete unused receivers if any

        $telephone = new Telephone();
        return $telephone->setupReceivers($page);
    }
    /**
     * Update database and set new receivers and SMS from the PageVo object
     *
     * @param PageVO $page
     */
    public function updateReceiversSMS(PageVO &$page)
    {
        global $vgDB, $vgDBPrefix;
        $sqlChoice = "update {$vgDBPrefix}surveychoice set receiver = ?, sms = ? where surveyID = ? and choiceID = ?";
        $resChoice = $vgDB->Prepare($sqlChoice);
        $surveys = &$page->getSurveys();
        foreach($surveys as &$survey)
        {
            $surveyChoices =& $survey->getChoices();
            foreach($surveyChoices as &$surveyChoice)
            {
                $param = array(
                        $surveyChoice->getReceiver(),
                        $surveyChoice->getSMS(),
                        $surveyChoice->getSurveyID(),
                        $surveyChoice->getChoiceID(),
                );
                $vgDB->Execute($resChoice, $param);
            }
        }
    }
    /**
     * Release receivers which have not been released this far.
     * Find and mark as receivers pages (surveys) which have expired.
     *
     * @return Array array of integers specifying pageID of pages which have been finalized
     */
    public function releaseReceivers()
    {
        $telephone = new Telephone();

        global $vgDB, $vgDBPrefix;
        $now = vfDate();
        $pages = $this->getPages("WHERE endTime <= ? and receivers_released = 0", array($now));

        $ret = array();
        foreach ($pages as $page)
        {
            /* @var $page PageVO */
            $telephone->deleteReceivers($page);
            $vgDB->Execute("UPDATE {$vgDBPrefix}page SET receivers_released = 1 WHERE pageID = ?", array($page->getPageID()));
            $ret[] = $page->getPageID();
        }
        return $ret;
    }
}
