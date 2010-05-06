<?php
if (!defined('MEDIAWIKI')) die();

/**
 * This page includes class USR, SurveyRecordDAO and SurveyRecordVO which are used to
 * vote for a Survey.
 *
 * @package DAO of Survey
 */

require_once("$gvPath/VO/CallVO.php");
require_once("$gvPath/VoteDAO.php");

/**
 * Class Usr includes functions which can vote surveys so far
 *
 * @author Bai Qifeng
 * @version 2.0
 */
class Usr
{
	private $usrID;
	/**
	 * Initiate a voter
	 *
	 * @param $username ID of the user, leave blank if it is unknown
	 */
	function __construct($username)
	{
		$this->usrID = $username;
	}
	/**
	 * Get username of this user (usrID)
	 *
	 * @return string username
	 */
	function getUsername()
	{
		return $this->usrID;
	}
	/**
	 * Get mobile phone from wikidb
	 * Asumes that current logged user is the user doing the voting
	 *
	 * @return a string representing mobile phone of a user, or false if it does not exist
	 */
	function getUserMobileNumber()
	{
		global $wgUser;
		if(!$wgUser->isLoggedIn())
		{
			throw new SurveyException("Not logged in", 400);
		}
		if( $wgUser->getName() != $this->usrID )
		{
			throw new SurveyException("Username does not match", 400);
		}
		$mobile = $wgUser->getOption('mobilephone');
		if(strlen($mobile) > 6) //@todo check validity of mobile number
		{
			return $mobile;
		}
		return false;
	}
	/**
	 * Vote for a survey.
	 *
	 * @param $type type of this vote, could be CALL, SMS or WEB
	 * @param $surveyID surveyID of a survey which want to be voted
	 * @param $choiceID choice of a survey which voter wants to vote
	 * @param $presentationID presentationID of a survey, can be empty
	 * @param $callsmsid CALL or SMS in in database, can be empty
	 * @return boolean true for success
	 */
	public function vote($type, $surveyID, $choiceID, $presentationID = 0, $callsmsid = NULL)
	{
		$finalSql = array();
		$username = $this->getUsername();
		$voteaccess = new VoteDAO();

		$vote = $voteaccess->newFromCurrentSurvey($type, $username, $surveyID, $choiceID, $presentationID);
		
		//Check whether have a user associated with the mobile phone
		//If it is, then use his mobilephone as voterID
		if($vote->getType() =='WEB')
		{
			$mobile = getUserMobileNumber();
			if($mobile != false)
			{
				$vote->voterID = $mobile;
			}
		}
		$voteaccess->processVote($vote, $callsmsid);
		return true;
	}
}
?>