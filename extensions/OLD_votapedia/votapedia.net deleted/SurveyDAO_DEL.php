<?php
class SurveyDAO_DEL
{
    /**
     * Get array of surveys selected with an SQL statement
     * 
     * @param $sql select SQL statement
     * @param $params arrays of parameters to SQL statement
     */
    private function getSurveysSQL($sql, $params)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $rsSurveys = &$vgDB->Execute($sql, $params);

        $surveys = array();
        while(!$rsSurveys->EOF)
        {
            $survey = new SurveyVO();

            $survey->setPageID($rsSurveys->fields["pageID"]);
            $survey->setSurveyID($rsSurveys->fields["surveyID"]);
            $survey->setQuestion(trim($rsSurveys->fields["question"]));
            $survey->setAnswer(trim($rsSurveys->fields["answer"]));
            $survey->setPoints($rsSurveys->fields["points"]);
            //Redundant info from PageVO
            $survey->setType($page->getType());
            $survey->setVotesAllowed( $page->getVotesAllowed() );
            trigger_error('$survey->setType($page->getType())',E_USER_ERROR);
            die('horrible death');
            //choices
            $choices = $this->getChoices($survey->getSurveyID(), $survey->getPageID());
            $survey->setChoices($choices);
            //presentations
            $presentations = $this->getPresentations($survey->getSurveyID());
            $survey->setPresentations($presentations);

            $surveys[]=$survey;
            $rsSurveys->MoveNext();
        }
        $rsSurveys->Close();
        return $surveys	;
    }
    /**
     * Get a survey(SurveyVO) by surveyID
     * 
     * @param $id an ID which want to be retreived
     * @return SurveyVO $survey a survey which matches ID
     */
    function findSurveyByID($id)
    {
        $surveys = $this->getSurveysSQL("select * from {$vgDBPrefix}survey where surveyID = ?", array($surveyID));
        if(count($surveys) == 0)
            throw new SurveyException("Survey not found", 400);
        return $surveys[0];
    }
    /**
     * Get an array of current surveys
     * 
     * @param $num number of surveys required, can be empty for all surveys
     */
    function findCurrentSurveys($num = NULL)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
        $now = vfDate();
        $sql = "select pageID from {$vgDBPrefix}page where starttime <= '$now' and endtime >= '$now' and surveytype = 1 order by starttime desc";
        $param = array();
        if($num)
        {
            $sql .= " limit ?";
            $param = array($num);
        }
        $rs = &$vgDB->Execute($sql, $param);
        $surveyIDs = array();
        $votesAllowed = array();

        while(!$rs->EOF)
        {
            $surveyIDs[]= $rs->fields["pageID"];
            //$votesAllowed[] = $rs->fields["votesAllowed"];
            $rs->MoveNext();
        }

        $surveys = array();
        foreach($surveyIDs as $id)
        {
            $survey = $this->getSurveysSQL("select * from {$vgDBPrefix}survey where pageID = ?", $id);
            if(count($survey) == 0)
                throw new SurveyException("findCurrentSurveys, survey not found.");
            $surveys[] = $survey[0] ;
        }
        return $surveys;
    }
}

