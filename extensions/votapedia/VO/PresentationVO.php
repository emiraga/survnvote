<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/**
 * Value Object of Presentation
 *
 * @package ValueObject
 */
class PresentationVO
{
    private $pageID;
    private $presentationID = 0;
    private $name;
    private $active = 0;
    private $startTime = "2999-01-01 00:00:00";
    private $endTime = "2999-01-01 00:00:00";
    /**
     * set Page ID of presentation
     *
     * @param Integer $surveyID
     */
    public function setPageID($pageID)
    {
        $this->pageID = $pageID;
    }
    /**
     * set presentation ID
     *
     * @param Integer $presentationID
     */
    public function setPresentationID($presentationID)
    {
        $this->presentationID = $presentationID;
    }
    /**
     * Set presentation
     *
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * Set whether the presentation is active
     *
     * @param Boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
    /**
     * @return Integer $surveyID
     */
    public function getPageID()
    {
        return $this->pageID;
    }
    /**
     * get ID of presentation
     *
     * @return Integer $presentationID
     */
    public function getPresentationID()
    {
        return $this->presentationID;
    }
    /**
     * Get the presentation content
     *
     * @return String name
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * get whether the presentation is acitved
     *
     * @return Boolean $active
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * Set start time of this presentation, must match the required date format
     *
     * @param String $startTime yyyy-mm-dd hh:mm:ss
     */
    function setStartTime($startTime)
    {
        $this->startTime = $this->validateDate($startTime);
    }
    /**
     * Set end time of this presentation
     * @param String $endTime
     */
    function setEndTime($endTime)
    {
        $this->endTime = $this->validateDate($endTime);
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
}

