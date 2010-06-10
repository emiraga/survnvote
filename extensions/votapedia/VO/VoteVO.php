<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ValueObject
 */

/**
 * Vote value object
 *
 * @package ValueObject
 */
class VoteVO
{
    private $pageID;
    private $surveyID;
    private $choiceID;
    private $presentationID = 0;
    private $voterID;
    private $voteDate;
    private $voteType;
    private $votesAllowed = 1;
    /**
     * @return Integer survey ID
     */
    public function getSurveyID()
    {
        return $this->surveyID;
    }
    /**
     * @return Integer page ID
     */
    public function getPageID()
    {
        return $this->pageID;
    }
    /**
     * @return Integer choice ID
     */
    public function getChoiceID()
    {
        return $this->choiceID;
    }
    /**
     * Return the presentation ID
     *
     * @return Integer presentation ID
     */
    public function getPresentationID()
    {
        return $this->presentationID;
    }
    /**
     * @return Integer voter ID
     */
    public function getVoterID()
    {
        return $this->voterID;
    }
    /**
     * @return String vote date
     */
    public function getVoteDate()
    {
        return $this->voteDate;
    }
    /**
     * @return String vote type
     */
    public function getVoteType()
    {
        return $this->voteType;
    }
    /**
     * @return Integer number of votes allowed per user
     */
    public function getVotesAllowed()
    {
        return $this->votesAllowed;
    }
    /**
     * @param Integer $pageid
     */
    public function setPageID($pageid)
    {
        $this->pageID = $pageid;
    }
    /**
     * @param Integer $surveyid
     */
    public function setSurveyID($surveyid)
    {
        $this->surveyID = $surveyid;
    }
    /**
     *
     * @param Integer $choiceid
     */
    public function setChoiceID($choiceid)
    {
        $this->choiceID = $choiceid;
    }
    /**
     *
     * @param Integer $presentationid
     */
    public function setPresentationID ($presentationid)
    {
        $this->presentationID = $presentationid;
    }
    /**
     *
     * @param String $voterid
     */
    public function setVoterID($voterid)
    {
        $this->voterID = $voterid;
    }
    /**
     *
     * @param String $votedate
     */
    public function setVoteDate($votedate)
    {
        $this->voteDate = $votedate;
    }
    /**
     *
     * @param String $votetype 'CALL', 'WEB' or 'SMS'
     */
    public function setVoteType($votetype)
    {
        if( in_array($votetype, array( 'WEB', 'CALL', 'SMS' )) )
            $this->voteType = $votetype;
        else
            throw new SurveyException("Invalid vote type", 400);
    }
    /**
     * Set number of allowed votes per user
     * @param Integer $numvotes
     */
    public function setVotesAllowed($numvotes)
    {
        $this->votesAllowed = $numvotes;
    }
}


/**
 * Class which holds number of votes in a specific page
 */
class VotesCount
{
    private $record = array();
    /**
     * Set value of votes
     *
     * @param Integer $surveyID
     * @param Integer $choiceID
     * @param Integer $votes
     */
    public function set($surveyID, $choiceID, $votes)
    {
        $this->record[$surveyID][$choiceID] = $votes;
    }
    /**
     * Get number of votes for a choice in given survey.
     *
     * @param Integer $surveyID
     * @param Integer $choiceID
     * @return Integer
     */
    public function get($surveyID, $choiceID)
    {
        if(isset($this->record[$surveyID][$choiceID]))
            return $this->record[$surveyID][$choiceID];
        else
            return 0;
    }
}

