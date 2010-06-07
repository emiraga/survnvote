<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package DataAccessObject
 */

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
     * @param $page PageVO
     */
    public function __construct(PageVO &$page, $username)
    {
        $this->page =& $page;
        $this->name = $username;
    }
    /**
     * @param $page PageVO
     * @param $type String of a vote, CALL, SMS or WEB
     * @param $surveyID Integer of a survey which want to be voted
     * @param $choiceID Integer of a survey which voter wants to vote
     * @param $presentationID Integer of a survey, could be NULL
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
     * @param $vote VoteVO object
     * @return Boolean true on success
     */
    function vote(VoteVO &$vote)
    {
        global $vgDB, $vgDBPrefix;
        $vgDB->StartTrans();

        // Check whether voted before
        $sql ="select ID, choiceID from {$vgDBPrefix}surveyrecord where voterID = ? and surveyID = ? and presentationID = ? order by voteDate desc";
        $rs = $vgDB->Execute($sql, array($vote->getVoterID(), $vote->getSurveyID(), $vote->getPresentationID() ));

        if ($rs->RecordCount() >= $vote->getVotesAllowed() )
        {
            //user has more votes than allowed, remove previous vote
            $IDbyOldVote = $rs->fields['ID'];
            $choiceIDbyOldVote = $rs->fields['choiceID'];
            $vgDB->Execute("update {$vgDBPrefix}surveyrecord set choiceID = ? , voteDate = ? where ID = ?",
                    array($vote->getChoiceID(), $vote->getVoteDate(), $IDbyOldVote));
            $vgDB->Execute("update {$vgDBPrefix}surveychoice set vote=vote+1 where surveyID = ? and choiceID = ?",
                    array($vote->getSurveyID(), $vote->getChoiceID()));
            $vgDB->Execute("update {$vgDBPrefix}surveychoice set vote=vote-1 where surveyID = ? and choiceID = ?",
                    array($vote->getSurveyID(), $choiceIDbyOldVote));
        }
        else
        {
            $vgDB->Execute("insert into {$vgDBPrefix}surveyRecord (voterID, pageID, surveyID, choiceID, presentationID, voteDate, voteType) values(?,?,?,?,?,?,?)",
                    array($vote->getVoterID(), $vote->getPageID(), $vote->getSurveyID(), $vote->getChoiceID(),
                    $vote->getPresentationID(), $vote->getVoteDate(), $vote->getVoteType()));
            $vgDB->Execute("update {$vgDBPrefix}surveychoice set vote=vote+1 where surveyID = ? and choiceID = ?",
                    array($vote->getSurveyID(),  $vote->getChoiceID()));
        }
        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            throw new Exception("Process Vote database error: ".$vgDB->ErrorMsg(), 400);
        }
        return true;
    }
    static function countNewVotes($page_id, $timestamp)
    {
        global $vgDB, $vgDBPrefix;
        $datetime = vfDate($timestamp);
        return $vgDB->GetOne("SELECT count(ID) FROM {$vgDBPrefix}surveyrecord WHERE pageID = ? AND voteDate >= ?",
                array($page_id, $datetime));
    }
}

