<?php
#if (!defined('MEDIAWIKI')) die();
define('MEDIAWIKI',true);

require_once('../../LocalSettings.php');

/*
 * Enter user/pass of a admin account for mysql that
 * has priviledges for CREATE and DELETE of tables.
 */ 
$gvDBUserName       = 'root';
$gvDBUserPassword   = '';

require_once("$gvPath/Common.php");

$sql = <<<END_SQL
--
-- Table structure for table page
--
DROP TABLE IF EXISTS {$gvDBPrefix}page;
CREATE TABLE IF NOT EXISTS {$gvDBPrefix}page (
  pageID int(11) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(512) NOT NULL,
  startTime datetime NOT NULL,
  endTime datetime NOT NULL,
  duration int(11) NOT NULL,
  author varchar(20) NOT NULL,
  phone varchar(20) DEFAULT NULL,
  createTime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  invalidAllowed tinyint(1) NOT NULL DEFAULT '0',
  smsRequired tinyint(1) NOT NULL DEFAULT '0',
  teleVoteAllowed smallint(1) NOT NULL DEFAULT '1',
  anonymousAllowed tinyint(1) NOT NULL DEFAULT '1',
  showGraph tinyint(1) NOT NULL DEFAULT '1',
  displayTop tinyint(4) NOT NULL DEFAULT '0',
  surveyType tinyint(4) NOT NULL DEFAULT '1',
  votesAllowed tinyint(8) unsigned NOT NULL DEFAULT '1',
  subtractWrong tinyint(1) NOT NULL DEFAULT '0',
  eventID varchar(11) DEFAULT NULL,
  PRIMARY KEY (pageID),
  KEY pageID (pageID)
);

-- --------------------------------------------------------

--
-- Table structure for table presentation
--

DROP TABLE IF EXISTS {$gvDBPrefix}presentation;
CREATE TABLE IF NOT EXISTS {$gvDBPrefix}presentation (
  surveyID int(10) unsigned NOT NULL,
  presentationID tinyint(10) unsigned NOT NULL,
  presentation varchar(1000) NOT NULL,
  active tinyint(1) NOT NULL DEFAULT '0',
  mark tinyint(4) NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

--
-- Table structure for table survey
--

DROP TABLE IF EXISTS {$gvDBPrefix}survey;
CREATE TABLE IF NOT EXISTS {$gvDBPrefix}survey (
  pageID int(11) NOT NULL,
  surveyID int(11) unsigned NOT NULL AUTO_INCREMENT,
  question varchar(4000) NOT NULL,
  answer tinyint(4) NOT NULL DEFAULT '0',
  points tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (surveyID),
  KEY pageID (pageID)
);

-- --------------------------------------------------------

--
-- Table structure for table surveychoice
--

DROP TABLE IF EXISTS {$gvDBPrefix}surveychoice;
CREATE TABLE IF NOT EXISTS {$gvDBPrefix}surveychoice (
  surveyID int(11) NOT NULL,
  choiceID tinyint(4) unsigned NOT NULL DEFAULT '1',
  choice varchar(400) NOT NULL,
  receiver varchar(20) DEFAULT NULL,
  points tinyint(4) NOT NULL DEFAULT '0',
  SMS varchar(200) NOT NULL DEFAULT 'none',
  vote int(11) NOT NULL DEFAULT '0',
  KEY surveyID (surveyID)
);

-- --------------------------------------------------------

--
-- Table structure for table surveyrecord
--

DROP TABLE IF EXISTS {$gvDBPrefix}surveyrecord;
CREATE TABLE IF NOT EXISTS {$gvDBPrefix}surveyrecord (
  ID int(11) unsigned NOT NULL AUTO_INCREMENT,
  voterID varchar(50) NOT NULL DEFAULT 'unknown',
  surveyID int(11) NOT NULL,
  presentationID tinyint(4) NOT NULL DEFAULT '0',
  choiceID tinyint(4) NOT NULL,
  voteDate datetime NOT NULL,
  voteType varchar(6) NOT NULL,
  PRIMARY KEY (ID),
  KEY i_surveyrecord (surveyID)
);
END_SQL;

	$commands = split(';', $sql);
	foreach($commands as $sql)
	{
		$sql = trim($sql);
		if($sql)
		{
			$gvDB->Execute($sql);
		}
	}
?>