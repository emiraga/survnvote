<?php
//
//
// Deliver quiz updates to AJAX call
//
//
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
//echo $calldate;
$pageID='';
if(isset($_GET["pageID"]))
{
	$pageID = $_GET["pageID"];

	$connectionstring = odbc_connect($gVotingDBname, $gDBUserName, $gDBUserPassword);
	//SQL query
	$Query = "SELECT count(voterID) as num FROM view_quiz_result_by_voterid WHERE pageid = '$pageID'";
	
	//execute query
	$queryexe = odbc_do($connectionstring, $Query);
	
	$numParticipant=0;
	if(odbc_fetch_row($queryexe))
	{
		$numParticipant = odbc_result($queryexe, 'num');
	}
	echo "total=$numParticipant<br/>";
	
	//find index of each question
	$surveyIndex = array();
	$i=1;
	//gete the surveys
	$Query = "SELECT * FROM survey WHERE pageID = $pageID ORDER BY surveyID";
	$queryexe = odbc_do($connectionstring, $Query);
	while(odbc_fetch_row($queryexe))
	{
		$id = odbc_result($queryexe, 'surveyID');
		$surveyIndex["$id"]=$i;
		$i++;
	}
		
	//get new incoming calls
	$Query = "SELECT * FROM view_recent_call3 WHERE pageID = $pageID AND voteDate>'$calldate' ORDER BY voteDate";
	$queryexe = odbc_do($connectionstring, $Query);
	$nothing=true;
	$callerRecorded=array();
	while(odbc_fetch_row($queryexe))
	{
		$caller = odbc_result($queryexe, 'voterID');
		if(!isset($callerRecorded["$caller"]))
		{
			$question = odbc_result($queryexe, 'surveyid');
			$voteDate = odbc_result($queryexe, 'voteDate');
			$realname = odbc_result($queryexe, 'user_real_name');
			$questionIndex = $surveyIndex["$question"];
			$displayCaller='';
			if($realname!='')
				$displayCaller=$realname;
			else
				$displayCaller=substr($caller,0,strlen($caller)-2).'**';
			$len=strlen($displayCaller);
			if($len>13)
				$displayCaller=substr($displayCaller,0,12).'...';
			
			$answers="Q$questionIndex";
			$callerRecorded["$caller"]=	$answers;
			//get the answer record for this caller
			$Query2 = "SELECT * FROM view_recent_call2 WHERE pageID = $pageID AND voterID='$caller' AND voteDate<> '$voteDate' ORDER BY voteDate DESC";
			$queryexe2 = odbc_do($connectionstring, $Query2);
			while(odbc_fetch_row($queryexe2))
			{
				$question = odbc_result($queryexe2, 'surveyid');
				$questionIndex = $surveyIndex["$question"];
				$answers.=" Q$questionIndex";
			}
			echo "$answers=$displayCaller=$voteDate<br/>";
			$nothing=false;
		}
	}
	if($nothing)
		echo 'nothing';
	
	odbc_close($connectionstring);
}
?>