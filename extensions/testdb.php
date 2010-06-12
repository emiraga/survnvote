<?php

if(!defined('MEDIAWIKI')) define('MEDIAWIKI',true);
define('VOTAPEDIA_SETUP',true);

@require_once('../LocalSettings.php');
@include_once("../AdminSettings.php");
//user/pass of a admin account for mysql that has priviledges for CREATE and DELETE of tables.
$vgDBUserName = $wgDBadminuser;
$vgDBUserPassword = $wgDBadminpassword;
$vgDBName = "test_performance";

require_once("$vgPath/Common.php");

$vgDB->Execute("DROP TABLE IF EXISTS {$vgDBPrefix}surveyrecord ");
$vgDB->Execute("TRUNCATE adodb_logsql");

$vgDB->Execute(
"CREATE TABLE IF NOT EXISTS {$vgDBPrefix}surveyrecord (
  ID             int NOT NULL AUTO_INCREMENT,
  voterID        int NOT NULL,
  surveyID       int NOT NULL,
  presentationID TINYINT NOT NULL,
  choiceID       TINYINT NOT NULL,
  PRIMARY KEY    (ID),
  INDEX          (voterID)
);");

$vgDB->LogSQL(true);

for($i=0;$i<3000; $i++)
{
    if($i % 100 == 0)    echo "$i, ";
    $sql = "INSERT INTO {$vgDBPrefix}surveyrecord (voterID, surveyID, presentationID, choiceID) VALUES";
    $values = array();
    for($j=0;$j<500;$j++)
    {
        $values[] = sprintf("(%d,%d,%d,%d)",rand(1,1000),rand(1,1000),rand(1,5),rand(1,5));
    }
    $vgDB->Execute( $sql. join(',',$values) );
}
echo "\n";

$r = $vgDB->GetOne("SELECT count(*) FROM {$vgDBPrefix}surveyrecord WHERE voterID = 45"); echo "=45 Rows: $r\n";
$r = $vgDB->GetOne("SELECT count(*) FROM {$vgDBPrefix}surveyrecord WHERE voterID = 54"); echo "=54 Rows: $r\n";
$r = $vgDB->GetOne("SELECT count(*) FROM {$vgDBPrefix}surveyrecord WHERE surveyID = 45"); echo "=45 Rows: $r\n";
$r = $vgDB->GetOne("SELECT count(*) FROM {$vgDBPrefix}surveyrecord WHERE surveyID = 54"); echo "=54 Rows: $r\n";

explain("SELECT count(*) FROM {$vgDBPrefix}surveyrecord WHERE surveyID = 54");
explain("SELECT count(*) FROM {$vgDBPrefix}surveyrecord WHERE voterID = 54");

$a = $vgDB->GetALL("SELECT sql1, timer FROM adodb_logsql"); foreach($a as $b){   echo "$b[timer]  <-   $b[sql1]\n"; }

function explain($sql, $params = array())
{
    global $vgDB;
    $r = $vgDB->GetALL("EXPLAIN $sql", $params);
    $r = $r[0];
    echo $r['Extra'] . "  <- $sql\n";
}


