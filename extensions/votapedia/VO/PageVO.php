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
 * Page 1-->1..* survey
 * survey 1-->1..* choice
 * survey 1-->0..* presentation.
 *
 * @package ValueObject
 */
class PageVO
{
    private $pageID;
    private $title;
    private $phone='000';
    private $author = "UnknownUser";
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
    private $surveys = array();
    private $privacy = 1;
    private $phonevoting = 'anon';
    private $webvoting = 'anon';

    function __construct()
    {
        $this->endTime = $this->renewEndTime();
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
        $this->endTime = $this->renewEndTime();
    }
    /**
     * Set end time of this survey
     * @param String $endTime
     */
    function setEndTime($endTime)
    {
        $this->endTime = $endTime;
        //$this->duration = $this->renewDuration();
    }
    /**
     * Set duration of this survey, must be Integer
     * @param Integer $duration
     */
    function setDuration($duration)
    {
        $this->duration = $duration;
        $this->endTime = $this->renewEndTime();
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
     * @param String $author
     */
    function setAuthor($author)
    {
        $this->author = trim($author);
    }
    /**
     * Set phone of this survey , which is used to activate/deactivate survey
     * @param String $phone
     */
    function setPhone($phone)
    {
        $this->phone = trim($phone);
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
     * @return String author of this survey
     */
    function getAuthor()
    {
        return $this->author;
    }
    /**
     * get phone of this survey
     * @return String phone of this survey
     */
    function getPhone()
    {
        return $this->phone;
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
     * @return String values of 'ready', 'active' or 'ended'
     */
    function getStatus()
    {
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
     * @return String new value of end time
     */
    private function renewEndTime()
    {
        $start=strtotime($this->startTime);
        if($start == false || $start == -1)
            return $this->startTime;
        else
            return vfDate($start + $this->duration*60);
    }
}
?>