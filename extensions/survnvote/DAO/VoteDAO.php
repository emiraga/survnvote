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
        $vote->setPageID(intval($pageID));
        $vote->setSurveyID(intval($surveyID));
        $vote->setChoiceID(intval($choiceID));
        $vote->setPresentationID(intval($presentationID));
        $vote->setVoterID($this->userID);
        $vote->setVoteDate(vfDate());
        $vote->setVoteType( $type );
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
        $sql ="select voteID, choiceID from {$vgDBPrefix}vote where userID = ? and surveyID = ? and presentationID = ?";
        $rs = $vgDB->Execute($sql, array($vote->getVoterID(), $vote->getSurveyID(), $vote->getPresentationID() ));

        if ($rs->RecordCount() >= $vote->getVotesAllowed() )
        {
            //user has more votes than allowed, remove previous vote
            $IDbyOldVote = $rs->fields['voteID'];
            $oldChoiceID = $rs->fields['choiceID'];
            
            $vgDB->Execute("update {$vgDBPrefix}vote set choiceID = ? where voteID = ?",
                    array($vote->getChoiceID(), $IDbyOldVote));
            $vgDB->Execute("update {$vgDBPrefix}vote_details set voteDate = ?, voteType = ? where voteID = ?",
                    array($vote->getVoteDate(), $vote->getVoteType(), $IDbyOldVote));
            
            //count down the choice.numvotes in previous choice
            $vgDB->Execute("update {$vgDBPrefix}choice SET numvotes = numvotes-1 WHERE surveyID = ? AND choiceID = ?",
                    array($vote->getSurveyID(), $oldChoiceID));
        }
        else
        {
            //add new vote
            $vgDB->Execute("insert into {$vgDBPrefix}vote (userID, pageID, surveyID, choiceID, presentationID) values(?,?,?,?,?)",
                    array($vote->getVoterID(), $vote->getPageID(), $vote->getSurveyID(), $vote->getChoiceID(),
                    $vote->getPresentationID()));
            $voteid = intval($vgDB->Insert_ID());
            $vgDB->Execute("insert into {$vgDBPrefix}vote_details (voteID, voteDate, voteType, comments) values(?,?,?,?)",
                    array($voteid, $vote->getVoteDate(), $vote->getVoteType(), 'no comment'));
        }
        //update choice.numvotes field
        $vgDB->Execute("update {$vgDBPrefix}choice SET numvotes = numvotes+1 WHERE surveyID = ? AND choiceID = ?",
                array($vote->getSurveyID(), $vote->getChoiceID()));
        
        return true;
    }
    /**
     * Count new votes since certain datetime.
     *
     * @param Integer $page_id
     * @param Integer $presentation_id
     * @param Integer $last_voteID
     * @return Integer number of new votes
     */
    static function countNewVotes($page_id, $presentation_id, $last_voteID)
    {
        global $vgDB, $vgDBPrefix;
        $r = $vgDB->GetAll("SELECT count(voteID) as count, max(voteID) as maxch FROM {$vgDBPrefix}vote WHERE voteID > ? "
                ." AND pageID = ? AND presentationID = ?",
                array($last_voteID, $page_id, $presentation_id));
        $r = $r[0];
        return array($r['count'], $r['maxch']);
    }
    /**
     * Get number of votes for a specific page and presentationID
     * Returned array $result has following structure
     *
     *  $result[surveyID][choiceID] = num_of_votes
     *
     * @param PageVO $page
     * @param Integer $presentationID
     * @return VotesCount or Boolean false on wrong presentation
     */
    static function getNumVotes($page, $presentationID)
    {
        global $vgDB, $vgDBPrefix;
        
        if($page->getCurrentPresentationID() != $presentationID)
        {
            $pres =& $page->getPresentationByNum($presentationID);
            if(! $pres)
                return false;
            $result = unserialize( $pres->numvotes );
            return $result;
        }
        
        $result = new VotesCount();
        $records = $vgDB->GetAll("select numvotes,surveyID,choiceID FROM {$vgDBPrefix}choice"
        ." LEFT JOIN {$vgDBPrefix}survey"
        ." USING (surveyID) WHERE {$vgDBPrefix}survey.pageID = ?",
                array(intval($page->getPageID())));

        foreach($records as $record)
        {
            $result->set( $record['surveyID'], $record['choiceID'], $record['numvotes']);
        }
        return $result;
    }
    /**
     * Get previous choice/vote for this user.
     * 
     * @param Integer $userID
     * @param Integer $surveyID
     * @param Integer $presID
     * @return Integer
     */
    static function getPrevVote($userID, $surveyID, $presID)
    {
        global $vgDB, $vgDBPrefix;
        $sql ="select choiceID from {$vgDBPrefix}vote where userID = ? and surveyID = ? and presentationID = ?";
        return $vgDB->GetOne($sql, array($userID, $surveyID, $presID));
    }
}

