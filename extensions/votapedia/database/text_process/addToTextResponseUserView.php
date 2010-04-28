<?php
require_once("../SurveySettings.php");

$messageID=0;
if(isset($_GET["id"]))
{
	$messageID = $_GET["id"];
}
if(isset($_GET["amp;id"]))
{
	$messageID = $_GET["amp;id"];
}

$surveyID='';
if(isset($_GET["surveyID"]))
{
	$surveyID = $_GET["surveyID"];

	if($messageID=='')
		return;
	global $gDataSourceName;
	global $gDBUserName;
	global $gDBUserPassword;
	$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
		
	//add the sms
	$Query = "UPDATE textresponsesms SET accepted=1, acceptedTime=CURRENT_TIMESTAMP WHERE id=$messageID";
	
	$queryexe = odbc_do($connectionstring, $Query);
	odbc_close($connectionstring);
	echo "added<=>$messageID";
}
?>