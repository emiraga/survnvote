<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/Common.php");
require_once("$vgPath/DAO/Telephone.php");
require_once("$vgPath/VO/PageVO.php");
require_once("$vgPath/DAO/PresentationDAO.php");
require_once("$vgPath/DAO/SurveyDAO.php");

/**
 * Class PageDAO is used to save/retreive data of a PageSurvey.
 * It contains Read/Create/Update/Delete and other relevant funtionalities
 *
 * @author Bai Qifeng
 * @author Emir Habul
 * @package DataAccessObject
 */
class PageDAO
{
    /**
     * Get Pages by SQL
     * 
     * @param String $where SQL where statement ex: "WHERE A = B"
     * @param Array $param
     * @param Boolean $loadSurveys
     * @param Boolean $loadPresentations
     * @return Array of PageVO
     */
    public function &getPages($where, $param, $loadSurveys = true, $loadPresentations = true)
    {
        global $vgDB, $vgDBPrefix;

        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $sql ="select * from {$vgDBPrefix}page $where";
        $rs= &$vgDB->Execute($sql, $param);
        $pages = array();
        while(!$rs->EOF)
        {
            $page = new PageVO();
            $page->setPageID(intval($rs->fields["pageID"]));
            $page->setTitle($rs->fields["title"]);
            $page->setStartTime($rs->fields["startTime"]);
            $page->setDuration($rs->fields["duration"]);
            $page->setEndTime($rs->fields["endTime"]);
            $page->setAuthor($rs->fields["author"]);
            $page->setCreateTime($rs->fields['createTime']);
            $page->setShowGraphEnd($rs->fields['showGraphEnd']);
            $page->setDisplayTop($rs->fields['displayTop']);
            $page->setVotesAllowed($rs->fields['votesAllowed']);
            $page->setType($rs->fields['surveyType']);
            $page->setSubtractWrong($rs->fields['subtractWrong']);
            $page->setSMSRequired( $rs->fields['smsRequired'] );
            $page->setPrivacy($rs->fields['privacy']);
            $page->setPhoneVoting($rs->fields['phonevoting']);
            $page->setWebVoting($rs->fields['webvoting']);
            $page->bgimage = $rs->fields['bgimage'];
            $page->crowdID = $rs->fields['crowdID'];
            if($loadSurveys)
            {
                $page->setSurveys( SurveyDAO::getFromPage($page->getPageID()));
            }
            if($loadPresentations)
            {
                $page->setPresentations( PresentationDAO::getFromPage($page->getPageID()) );
            }
            $pages[] = $page;
            $rs->MoveNext();
        }
        $rs->Close();
        return $pages;
    }
    /**
     * Execute query and return one result PageVO
     *
     * @param String $where where statement in SQL
     * @param Array $param ov values which will be included in sql query
     * @param Boolean $loadSurveys
     * @param Boolean $loadPresentations
     * @return PageVO on success, false in does not exist
     */
    private function getOnePage($where, $param, $loadSurveys = true, $loadPresentations = true)
    {
        global $vgDB, $vgDBPrefix;
        $pages =& $this->getPages($where, $param, $loadSurveys, $loadPresentations);
        if (count($pages)==0)
            return false;
        return $pages[0];
    }
    /**
     * Find surveys which are related with wiki page
     *
     * @param String $title title of wiki page
     * @return PageVO an Instance of PageVO
     */
    function findByTitle($title)
    {
        $page = $this->getOnePage("where title = ?", array($title));
        if(!$page)
            throw new SurveyException("Cannot find this survey.", 201);
        return $page;
    }
    /**
     * Find surveys which are related with wiki page
     *
     * @param Integer $id IntegerID of wiki page
     * @param Boolean $loadsurveys should we load all surveys
     * @param Boolean $loadPresentations
     * @return PageVO An instance of PageVO
     */
    function findByPageID($id, $loadsurveys = true, $loadPresentations = true)
    {
        $page = $this->getOnePage("where pageID = ?", array(intval($id)), $loadsurveys, $loadPresentations);
        if(!$page)
            throw new SurveyException("Cannot find this survey.", 201);
        return $page;
    }
    /**
     * Insert Page into database, optionally it includes
     * survey /surveys, choices,and presentations
     *
     * @param PageVO $pageVO
     * @param Boolean $insertSurveys should surveys be inserted as well
     */
    public function insertPage(PageVO &$pageVO, $insertSurveys = true, $insertPresentations = true)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->StartTrans();

        $sql = "insert into {$vgDBPrefix}page (title,author,startTime,duration,endTime,"
                ."smsRequired,showGraphEnd,surveyType,"
                ."displayTop,subtractWrong,privacy,phonevoting,webvoting,bgimage,crowdID)values"
                ."(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        //@todo some fields from page are missing
        $resPage = $vgDB->Prepare($sql);
        $param = array( $pageVO->getTitle(),
                $pageVO->getAuthor(),
                $pageVO->getStartTime(),
                $pageVO->getDuration(),
                $pageVO->getEndTime(),
                $pageVO->isSMSRequired(),
                $pageVO->getShowGraphEnd(),
                $pageVO->getType(),
                $pageVO->getDisplayTop(),
                $pageVO->getSubtractWrong(),
                $pageVO->getPrivacy(),
                $pageVO->getPhoneVoting(),
                $pageVO->getWebVoting(),
                $pageVO->bgimage,
                intval($pageVO->crowdID),
        );
        $vgDB->Execute($resPage,$param);
        $pageVO->setPageID($vgDB->Insert_ID());

        if($insertSurveys)
        {
            $surveys =& $pageVO->getSurveys();
            foreach ($surveys as &$survey)
            {
                $survey->setPageID($pageVO->getPageID());
                SurveyDAO::insert($survey);
            }
        }
        if($insertPresentations)
        {
            $presents =& $pageVO->getPresentations();
            foreach($presents as &$presentation)
            {
                /* @var $presentation PresentationVO */
                $presentation->setPageID($pageVO->getPageID());
                PresentationDAO::insert($presentation);
            }
        }
        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            throw new SurveyException("Error while inserting a page: ".$vgDB->ErrorMsg(), 400);
        }
    }
    /**
     * Update a page and
     * Insert survey(s) into database, which includes
     * survey /surveys, which survey type is Quiz),
     * choices,and presentations(if survey type is Presentation)
     *
     * @param PageVO $pageVO
     * @param Boolean $update_surveys
     * @param Boolean $update_presentations
     */
    public function updatePage(PageVO &$pageVO, $update_surveys = true, $update_presentations = true)
    {
        global $vgDB, $vgDBPrefix;
        // Check wether the page exists
        $pageID = $pageVO->getPageID();
        assert($pageID > 0);

        $vgDB->StartTrans();
        $sql = "update {$vgDBPrefix}page set title=?,startTime=?,duration=?,endTime=?,"
                . " smsRequired=?,"
                . " showGraphEnd=?,surveyType=?,displayTop=?,votesallowed=?,"
                . " subtractWrong=?,privacy=?, phonevoting=?, webvoting=?, bgimage=?, crowdID=?"
                . " where pageID = ?";
        $resPage = $vgDB->Prepare($sql);
        $param = array(
                $pageVO->getTitle(),
                $pageVO->getStartTime(),
                $pageVO->getDuration(),
                $pageVO->getEndTime(),
                $pageVO->isSMSRequired(),
                $pageVO->getShowGraphEnd(),
                $pageVO->getType(),
                $pageVO->getDisplayTop(),
                $pageVO->getVotesAllowed(),
                $pageVO->getSubtractWrong(),
                $pageVO->getPrivacy(),
                $pageVO->getPhoneVoting(),
                $pageVO->getWebVoting(),
                $pageVO->bgimage,
                $pageVO->crowdID,
                $pageID
        );
        //@todo some fields here are missing
        $vgDB->Execute($resPage,$param);

        if($update_surveys)
        {
            SurveyDAO::delete($pageID);

            $refsurveys =& $pageVO->getSurveys();
            foreach ($refsurveys as &$survey)
            {
                $survey->setPageID($pageID);
                SurveyDAO::insert($survey);
            }
        }
        
        if($update_presentations)
        {
            PresentationDAO::delete($pageID);

            $refpresents =& $pageVO->getPresentations();
            foreach($refpresents as &$presentation)
            {
                /* @var $presentation PresentationVO */
                $presentation->setPageID($pageID);
                PresentationDAO::insert($presentation);
            }
        }
        
        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            throw new SurveyException("Commit error: ".$vgDB->ErrorMsg(),400);
        }
        return true;
    }
    /**
     * This is function is to start a survey. It will set the start time in PageVO
     *
     * @param PageVO $pageVO
     */
    function startPageSurvey(PageVO &$pageVO)
    {
        global $vgDB, $vgDBPrefix;
        $startDate = vfDate();
        $pageVO->setStartTime($startDate);

        $sql = "update {$vgDBPrefix}page set starttime = ?, endtime = ?, receivers_released = 0 where pageID = ?";
        $vgDB->Execute($sql, array($pageVO->getStartTime(), $pageVO->getEndTime(), $pageVO->getPageID()));

        return true;
    }
    /**
     * In finishing procedure, we automatically set current time as finishing time.
     *
     * @param PageVO $pageVO
     */
    function stopPageSurvey(PageVO $pageVO)
    {
        global $vgDB, $vgDBPrefix;
        $expiredDate = vfDate(time()-1);
        $sqlChoice = "update {$vgDBPrefix}page set endtime = ? where pageID = ?";
        $vgDB->Execute($sqlChoice, array($expiredDate, $pageVO->getPageID()));
        return true;
    }
    /**
     * Prepare page for restart survey and save a history of survey runs.
     * Survey type must be survey or questionnaire or quiz.
     *
     * @param PageVO $page
     */
    function renewPageSurvey(PageVO $page)
    {
        if($page->getType() != vSIMPLE_SURVEY
                && $page->getType() != vQUESTIONNAIRE
                && $page->getType() != vQUIZ)
        {
            throw new SurveyESurveyException('Survey type must be survey or questionnaire or quiz');
        }
        $presid = $page->getCurrentPresentationID();

        //Save history of survey runs
        $pres = new PresentationVO();
        $pres->setActive(false);
        $pres->setStartTime($page->getStartTime());
        $pres->setEndTime($page->getEndTime());
        $pres->setPageID($page->getPageID());
        $pres->setName('Run # ' . $presid);
        $pres->setPresentationID($presid);

        $page->addPresentation($pres);
        $page->setStartTime("2999-01-01 00:00:00");
        $page->setEndTime('2999-01-01 00:00:00');
        //update page information
        $this->updatePage($page, false, true);
    }
    /**
     * Update database and set new receivers and SMS from the PageVO object.
     *
     * @param PageVO $page
     */
    public function updateReceiversSMS(PageVO &$page)
    {
        global $vgDB, $vgDBPrefix;
        $sqlChoice = "update {$vgDBPrefix}choice set receiver = ?, sms = ?, finished = 0 where surveyID = ? and choiceID = ?";
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
}

