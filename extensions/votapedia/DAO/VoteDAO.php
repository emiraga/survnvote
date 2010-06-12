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
    /** @var String */ private $userID;

    /**
     * @param PageVO $page
     */
    public function __construct(PageVO &$page, $userID)
    {
        $this->page =& $page;
        $this->userID = $userID;
    }
    /**
     * @param PageVO $page
     * @param String $type of a vote, CALL, SMS or WEB
     * @param Integer $surveyID of a survey which want to be voted
     * @param Integer $choiceID of a survey which voter wants to vote
     * @param Integer $presentationID of a survey, could be NULL
     * @return SurveyVO object
     */
    function newFromPage($type, $pageID, $surveyID, $choiceID, $presentationID)
    {
        //$survey =& $this->page->getSurveyBySurveyID($surveyID);

        $vote = new VoteVO();
        $vote->setPageID($pageID);
        $vote->setSurveyID($surveyID);
        $vote->setChoiceID($choiceID);
        $vote->setPresentationID($presentationID);
        $vote->setVoterID($this->userID);
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
        $sql ="select voteID from {$vgDBPrefix}vote where userID = ? and surveyID = ? and presentationID = ?";
        $rs = $vgDB->Execute($sql, array($vote->getVoterID(), $vote->getSurveyID(), $vote->getPresentationID() ));

        if ($rs->RecordCount() >= $vote->getVotesAllowed() )
        {
            //user has more votes than allowed, remove previous vote
            $IDbyOldVote = $rs->fields['ID'];
            $vgDB->Execute("update {$vgDBPrefix}vote set choiceID = ? , voteDate = ? where voteID = ?",
                    array($vote->getChoiceID(), $vote->getVoteDate(), $IDbyOldVote));
        }
        else
        {
            //add new vote
            $vgDB->Execute("insert into {$vgDBPrefix}vote (userID, pageID, surveyID, choiceID, presentationID) values(?,?,?,?,?)",
                    array($vote->getVoterID(), $vote->getPageID(), $vote->getSurveyID(), $vote->getChoiceID(),
                    $vote->getPresentationID()));
            $voteid = $vgDB->Insert_ID();
            $vgDB->Execute("insert into {$vgDBPrefix}vote_details (voteID, voteDate, voteType, comments) values(?,?,?,?)",
                    array($voteid, $vote->getVoteDate(), $vote->getVoteType(), 'no comment'));
        }
        return true;
    }
    /**
     * Count new votes since certain datetime.
     *
     * @param Integer $page_id
     * @param Integer $presentation_id
     * @param Integer $timestamp
     * @return Integer number of new votes
     */
    static function countNewVotes($page_id, $presentation_id, $timestamp)
    {
        global $vgDB, $vgDBPrefix;
        $datetime = vfDate($timestamp);
        return $vgDB->GetOne("SELECT count(ID) FROM {$vgDBPrefix}vote WHERE pageID = ? "
        ."AND presentationID = ? AND voteDate >= ?",
                array($page_id, $presentation_id , $datetime));
    }
    /**
     * Get number of votes for a specific page and presentationID
     * Returned array $result has following structure
     *
     *  $result[surveyID][choiceID] = num_of_votes
     *
     * @param Integer $pageID
     * @param Integer $presentationID
     * @return VotesCount
     */
    static function getNumVotes($pageID, $presentationID)
    {
        global $vgDB, $vgDBPrefix;
        $result = new VotesCount();
        $records = $vgDB->GetAll("select surveyID, choiceID, count(voteID) as votes from {$vgDBPrefix}vote "
                ."where pageID = ? and presentationID = ? group by surveyID, choiceID",
                array($pageID, $presentationID));
        $votes = 0;
        foreach($records as $record)
        {
            $result->set( $record['surveyID'], $record['choiceID'], $record['votes']);
            $votes += $record['votes'];
        }
        return $result;
    }
}

