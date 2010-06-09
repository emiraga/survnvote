<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/VO/VoteVO.php");

/**
 * Class for managing votes in database
 * 
 * @package DataAccessObject
 */
class VoteDAO
{
    /** @var PageVO */ private $page;
    /** @var String */ private $name;
    
    /**
     * @param PageVO $page
     */
    public function __construct(PageVO &$page, $username)
    {
        $this->page =& $page;
        $this->name = $username;
    }
    /**
     * @param PageVO $page
     * @param String $type of a vote, CALL, SMS or WEB
     * @param Integer $surveyID of a survey which want to be voted
     * @param Integer $choiceID of a survey which voter wants to vote
     * @param Integer $presentationID of a survey, could be NULL
     * @return SurveyVO object
     */
    function newFromPage($type, $pageID, $surveyID, $choiceID, $presentationID = 0)
    {
        //$survey =& $this->page->getSurveyBySurveyID($surveyID);

        $vote = new VoteVO();
        $vote->setPageID($pageID);
        $vote->setSurveyID($surveyID);
        $vote->setChoiceID($choiceID);
        $vote->setPresentationID($presentationID);
        $vote->setVoterID($this->name);
        $vote->setVoteDate(vfDate());
        $vote->setVoteType( $type );
        $vote->setVotesAllowed( $this->page->getVotesAllowed() );
        return $vote;
    }
    /**
     * Check for validity of vote and add this vote to database
     *
     * @param VoteVO $vote object
     * @return Boolean true on success
     */
    function vote(VoteVO &$vote)
    {
        global $vgDB, $vgDBPrefix;
        // Check if user has voted before
        $sql ="select ID from {$vgDBPrefix}surveyrecord where voterID = ? and surveyID = ? and presentationID = ? order by voteDate asc";
        $rs = $vgDB->Execute($sql, array($vote->getVoterID(), $vote->getSurveyID(), $vote->getPresentationID() ));

        if ($rs->RecordCount() >= $vote->getVotesAllowed() )
        {
            //user has more votes than allowed, remove previous vote
            $IDbyOldVote = $rs->fields['ID'];
            $vgDB->Execute("update {$vgDBPrefix}surveyrecord set choiceID = ? , voteDate = ? where ID = ?",
                    array($vote->getChoiceID(), $vote->getVoteDate(), $IDbyOldVote));
        }
        else
        {
            //add new vote
            $vgDB->Execute("insert into {$vgDBPrefix}surveyrecord (voterID, pageID, surveyID, choiceID, presentationID, voteDate, voteType) values(?,?,?,?,?,?,?)",
                    array($vote->getVoterID(), $vote->getPageID(), $vote->getSurveyID(), $vote->getChoiceID(),
                    $vote->getPresentationID(), $vote->getVoteDate(), $vote->getVoteType()));
        }
        return true;
    }
    /**
     * Count new votes since certain datetime.
     * 
     * @param Integer $page_id
     * @param Integer $timestamp
     * @return Integer number of new votes
     */
    static function countNewVotes($page_id, $timestamp)
    {
        global $vgDB, $vgDBPrefix;
        $datetime = vfDate($timestamp);
        return $vgDB->GetOne("SELECT count(ID) FROM {$vgDBPrefix}surveyrecord WHERE pageID = ? AND voteDate >= ?",
                array($page_id, $datetime));
    }
}

