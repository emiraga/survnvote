<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

global $vpPath;
require_once("$vpPath/ChoiceVO.php");

/**
 * An value object of a survey.
 *
 * @author Bai Qifeng
 * @package ValueObject
 */
class SurveyVO
{
    private $pageID;
    private $surveyID;
    private $question;
    private $answer = 0;
    private $points = 0;
    private $choices = array();
    private $presentations = array();
    private $votesAllowed=1;

    //Reduntant info from PageVO
    private $surveyType = 1;

    /**
     * Set pageID
     * @param Integer $id
     */
    function setPageID($id)
    {
        $this->pageID = $id;
    }
    /**
     * Set an ID of this survey
     * @param Integer $id
     */
    function setSurveyID($id)
    {
        $this->surveyID = $id;
    }
    /**
     * Set question of this survey
     * @param String $question
     */
    function setQuestion($question)
    {
        $question = trim($question);
        if ($question)
            $this->question = $question;
        else
            throw new SurveyException("SurveyVO: Question cannot be a empty!",102);
    }
    /**
     * Set the answer of survey
     * 
     * @param Integer $answer
     */
    function setAnswer($answer)
    {
        if (preg_match("/^[0-9]{1,}$/", $answer))
        {
            $this->answer = $answer;
        }
        else
        {
            throw new SurveyException("SurveyVO: Answer of a question must be an integer");
        }
    }
    /**
     * Set the answer of survey
     * @param String $answer
     */
    function setAnswerByChoice($answer)
    {
        $choiceid = 1;
        foreach($this->choices as &$choice)
        {
            /* @var $choice ChoiceVO */
            if($answer == $choice->getChoice())
            {
                $this->answer = $choiceid;
                return ;
            }
            $choiceid++;
        }
        throw new SurveyException("SurveyVO: Answer of question choice not found.");
    }
    /**
     * Set points of the question/survey
     *
     * @param Integer $points
     */
    function setPoints($points)
    {
        if (preg_match("/^[0-9]{1,}$/", $points))
        {
            $this->points = $points;
        }
        else
            throw new SurveyException("SurveyVO: Points of a question must be an Integer!",101);
    }
    /**
     * Set multi choices of this survey
     *
     * @param Array $choices of ChoiceVO
     */
    function setChoices(array $choices)
    {
        $this->choices = $choices;
    }
    /**
     * Generate choices from the array of strings
     *
     * @param Array $values of strings
     */
    function generateChoices(array $values, $urldecode = false)
    {
        $choices = array();
        foreach($values as $value)
        {
            $value = trim($value);
            if($urldecode)
                $value = urldecode($value);
            if(strlen($value)<1)
                continue;
            $choice = new ChoiceVO();
            $choice->setSurveyID( $this->getSurveyID() );
            $choice->setPageID($this->getPageID());
            $choice->setChoice($value);
            $choice->setVote(0);
            $choice->setPoints(0);
            $choices[] = $choice;
        }
        $this->setChoices($choices);
    }
    /**
     * Set multiple presentations of this survey
     *
     * @param Array $presentations of PresentationVO
     */
    function setPresentations($presentations)
    {
        $this->presentations = $presentations;
    }
    /**
     * Set type of Survey
     *
     * @param Integer $surveyType
     */
    function setType($surveyType)
    {
        $this->surveyType = $surveyType;
    }
    /**
     * Set number of votes allowed per one user
     *
     * @param Integer $votesAllowed
     */
    function setVotesAllowed($votesAllowed)
    {
        $this->votesAllowed = $votesAllowed;
    }
    /**
     * Get page ID
     * 
     * @return Integer $pageID
     */
    function getPageID()
    {
        return $this->pageID;
    }
    /**
     * Get survey ID of this survey
     * 
     * @return Integer ID of the survey which contains this choice
     */
    function getSurveyID()
    {
        return $this->surveyID;
    }
    /**
     * get question of this survey
     * @return String quesion of this survey
     */
    function getQuestion()
    {
        return $this->question;
    }
    /**
     * Get the answer of survey
     *
     * @return Integer $answer
     */
    function getAnswer()
    {
        return $this->answer;
    }
    /**
     * get the points of this survey
     *
     * @return Integer $points
     */
    function getPoints()
    {
        return $this->points;
    }

    /**
     * @return Integer $type Type of survey
     */
    function getType()
    {
        return $this->surveyType;
    }
    /**
     * get mulit choices in this survey
     * @return Array $choices
     */
    function &getChoices()
    {
        return $this->choices;
    }
    /**
     * get the number of choices in this survey
     * @return Integer the number of choices included in this survey
     */
    function getNumOfChoices()
    {
        return count($this->choices);
    }
    /**
     * Get one choice in this survey based on ID of this choice.
     * 
     * @param Integer $i id of the choice which want to be retrieved
     * @return ChoiceVO a choice
     */
    function getChoiceByNum($i)
    {
        if(isset($this->choices[$i]))
            return $this->choices[$i];
        else
            return false;
    }
    /**
     * @param String $receiver telephone number of the receiver
     * @return Integer choiceID
     */
    /*function getChoiceIDByReceiver($receiver)
    {
        var_dump($this->choices);
        foreach($this->choices as $choice )
        {
            if ($choice->getReceiver() == $receiver)
                return $choice->getChoiceID();
        }
        return null;
    }*/
    /**
     * Get multi presentations in this survey.
     * 
     * @return Array of presentations in this survey
     */
    function getPresentations()
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
        if(isset($this->presentations[$i]))
            return $this->presentations[$i];
        else
            return false;
    }
    /**
     * @return Integer number of votes allowed
     */
    function getVotesAllowed()
    {
        return $this->votesAllowed;
    }
    /**
     * Get the current active presentation
     *
     * @return PresentationVO presentation
     */
    function getActivePresentationID()
    {
        foreach($this->presentations as $presentation)
        {
            if ($presentation->getActive() == '1' )
                return  $presentation->getPresentationID();
        }
        return 0;
    }
    /**
     * Convert to XML.
     * 
     * @return String
     */
    function toXML()
    {

        $xml="<survey>";
        $xml=$xml."<id>".$this->surveyID."</id>";
        $xml=$xml."<question>".str_ireplace('+',' ',urldecode($this->question))."</question>";
        $xml=$xml."<votesAllowed>".$this->votesAllowed."</votesAllowed>";

        $xml=$xml."<choices>";
        $votes  = 0;
        foreach($this->choices as $choice)
            $votes +=$choice->getVote();

        foreach($this->choices as $choice)
        {
            $xml=$xml."<choice>";

            $xml=$xml."<value>".str_ireplace('+',' ',urldecode(htmlspecialchars($choice->getChoice())))."</value>";
            /*
			 if ($choice->getSMS()=="none" || is_null($choice->getSMS()))
			 $xml=$xml."<sms></sms>";
			 else
			 $xml=$xml."<sms>$choice->getSMS()</sms>";
            */
            $xml=$xml."<receiver>".$choice->getReceiver()."</receiver>";
            if ($votes == 0)
                $xml=$xml."<vote>".$choice->getVote()."</vote>";
            else
                $xml=$xml."<vote>".round($choice->getVote()/$votes,2)."</vote>";
            $xml=$xml."</choice>";
        }
        $xml=$xml."</choices>";
        $xml=$xml."</survey>";
        return $xml;
    }
}

?>