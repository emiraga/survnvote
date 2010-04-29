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

$deldate=0;
if(isset($_GET["deldate"]))
{
	$deldate = $_GET["deldate"];
}
if(isset($_GET["amp;deldate"]))
{
	$deldate = $_GET["amp;deldate"];
}

$surveyID='';
if(isset($_GET["surveyID"]))
{
	$surveyID = $_GET["surveyID"];
	global $gDataSourceName;
	global $gDBUserName;
	global $gDBUserPassword;
	$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
		
	//get new incoming SMS
	$Query = "SELECT * FROM textresponsesms WHERE surveyid = $surveyID AND acceptedTime>='$calldate' AND accepted=1 ORDER BY acceptedTime";
	$queryexe = odbc_do($connectionstring, $Query);
	$nothing=true;
	while(odbc_fetch_row($queryexe))
	{
		$id = odbc_result($queryexe, 'id');
		$caller = odbc_result($queryexe, 'sender');
		$content = odbc_result($queryexe, 'sms');
		$accepted = odbc_result($queryexe, 'accepted');
		$acceptedTime = odbc_result($queryexe, 'acceptedTime');
		$username = odbc_result($queryexe, 'username');
		$realname = odbc_result($queryexe, 'realname');
		$content = stripslashes($content);
		$displayCaller='';
		if($realname!='')
			$displayCaller=$realname;
		else if($caller!='')
			$displayCaller=substr($caller,0,strlen($caller)-2).'**';
		else
			$displayCaller=$username;
			
		if(strlen($username)==32)
			$displayCaller='Anonymous';
			
		echo $displayCaller.'<=>'.$content.'<=>'.$acceptedTime.'<=>'.$id.'<br/>';

		$nothing=false;
	}
	
	$Query = "SELECT * FROM textresponsesms WHERE surveyid = $surveyID AND acceptedTime>='$deldate' AND accepted=2 ORDER BY acceptedTime";
	$queryexe = odbc_do($connectionstring, $Query);
	$nothing=true;
	while(odbc_fetch_row($queryexe))
	{
		$id = odbc_result($queryexe, 'id');
		$delTime = odbc_result($queryexe, 'acceptedTime');
		echo "deleted<=>$id<=>$delTime<br/>";
		$nothing=false;
	}
	if($nothing)
		echo 'nothing';
	
	odbc_close($connectionstring);
}
?>