<?php
if (!defined('MEDIAWIKI')) die();

/**
 * @package DataAccessObject
 */

global $vgPath;
require_once("$vgPath/VO/VoteVO.php");
require_once("$vgPath/DAO/IncomingDAO.php");

/**
 * Class for managing votes in database
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
     * @param $type of a vote, CALL, SMS or WEB
     * @param $username ID of a user
     * @param $surveyID surveyID of a survey which want to be voted
     * @param $choiceID choice of a survey which voter wants to vote
     * @param $presentationID presentationID of a survey, could be NULL
     * @return SurveyVO object
     */
    function newFromPage($type, $username, $surveyID, $choiceID, $presentationID = 0)
    {
        $survey =& $this->page->getSurveyBySurveyID($surveyID);

        $vote = new VoteVO();
        $vote->setSurveyID($surveyID);
        $vote->setChoiceID($choiceID);
        $vote->setPresentationID($presentationID);
        $vote->setVoterID($username);
        $vote->setVoteDate(vfDate());
        $vote->setVoteType( $type );
        $vote->setVotesAllowed( $this->page->getVotesAllowed() );
        return $vote;
    }
    /**
     * Check for validity of vote and add this vote to database
     *
     * @param $vote VoteVO object
     * @param $incoming IncomingDAO object related to CALL or SMS
     * @return true on success
     */
    function vote(VoteVO &$vote, IncomingDAO &$incoming)
    {
        assert($vote->getVoteType() == $incoming->getType());
        
        global $vgDB, $vgDBPrefix;
        $vgDB->StartTrans();

        /*@todo put this somewhere else
        if ($vote->getInvalidAllowed() == 0 && $vote->getVoterID() == '') //if multi-vote is not allowed
        {
            $incoming->updateError(5); //Invalid telephone is forbidden
            return false;
        }
        */
        // Check whether voted before
        $sql ="select * from {$vgDBPrefix}surveyrecord where voterID = ? and surveyID = ? and presentationID = ? order by voteDate asc";
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
            
            $incoming->updateError(4); //Repeated voting
        }
        else
        {
            $vgDB->Execute("insert into {$vgDBPrefix}surveyRecord (voterID, surveyID, choiceID, presentationID, voteDate, voteType) values(?,?,?,?,?,?)",
                    array($vote->getVoterID(), $vote->getSurveyID(), $vote->getChoiceID(),
                    $vote->getPresentationID(), $vote->getVoteDate(), $vote->getVoteType()));
            $vgDB->Execute("update {$vgDBPrefix}surveychoice set vote=vote+1 where surveyID = ? and choiceID = ?",
                    array($vote->getSurveyID(),  $vote->getChoiceID()));
        }
        echo "COMPLETE";
        $vgDB->CompleteTrans();
        if ($vgDB->HasFailedTrans())
        {
            throw new Exception("Process Vote database error: ".$vgDB->ErrorMsg(), 400);
        }
        return true;
    }
}
?>