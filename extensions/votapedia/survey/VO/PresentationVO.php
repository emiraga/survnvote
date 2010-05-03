<?php
if (!defined('MEDIAWIKI')) die();

/**
  * Value Object of Presentation
  *
  * @package ValueObject of survey
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
 	 * @param $surveyID
 	 */
 	public function setSurveyID($surveyID)
 	{
 		$this->surveyID = $surveyID;
 	}
 	/**
 	 * set presentation ID
 	 * 
 	 * @param $presentationID
 	 */
 	public function setPresentationID($presentationID)
 	{
 		$this->presentationID = $presentationID;
 	}
 	/**
 	 * set presentation
 	 * @param $presentation
 	 */
 	public function setPresentation($presentation)
 	{
 		$this->presentation = $presentation;
 	}
 	/**
 	 * set whether the presentation is active
 	 *
 	 * @param $active
 	 */
 	public function setActive($active)
 	{
 		$this->active = $active;
 	}
 	/**
 	 * set how many points the presentation gets
 	 * @param $mark
 	 */
 	public function setMark($mark)
 	{
 		$this->mark = $mark;
 	}
 	/**
 	 * @return integer $surveyID
 	 */
  	public function getSurveyID()
 	{
 		return $this->surveyID;
 	}
 	/**
 	 * get ID of presentation
 	 *
 	 * @return integer $presentationID
 	 */
 	public function getPresentationID()
 	{
 		return $this->presentationID;
 	}
 	/**
 	 * get the presentation content
 	 *
 	 * @return string $presentation
 	 */
 	public function getPresentation()
 	{
 		return $this->presentation;
 	}
 	/**
 	 * get whether the presentation is acitved
 	 * 
 	 * @return bool $active
 	 */
 	public function getActive()
 	{
 		if ($this->active)
		   		return '1';
			else
				return '0';
 	}
 	
 	/**
 	 * get how many marks this presentation gets
 	 *
 	 * @return integer marks
 	 */
 	public function getMark()
 	{
 		return $this->mark;
 	}
 	
}

?>