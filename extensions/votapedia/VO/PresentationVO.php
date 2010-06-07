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
    private $surveyID;
    private $presentationID = 0;
    private $presentation;
    private $active = 0;
    private $mark = 0;
    /**
     * set Survey ID of presentation
     *
     * @param $surveyID Integer
     */
    public function setSurveyID($surveyID)
    {
        $this->surveyID = $surveyID;
    }
    /**
     * set presentation ID
     *
     * @param $presentationID Integer
     */
    public function setPresentationID($presentationID)
    {
        $this->presentationID = $presentationID;
    }
    /**
     * Set presentation
     *
     * @param $presentation String
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
    }
    /**
     * Set whether the presentation is active
     *
     * @param $active Boolean
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
    /**
     * set how many points the presentation gets
     * @param $mark Integer
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }
    /**
     * @return Integer $surveyID
     */
    public function getSurveyID()
    {
        return $this->surveyID;
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
     * get the presentation content
     *
     * @return String $presentation
     */
    public function getPresentation()
    {
        return $this->presentation;
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
     * get how many marks this presentation gets
     *
     * @return Integer marks
     */
    public function getMark()
    {
        return $this->mark;
    }

}

