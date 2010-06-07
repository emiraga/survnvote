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
     * @param Integer $surveyID
     */
    public function setSurveyID($surveyID)
    {
        $this->surveyID = $surveyID;
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
     * @param String $presentation
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
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
     * set how many points the presentation gets
     * @param Integer $mark
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

