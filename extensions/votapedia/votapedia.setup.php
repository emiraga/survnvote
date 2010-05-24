<?php
define('MEDIAWIKI',true);
define('VOTAPEDIA_SETUP',true);

require_once('../../LocalSettings.php');

/*
 * Enter user/pass of a admin account for mysql that
 * has priviledges for CREATE and DELETE of tables.
*/
global $vgDBUserName, $vgDBUserPassword;
$vgDBUserName       = 'root'; // Master username for database (user has permission to create tables)
$vgDBUserPassword   = '';     // Password for database master user

function vfDoSetup($justprint = false)
{
    global $vgPath, $vgDB, $vgDBPrefix;
    require_once("$vgPath/Common.php");

    $sql = <<<END_SQL
DROP TABLE IF EXISTS {$vgDBPrefix}page;
DROP TABLE IF EXISTS {$vgDBPrefix}presentation;
DROP TABLE IF EXISTS {$vgDBPrefix}survey;
DROP TABLE IF EXISTS {$vgDBPrefix}surveychoice;
DROP TABLE IF EXISTS {$vgDBPrefix}surveyrecord;
DROP TABLE IF EXISTS {$vgDBPrefix}usedreceivers;

--
-- Table structure for table page
--
CREATE TABLE IF NOT EXISTS {$vgDBPrefix}page (
  pageID int(11) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(512) NOT NULL,
  startTime datetime NOT NULL,
  endTime datetime NOT NULL,
  duration int(11) NOT NULL,
  author varchar(20) NOT NULL,
  phone varchar(20) DEFAULT NULL,
  createTime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  smsRequired tinyint(1) NOT NULL DEFAULT '0', 
  showGraph tinyint(1) NOT NULL DEFAULT '1',
  displayTop tinyint(4) NOT NULL DEFAULT '0',
  surveyType tinyint(4) NOT NULL DEFAULT '1',
  votesAllowed tinyint(8) unsigned NOT NULL DEFAULT '1',
  subtractWrong tinyint(1) NOT NULL DEFAULT '0',
  privacy tinyint(4) NOT NULL DEFAULT '1',
  phonevoting varchar(5) DEFAULT 'anon',
  webvoting varchar(5) DEFAULT 'anon',
  PRIMARY KEY (pageID),
  KEY pageID (pageID)
);

-- --------------------------------------------------------

--
-- Table structure for table presentation
--

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
            if(! $justprint)
                $vgDB->Execute($sql);
            else
                echo htmlspecialchars($sql) . ";\n";
        }
    }
}
echo '<html><head><title>votapedia installation</title></head><body>';
global $vgScript, $wgScriptPath;
if(defined('VOTAPEDIA_TEST') || isset($_POST['do_install']))
{
    try
    {
        vfDoSetup();
        echo "<h1>votapedia installation is complete.</h1>\n";
    }
    catch(Exception $e)
    {
        echo "<p><b>Error: </b>".htmlspecialchars($e->getMessage())."</p>";
        echo "<h2>Alternative: Manual setup</h2>";
        echo "<p>Most likely username/password combination is wrong or you don't have sufficient priviledges. You can try again to enter correct values in setup file.</p>";
        echo "<p>If you cannot get super/master user account, then execute this SQL manually (using cPanel/phpMyAdmin or similar).</p>";
        echo "<pre style='background-color: #CCC;'>";
        vfDoSetup(true);
        echo "</pre>";
    }
    if(isset($_POST['do_install']))
    {
        echo "When you are done with installation, <u>please</u> delete the file <b>votapedia.setup.php</b> from votapedia extension directory.<br>\n";
        echo "<p><a href='$wgScriptPath'>Return to MediaWiki</a></p>";
    }
}
else
{

    echo <<<END_HTML
<h1>Welcome to votapedia installation.</h1>
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
<form action="$vgScript/votapedia.setup.php" method="POST">
<input type=submit name=do_install value="Install" />
</form>
</li>
</ol>
END_HTML;
}

echo '</body></html>';

?>