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
     * @param $pageid Integer
     */
    public function setPageID($pageid)
    {
        $this->pageID = $pageid;
    }
    /**
     * @param $surveyid Integer
     */
    public function setSurveyID($surveyid)
    {
        $this->surveyID = $surveyid;
    }
    /**
     *
     * @param $choiceid Integer
     */
    public function setChoiceID($choiceid)
    {
        $this->choiceID = $choiceid;
    }
    /**
     *
     * @param $presentationid Integer
     */
    public function setPresentationID ($presentationid)
    {
        $this->presentationID = $presentationid;
    }
    /**
     *
     * @param $voterid String
     */
    public function setVoterID($voterid)
    {
        $this->voterID = $voterid;
    }
    /**
     *
     * @param $votedate String
     */
    public function setVoteDate($votedate)
    {
        $this->voteDate = $votedate;
    }
    /**
     *
     * @param $votetype String 'CALL', 'WEB' or 'SMS'
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
     * @param $numvotes Integer
     */
    public function setVotesAllowed($numvotes)
    {
        $this->votesAllowed = $numvotes;
    }
}

