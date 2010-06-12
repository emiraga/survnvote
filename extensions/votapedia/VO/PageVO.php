<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/** Include dependencies */
global $vpPath;
require_once("$vpPath/SurveyVO.php");

/**
 * Value Object of Page which contains survey array etc.
 *
 * Page 1-->0..* PresentationVO
 * Page 1-->1..* SurveyVO
 * SurveyVO 1-->1..* ChoiceVO
 *
 * @package ValueObject
 */
class PageVO
{
    private $pageID;
    private $title;
    private $author = 0;
    private $startTime = "2999-01-01 00:00:00";
    private $endTime;
    private $duration = 60;
    private $createTime;
    private $smsRequired = 0;
    private $showGraphEnd = true;
    private $surveyType = 1;
    private $displayTop = 0;
    private $votesAllowed = 1;
    private $subtractWrong = 0;
    private $privacy = 1;
    private $phonevoting = 'anon';
    private $webvoting = 'anon';
    private $surveys = array();
    private $presentations = array();

    /**
     * Construct PageVO
     */
    function __construct()
    {
        $this->endTime = $this->renewEndTime($this->duration);
        $this->setCreateTime(vfDate());
    }
    /**
     * Set an ID of this survey
     * @param Integer $id
     */
    function setPageID($id)
    {
        $this->pageID = $id;
    }
    /**
     * Set question of this survey
     *
     * @param String $title
     */
    function setTitle($title)
    {
        $title = trim($title);
        if ($title)
            $this->title = $title;
        else
            throw new SurveyException("PageVO: Question cannot be a empty!",102);
    }
    /**
     * Set start time of this survey, must match the required date format
     *
     * @param String $startTime yyyy-mm-dd hh:mm:ss
     */
    function setStartTime($startTime)
    {
        $this->startTime = $this->validateDate($startTime);
        $this->endTime = $this->renewEndTime($this->duration);
    }
    /**
     * Set end time of this survey
     * @param String $endTime
     */
    function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }
    /**
     * Set duration of this survey, must be Integer
     * @param Integer $duration
     * @param Boolean $checkEndNow check if this will cause survey to stop!
     * @return Boolean result of the checking for now < endTime
     */
    function setDuration($duration, $checkEndNow = false)
    {
        $newEndTime = $this->renewEndTime($duration);
        if($checkEndNow)
        {
            $now = vfDate();
            if( $newEndTime <= $now )
                return false;
        }
        $this->duration = $duration;
        $this->endTime = $newEndTime;
        return true;
    }
    /**
     * get created time of this survey
     * @param String $createTime create time of this survey
     */
    function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }
    /**
     * Set author of this survey
     * @param Integer $author
     */
    function setAuthor($author)
    {
        $this->author = trim($author);
    }
    /**
     * set whether the suvey needs to sms back to the voters
     * @param Boolean $smsRequired
     */
    function setSMSRequired($smsRequired)
    {
        $this->smsRequired = $smsRequired;
    }
    /**
     * set whether allow graph is shown in voting
     * @param Boolean $showGraph
     */
    function setShowGraphEnd($showGraph)
    {
        $this->showGraphEnd = (bool) $showGraph;
    }
    /**
     * Set Top n presentations would be displayed
     *
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
     * @param Integer $surveyType
     */
    function setType($surveyType)
    {
        $this->surveyType = $surveyType;
    }
    /**
     * Set multi choices of this survey
     *
     * @param Array $surveys
     */
    function setSurveys(array $surveys)
    {
        $this->surveys = $surveys;
    }
    /**
     * Subtract wrong answers from points
     * @param Boolean $subtractWrong
     */
    function setSubtractWrong($subtractWrong)
    {
        $this->subtractWrong = $subtractWrong;
    }
    /**
     * Privacy level of this Page
     *
     * @param Integer $privacy
     */
    function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }
    /**
     * Privacy level of this Page
     *
     * @param Integer $privacy
     */
    function setPrivacyByName($privacy)
    {
        if($privacy == 'low')
            $this->privacy = vPRIVACY_LOW;
        elseif($privacy == 'medium')
            $this->privacy = vPRIVACY_MEDIUM;
        elseif($privacy == 'high')
            $this->privacy = vPRIVACY_HIGH;
        else
            throw new SurveyException('setPrivacyByName: Wrong privacy level');
    }
    /**
     * Set the voting options, 'anon', 'yes' or 'no'
     *
     * @param String $vote
     */
    function setPhoneVoting($vote)
    {
        $this->phonevoting = $vote;
    }
    /**
     * Set the voting options, 'anon', 'yes' or 'no'
     *
     * @param String $vote
     */
    function setWebVoting($vote)
    {
        $this->webvoting = $vote;
    }
    /**
     * get survey ID of this survey
     * @return Integer ID of the survey which contains this choice
     */
    function getPageID()
    {
        return $this->pageID;
    }
    /**
     * get question of this survey
     * @return String quesion of this survey
     */
    function getTitle()
    {
        return $this->title;
    }
    /**
     * get starting time of this survey
     * @return String start time of this survey
     */
    function getStartTime()
    {
        return $this->startTime;
    }
    /**
     * get finishing time of this survey
     * @return String finishing time of this survey
     */
    function getEndTime()
    {
        return $this->endTime;
    }
    /**
     * get duration of this survey
     * @return Integer duration of this survey
     */
    function getDuration()
    {
        return $this->duration;
    }
    /**
     * get author of this survey
     * @return Integer author of this survey
     */
    function getAuthor()
    {
        return $this->author;
    }
    /**
     * get created time of this survey
     * @return String created time of this survey
     */
    function getCreateTime()
    {
        return $this->createTime;
    }
    /**
     * Check wether the survey needs to sms back to voters
     * @return Boolean
     *
     */
    function isSMSRequired()
    {
        return $this->smsRequired;
    }
    /**
     * Check wether the survey allows to show graph in voting
     * @return Boolean
     *
     */
    function getShowGraphEnd()
    {
        return $this->showGraphEnd;
    }
    /**
     * Should incorrect answers be subtracted from points
     * @return Boolean
     */
    function getSubtractWrong()
    {
        return $this->subtractWrong;
    }
    /**
     * @return Integer $type Type of survey
     */
    function getType()
    {
        return $this->surveyType;
    }
    /**
     * @return Integer $type Type of survey
     */
    function getTypeName()
    {
        switch($this->surveyType)
        {
            case vSIMPLE_SURVEY:      return vtagSIMPLE_SURVEY;
            case vQUESTIONNAIRE:      return vtagQUESTIONNAIRE;
            case vQUIZ:               return vtagQUIZ;
            case vRANK_EXPOSITIONS:   return vtagRANK_EXPOSITIONS;
            case vTEXT_RESPONSE:      return vtagTEXT_RESPONSE;
        }
        throw new SurveyException("Unknown survey type");
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
     * Get privacy level of this Page
     * @return Integer
     */
    function getPrivacy()
    {
        return $this->privacy;
    }
    /**
     * Get privacy level of this Page
     *
     * @return String privacy
     */
    function getPrivacyByName()
    {
        if($this->privacy == vPRIVACY_LOW)
            return 'low';
        elseif($this->privacy == vPRIVACY_MEDIUM)
            return 'medium';
        elseif($this->privacy == vPRIVACY_HIGH)
            return 'high';
        else
            throw new SurveyException('getPrivacyByName: Wrong privacy level');
    }
    /**
     * Get the voting options, 'anon', 'yes' or 'no'
     *
     * @return String vote
     */
    function getPhoneVoting()
    {
        return $this->phonevoting;
    }
    /**
     * Get the voting options, 'anon', 'yes' or 'no'
     *
     * @return String vote
     */
    function getWebVoting()
    {
        return $this->webvoting;
    }
    /**
     * get mulit choices in this survey
     * @return Array of SurveyVO
     */
    function &getSurveys()
    {
        return $this->surveys;
    }
    /**
     * get the number of choices in this survey
     * @return Integer the number of choices included in this survey
     */
    function getNumOfSurveys()
    {
        return count($this->surveys);
    }
    /**
     * get one survey by its surveyID
     * @param Integer $id id of the survey
     * @return SurveyVO $surveyVO
     */
    function getSurveyBySurveyID($id)
    {
        foreach($this->surveys as $survey)
        {
            if ($survey->getSurveyID()== $id)
                return $survey;
        }
        throw new Exception("No such survey by ID");
    }
    /**
     * Validate whether matchs the requried data format
     * @param String $date date
     * @return String date if true, tigger a error if false
     */
    function validateDate($date)
    {
        if (preg_match("/^[0-9]{4}\-[0|1][0-9]\-[0-3][0-9]\040[0-9]{2}\:[0-9]{2}:[0-9]{2}/", $date))
            return $date;
        else
            throw new SurveyException("Date/Time must follow yyyy-mm-dd hh:mm:ss format!",100);
    }
    /**
     * Get the status of the survey/page
     *
     * @param Integer $presID for which presentation are you asking for status?
     * @return String values of 'ready', 'active' or 'ended'
     */
    function getStatus($presID)
    {
        if( $this->getCurrentPresentationID() != $presID )
            return 'ended';
        
        $starttime = strtotime ($this->getStartTime());
        $endtime = strtotime ($this->getEndTime());
        $now = time();

        if ($endtime == false || $endtime == -1 || $starttime == false || $starttime == -1)
            return 'ready';
        else if ($starttime <= $now && $now < $endtime)
            return 'active';
        else if ($endtime <= $now)
            return 'ended';
        else
            return 'ready';
    }
    /**
     * Recompute the end time based in the startTime and duration.
     *
     * If current unix time cannot fit into an integer,
     * end time will be equal to start time.
     *
     * @param Integer $duration in minutes duration of survey
     * @return String new value of end time
     */
    private function renewEndTime($duration)
    {
        $start=strtotime($this->startTime);
        if($start == false || $start == -1)
            return $this->startTime;
        else
            return vfDate($start + $duration*60);
    }
    /**
     * Set multiple presentations of this page
     *
     * @param Array $presentations of PresentationVO
     */
    function setPresentations(&$presentations)
    {
        $this->presentations =& $presentations;
    }
    /**
     * Add new presentation to this page.
     *
     * @param PresentationVO $presentation
     */
    function addPresentation(PresentationVO $presentation)
    {
        $this->presentations[] = $presentation;
    }
    /**
     * Get multi presentations in this survey.
     *
     * @return Array of presentations in this survey
     */
    function &getPresentations()
    {
        return $this->presentations;
    }
    /**
     * Get the number of presentations in this survey.
     *
     * @return Integer the number of presentations included in this survey
     */
    function getNumOfPresentations()
    {
        return count($this->presentations);
    }
    /**
     * Get one choice in this survey based on ID of this choice.
     *
     * @param Integer $i id of the choice which want to be retrieved
     * @return PresentationVO a presentation
     */
    function getPresentationByNum($i)
    {
        if(isset($this->presentations[$i-1]))
            return $this->presentations[$i-1];
        else
            return false;
    }
    /**
     * Get the current active presentation
     *
     * @return PresentationVO presentation
     */
    /*function getActivePresentationID()
    {
        foreach($this->presentations as $presentation)
        {
            if ($presentation->getActive())
                return  $presentation->getPresentationID();
        }
        return 0;
    }*/
    /**
     * Should this page be allowed to be edited.
     *
     * @param Integer $presID
     * @return Boolean
     */
    function isEditable($presID)
    {
        return ($this->getStatus($presID) == 'ready') && ($this->getNumOfPresentations() == 0);
    }
    /**
     * Get current presentationID.
     * Used only for surveys, questionnaires and quizes.
     *
     * @return Integer
     */
    function getCurrentPresentationID()
    {
        if($this->getType() != vSIMPLE_SURVEY
                && $this->getType() != vQUESTIONNAIRE
                && $this->getType() != vQUIZ)
        {
            throw new SurveyESurveyException('Survey type must be survey or questionnaire or quiz');
        }
        return $this->getNumOfPresentations() + 1;
    }
}

