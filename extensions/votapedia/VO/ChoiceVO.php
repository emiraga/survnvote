<?php
if (!defined('MEDIAWIKI')) die();
/**
 * This package contains all value objects.
 *
 * @package ValueObject
 */

/**
 * An value object of a choice which follows PHP MVC suggestion.
 * @author Bai Qifeng
 * @package ValueObject
 */
class ChoiceVO
{
    private $choiceID;
    private $surveyID;
    private $pageID;
    private $choice;
    private $receiver;
    private $SMS;
    private $points;
    
    /**
     * Get the choice content of this choice
     *
     * @return String choice content of this coice
     */
    public function getChoice()
    {
        return $this->choice;
    }
    /**
     * Get the choice ID of this choice
     * @return Integer the ID of this choice
     */
    public function getChoiceID()
    {
        return $this->choiceID;
    }
    /**
     * Get the survey ID which contains this choice
     * @return Integer the ID of a survey which contains this choice
     */
    public function getSurveyID()
    {
        return $this->surveyID;
    }
    /**
     * Get the page ID which contains this choice
     * @return Integer the ID of a page which contains this choice
     */
    public function getPageID()
    {
        return $this->pageID;
    }
    /**
     * Get the SMS content for this choice
     * The last two digitals of the receiver.
     * @return String SMS content which indicates this choice
     */
    public function getSMS()
    {
        return $this->SMS;
    }
    /**
     * Get the receiving telephone number for this choice.
     * 
     * @return String the receiving telephone number for this choice
     */
    public function getReceiver()
    {
        return $this->receiver;
    }
    /**
     * Get how many points this choice has
     * @return Integer the number of points
     */
    public function getPoints()
    {
        return $this->points;
    }
    /**
     * Set choice ID
     * @param Integer $choiceID
     */
    public function setChoiceID($choiceID)
    {
        $this->choiceID = $choiceID;
    }
    /**
     * Set choice content of this choice
     * 
     * @param String $choice
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;
    }
    /**
     * Set survey ID which includes this choice
     *
     * @param Integer $surveyID
     */
    public function setSurveyID($surveyID)
    {
        $this->surveyID = $surveyID;
    }
    /**
     * Set page ID which includes this survey with choice
     * @param Integer $pageID
     */
    public function setPageID($pageID)
    {
        $this->pageID = $pageID;
    }
    /**
     * Set receiving telepone number for this choice
     * @param String $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }
    /**
     * Set SMS for this choice.
     * 
     * @param String $SMS
     */
    public function setSMS($SMS)
    {
        $this->SMS = $SMS;
    }
    /**
     * Set points for this choice
     *
     * @param Integer $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }
}

