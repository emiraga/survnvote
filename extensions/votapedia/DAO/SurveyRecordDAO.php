<?php
if (!defined('MEDIAWIKI')) die();
/**
 * SurveyRecordDAO includes functions which can access
 * voting records of surveys from database system.
 * 
 * @package DAO of Survey
 * @author Bai Qifeng
 * @author Emir Habul
 * @version 1.1
 */

class SurveyRecordDAO
{
	/**
	 * Check whether the voter is in its first voting
	 * Looks into tables 'incomingcall' and 'incomingsms'
	 * @todo this does not belong here
	 * 
	 * @param $voterID ID of voter, could be wiki usrname, telephone number.
	 * @return Boolean true represents it is the first time voting.
	 */
	function isFirstVoting($voterID)
	{
		global $gvDB, $gvDBPrefix;
		
		$num= $gvDB->GetOne("select count(*) as num from {$gvDBPrefix}incomingcall where caller = ?", array($voterID));
		if ($num > 0)
			return false;

		$num= $gvDB->GetOne("select count(*) as num from {$gvDBPrefix}incomingsms where sender = ?", array($voterID));
		if ($num > 0)
			return false;

		return true;
	}

	/** 
	 * Check whether the voter has voted to this survey before
	 * 
	 * @param $surveyID ID of this survey
	 * @param $username username/ID of voter
	 * @return Boolean false represents this voter has voted this survey before.
	 */
	function isMultipleVote($surveyID, $username)
	{
		global $gvDB, $gvDBPrefix;
		$sql="select count(surveyID) as num from {$gvDBPrefix}surveyrecord where surveyID = ? and voterID = ?";
		$num= $gvDB->GetOne($sql,array( $surveyID, $username ));
		return ($num != 0);
	}
	/**
	 * Insert a voting record of a survey into database.
	 * 
	 * Write to table surveyRecord and update table surveyChoice with increment the vote field.
	 * 
	 * @param $surveyRecordVO SurveyRecordVO object
	 * @return NULL
	 */
	function insertRecord(SurveyRecordVO &$surveyRecordVO)
	{
		global $gvDB, $gvDBPrefix;

		$sql = "insert into {$gvDBPrefix}surveyRecord (surveyID, choiceID, presentationID, voterID, voteDate, voteType) values(?,?,?,?,?,?)";
		
		$params = array(
			$surveyRecordVO->getSurveyID(),$surveyRecordVO->getChoiceID(),$surveyRecordVO->getPresentationID(),
			$surveyRecordVO->getVoterID(),$surveyRecordVO->getVoteDate(), $surveyRecordVO->getVoteType(),
		);
		$gvDB->Execute($sql,$params);

		$sql = "update {$gvDBPrefix}surveyChoice set vote=vote+1 where surveyID = ? and choiceID = ?";
		$params = array(
			$surveyRecordVO->getSurveyID(), $surveyRecordVO->getChoiceID()
		);
		$gvDB->Execute($sql,$params);
	}
}

?>