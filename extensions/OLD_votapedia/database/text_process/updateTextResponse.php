<?php
require_once("../SurveySettings.php");

$calldate=0;
if(isset($_GET["calldate"]))
{
	$calldate = $_GET["calldate"];
}
if(isset($_GET["amp;calldate"]))
{
	$calldate = $_GET["amp;calldate"];
}

$surveyID='';
if(isset($_GET["surveyID"]))
{
	$surveyID = $_GET["surveyID"];

	global $gDataSourceName;
	global $gDBUserName;
	global $gDBUserPassword;
	$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
	//SQL query
	$Query = "SELECT count(username) as num FROM ( SELECT DISTINCT username FROM textresponsesms WHERE surveyid = $surveyID) AS temp";
	
	//execute query
	$queryexe = odbc_do($connectionstring, $Query);
	
	$numParticipant=0;
	if(odbc_fetch_row($queryexe))
	{
		$numParticipant = odbc_result($queryexe, 'num');
	}
	echo "total<=>$numParticipant<br/>";
	
	//get new incoming SMS
	$Query = "SELECT * FROM textresponsesms WHERE surveyid = $surveyID AND time>'$calldate' ORDER BY time";
	$queryexe = odbc_do($connectionstring, $Query);
	$nothing=true;
	while(odbc_fetch_row($queryexe))
	{
		$id = odbc_result($queryexe, 'id');
		$caller = odbc_result($queryexe, 'sender');
		$content = odbc_result($queryexe, 'sms');
		$time = odbc_result($queryexe, 'time');
		$username = odbc_result($queryexe, 'username');
		$realname = odbc_result($queryexe, 'realname');
		$displayCaller='';
		if($realname!='')
			$displayCaller=$realname;
		else if($caller!='')
			$displayCaller=substr($caller,0,strlen($caller)-2).'**';
		else
			$displayCaller=$username;
			
		if(strlen($username)==32)
			$displayCaller='Anonymous';
		echo $displayCaller.'<=>'.$content.'<=>'.$time.'<=>'.$id.'<br/>';
		$nothing=false;
	}
	if($nothing)
		echo 'nothing';
	
	odbc_close($connectionstring);
}
?>