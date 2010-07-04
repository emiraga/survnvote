<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/** Include dependencies */
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
    /**
     * Set pageID
     * 
     * @param Integer $id
     */
    function setPageID($id)
    {
        $this->pageID = $id;
    }
    /**
     * Set an ID of this survey
     * 
     * @param Integer $id
     */
    function setSurveyID($id)
    {
        $this->surveyID = $id;
    }
    /**
     * Set question of this survey
     *
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
     * 
     * @param String $answer
     */
    function setAnswerByChoice($answer)
    {
        $choiceid = 1;
        foreach($this->choices as &$choice)
        {
            /* @var $choice ChoiceVO */
            if($answer == $choice->choice)
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
    function generateChoices(array &$values, $urldecode = false)
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
            $choice->surveyID = $this->getSurveyID();
            $choice->pageID = $this->getPageID();
            $choice->choice = $value;
            $choice->points = 0;
            $choices[] = $choice;
        }
        $this->setChoices($choices);
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
     * Get question of this survey
     * 
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
     * Get the points of this survey
     *
     * @return Integer $points
     */
    function getPoints()
    {
        return $this->points;
    }
    /**
     * Get multi choices in this survey
     *
     * @return Array $choices
     */
    function &getChoices()
    {
        return $this->choices;
    }
    /**
     * Get the number of choices in this survey
     *
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
     * Convert to XML.
     * 
     * @return String
     */
    function toXML()
    {

        $xml="<survey>";
        $xml=$xml."<id>".$this->surveyID."</id>";
        $xml=$xml."<question>".str_ireplace('+',' ',urldecode($this->question))."</question>";

        $xml=$xml."<choices>";
        $votes  = 0;
        foreach($this->choices as $choice)
            $votes +=$choice->getVote();

        foreach($this->choices as $choice)
        {
            $xml=$xml."<choice>";

            $xml=$xml."<value>".str_ireplace('+',' ',urldecode(htmlspecialchars($choice->choice)))."</value>";
            /*
			 if ($choice->getSMS()=="none" || is_null($choice->getSMS()))
			 $xml=$xml."<sms></sms>";
			 else
			 $xml=$xml."<sms>$choice->getSMS()</sms>";
            */
            $xml=$xml."<receiver>".$choice->receiver."</receiver>";
            if ($votes == 0)
                $xml=$xml."<vote>".$choice->numvotes."</vote>";
            else
                $xml=$xml."<vote>".round($choice->numvotes/$votes,2)."</vote>";
            $xml=$xml."</choice>";
        }
        $xml=$xml."</choices>";
        $xml=$xml."</survey>";
        return $xml;
    }
}

