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
            $sql = "insert into {$vgDBPrefix}choice (pageID, surveyID, choiceID, choice, points, numvotes) values (?,?,?,?,?,?)";
            $resChoice = $vgDB->Prepare($sql);
            $choiceID = 0;
            $choices =& $survey->getChoices();
            foreach($choices as &$surveyChoice)
            {
                /* @var $surveyChoice ChoiceVO */
                $choiceID++;
                $param = array(
                        $survey->getPageID(),
                        $survey->getSurveyID(),
                        $choiceID,
                        $surveyChoice->choice,
                        SurveyDAO::evaluatePoints($choiceID,$survey->getNumOfChoices()),
                        $surveyChoice->numvotes
                );
                $vgDB->Execute($resChoice,$param);

                $surveyChoice->choiceID = $choiceID;
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

        $sql = "delete from {$vgDBPrefix}choice where pageID = ?";
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
        $sql = "select * from {$vgDBPrefix}survey where pageID = ?";
        $rsSurveys = &$vgDB->Execute($sql, array(intval($pageID)));

        $surveys = array();
        $ids = array();
        while(!$rsSurveys->EOF)
        {
            $survey = new SurveyVO();

            $survey->setPageID(intval($rsSurveys->fields["pageID"]));
            $survey->setSurveyID(intval($rsSurveys->fields["surveyID"]));
            $survey->setQuestion(trim($rsSurveys->fields["question"]));
            $survey->setAnswer(trim($rsSurveys->fields["answer"]));
            $survey->setPoints($rsSurveys->fields["points"]);

            if($load_choices)
            {
                $choices =& SurveyDAO::getChoices($survey->getSurveyID(), $survey->getPageID());
                $survey->setChoices($choices);
            }

            $surveys[] = $survey;
            $ids[] = intval($rsSurveys->fields["surveyID"]);
            $rsSurveys->MoveNext();
        }
        $rsSurveys->Close();
        array_multisort($ids, SORT_NUMERIC, SORT_ASC, $surveys);
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
        $sql = "select * from {$vgDBPrefix}choice where surveyID=?";
        $rsChoice = &$vgDB->Execute($sql, array(intval($surveyID)));

        $choices = array();
        $ids = array();
        while(!$rsChoice->EOF)
        {
            //Access by name, some database may not support this
            //small case
            $choice = new ChoiceVO();
            $choice->surveyID = intval($rsChoice->fields['surveyID']);
            $choice->pageID = intval($rsChoice->fields['pageID']);
            $choice->choiceID = intval($rsChoice->fields['choiceID']);
            $choice->choice = trim($rsChoice->fields['choice']);
            $choice->receiver = trim($rsChoice->fields['receiver']);
            $choice->SMS = trim($rsChoice->fields['SMS']);
            $choice->points = $rsChoice->fields['points'];
            $choice->numvotes = intval( $rsChoice->fields['numvotes'] );

            $choices[] = $choice;
            $ids[] = $choice->choiceID;
            $rsChoice->MoveNext();
        }
        $rsChoice->Close();
        array_multisort( $ids, SORT_NUMERIC, SORT_ASC, $choices );
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

