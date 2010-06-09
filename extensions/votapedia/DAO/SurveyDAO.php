<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/**
 * Description of SurveyDAO
 *
 * @author Emir Habul
 * @package DataAccessObject
 */
class SurveyDAO {
    /**
     * Insert a new survey contains multi choices, presentations
     *
     * @param SurveyVO $survey an instance of SurveyVO
     */
    static function insert(SurveyVO &$survey)
    {
        global $vgDB, $vgDBPrefix;
        $sql="insert into {$vgDBPrefix}survey (pageID, question, answer, points) values (?,?,?,?)";
        $res=$vgDB->Prepare($sql);
        $paramSurvey = array(
                $survey->getPageID(),
                $survey->getQuestion(),
                $survey->getAnswer(),
                $survey->getPoints()
        );
        $vgDB->Execute($res,$paramSurvey);
        $survey->setSurveyID( $vgDB->Insert_ID() );

        if ($survey->getNumOfChoices() > 0)
        {
            //Insert Choices begin
            $sql = "insert into {$vgDBPrefix}surveychoice (pageID, surveyID, choiceID, choice, points) values (?,?,?,?,?)";
            $resChoice = $vgDB->Prepare($sql);
            $choiceID = 0;
            $choices =& $survey->getChoices();
            foreach($choices as &$surveyChoice)
            {
                $choiceID++;
                $param = array(
                        $survey->getPageID(),
                        $survey->getSurveyID(),
                        $choiceID,
                        $surveyChoice->getChoice(),
                        SurveyDAO::evaluatePoints($choiceID,$survey->getNumOfChoices())
                );
                $vgDB->Execute($resChoice,$param);

                $surveyChoice->setChoiceID( $choiceID );
            }
        }
    }
    /**
     * Delete suveys in a page which includes the data items in
     * Survey and SuveyChoice.
     *
     * @param Integer $pageID id of a page
     */
    static function delete($pageID)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->StartTrans();

        $sql = "delete from {$vgDBPrefix}surveychoice where pageID = ?";
        $vgDB->Execute($sql, array($pageID));

        $sql = "delete from {$vgDBPrefix}survey where pageID = ?";
        $vgDB->Execute($sql, array($pageID));

        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            $message = $vgDB->ErrorMsg();
            throw new Exception("Commit error: $message");
        }
    }
    /**
     * Using database record to fill in a SurveyVO.
     *
     * @param Integer $pageID id of page
     * @return Array $surveys
     */
    static function &getFromPage($pageID, $load_choices = true)
    {
        global $vgDB, $vgDBPrefix;
        $sql = "select * from {$vgDBPrefix}survey where pageID = ? order by surveyID";
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $rsSurveys = &$vgDB->Execute($sql, array($pageID));

        $surveys = array();
        while(!$rsSurveys->EOF)
        {
            $survey = new SurveyVO();

            $survey->setPageID($rsSurveys->fields["pageID"]);
            $survey->setSurveyID($rsSurveys->fields["surveyID"]);
            $survey->setQuestion(trim($rsSurveys->fields["question"]));
            $survey->setAnswer(trim($rsSurveys->fields["answer"]));
            $survey->setPoints($rsSurveys->fields["points"]);

            if($load_choices)
            {
                $choices =& SurveyDAO::getChoices($survey->getSurveyID(), $survey->getPageID());
                $survey->setChoices($choices);
            }

            $surveys[] = $survey;
            $rsSurveys->MoveNext();
        }
        $rsSurveys->Close();

        return $surveys;
    }
    /**
     * Get choices of a survey.
     *
     * @param Integer $surveyID
     * @param Integer $pageID
     * @return Array choices
     */
    static function &getChoices($surveyID, $pageID)
    {
        global $vgDB, $vgDBPrefix;
        $sql = "select * from {$vgDBPrefix}surveyChoice where surveyID=? and pageID=? order by choiceID";
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $rsChoice = &$vgDB->Execute($sql, array($surveyID, $pageID));

        $choices = array();
        while(!$rsChoice->EOF)
        {
            //Access by name, some database may not support this
            //small case
            $choice = new ChoiceVO();
            $choice->setSurveyID($rsChoice->fields['surveyID']);
            $choice->setPageID($rsChoice->fields['pageID']);
            $choice->setChoiceID($rsChoice->fields['choiceID']);
            $choice->setChoice(trim($rsChoice->fields['choice']));
            $choice->setReceiver(trim($rsChoice->fields['receiver']));
            $choice->setSMS(trim($rsChoice->fields['SMS']));
            $choice->setPoints($rsChoice->fields['points']);

            $choices[] = $choice;
            $rsChoice->MoveNext();
        }
        $rsChoice->Close();
        return $choices;
    }
    /**
     * Calcuate the mark which choice gets
     *
     * @param Integer $choiceID
     * @param Integer $numberOfChoices
     */
    static function evaluatePoints($choiceID, $numberOfChoices)
    {
        //reversing the marks simplely
        return $numberOfChoices - $choiceID + 1;
    }
}

