<?php
#if (!defined('MEDIAWIKI')) die();
define('MEDIAWIKI',true);

require_once('../../LocalSettings.php');

/*
 * Enter user/pass of a admin account for mysql that
 * has priviledges for CREATE and DELETE of tables.
 */
global $vgDBUserName, $vgDBUserPassword;
$vgDBUserName       = 'root'; // Master username for database (user has permission to create tables)
$vgDBUserPassword   = '';     // Password for database user

function vfDoSetup()
{
	global $gvPath, $vgDB, $vgDBPrefix;
	require_once("$gvPath/Common.php");
	
	$sql = <<<END_SQL
--
-- Table structure for table page
--
DROP TABLE IF EXISTS {$vgDBPrefix}page;
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}page (
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
  privacy tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (pageID),
  KEY pageID (pageID)
);

-- --------------------------------------------------------

--
-- Table structure for table presentation
--

DROP TABLE IF EXISTS {$vgDBPrefix}presentation;
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}presentation (
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

DROP TABLE IF EXISTS {$vgDBPrefix}survey;
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}survey (
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

DROP TABLE IF EXISTS {$vgDBPrefix}surveychoice;
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}surveychoice (
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

DROP TABLE IF EXISTS {$vgDBPrefix}surveyrecord;
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}surveyrecord (
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

--
-- Table structure for table usedreceivers
--

DROP TABLE IF EXISTS {$vgDBPrefix}usedreceivers;
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}usedreceivers (
  receiver varchar(20) NOT NULL,
  UNIQUE(receiver)
);

END_SQL;
	
	$commands = split(';', $sql);
	foreach($commands as $sql)
	{
		$sql = trim($sql);
		if($sql)
		{
			$vgDB->Execute($sql);
		}
	}
}

global $gvScript, $wgScriptPath;
if(defined('VOTAPEDIA_TEST') || isset($_POST['do_install']))
{
	try
	{
		vfDoSetup();
	}
	catch(Exception $e)
	{
		die("<h3>Error: ".htmlspecialchars($e->getMessage())."</h3>");
	}
	if(isset($_POST['do_install']))
	{
		echo "<h1>Votapedia installation is complete.</h1>\n";
		echo "<u>Please</u> delete the file <b>votapedia.setup.php</b> from votapedia extension directory.<br>\n";
		echo "<p><a href='$wgScriptPath'>Return to MediaWiki</a></p>";
	}
} else {

	echo <<<END_HTML
<h1>Welcome to Votapedia installation.</h1>
<p>This script <b>votapedia.setup.php</b> is very dangerous and must be deleted after installation has been completed</p>
<p>Do not run this script if you have already installed votapedia, it will <b>delete</b> tables from database related to votapedia.</p>
Installation Steps:
<ol>
<li>Configure MediaWiki by editing file <b>LocalSettings.php</b>.</li>
<li>Make sure that MediaWiki is working properly.</li>
<li>Edit file <b>extensions/votapedia/votapedia.php</b> to configure votapedia settings.</li>
<li>Edit file <b>extensions/votapedia/votapedia.setup.php</b> to set the master user/password.</li>
<li>Open this script in browser (you are doing it right now)</li>
<li>
<form action="$gvScript/votapedia.setup.php" method="POST">
<input type=submit name=do_install value="Install" />
</form>
</li>
</ol>
END_HTML;
}

?>