<?php
//
// Calculates results
// writes to table quizresultsms
//
require_once("../SurveySettings.php");
require_once("../incomingSMS/sendSMS.php");
ob_start();

$siteName=$_SERVER['HTTP_HOST'];

$pageID=0;
if(isset($_POST["page"]))
{
	$pageID = $_POST["page"];
}
$pageIDs=explode('|',$pageID);

$sender='unknown';
if(isset($_POST["sender"]))
{
	$sender = $_POST["sender"];
}

$totalPoints=0;
if(isset($_POST["totalpoint"]))
{
	$totalPoints = $_POST["totalpoint"];
}

if(isset($HTTP_POST_VARS["sendsms"]))
	$sendsms = $HTTP_POST_VARS["sendsms"];
else
{
	echo 'Please the users to send to.';
	$sendsms = array();
}

global $gVotingDBname;
global $gDBUserName;
global $gDBUserPassword;
	
$connectionstring = odbc_connect($gVotingDBname, $gDBUserName, $gDBUserPassword);

$Where='';
$loop=0;
foreach($pageIDs as $quiz)
{
	if($quiz!='')
	{
		if($loop!=0)
			$Where.="OR ";
	
		$Where .="pageID = $quiz ";
		$loop++;
	}
}
		
$Query = "SELECT * FROM view_quiz_result WHERE $Where ORDER BY marks DESC";
$queryexe = odbc_do($connectionstring, $Query);
$result = array();
$phones = array();
$ranks = array();
$numParticipant=0;
$meanScore=0;
while(odbc_fetch_row($queryexe))
{
	$v = odbc_result($queryexe, 'voterid');
	if($v==NULL)
		$v = odbc_result($queryexe, 'phone');
	$mark =  odbc_result($queryexe, 'marks'); 
	$phone = odbc_result($queryexe, 'phone'); 
	if(!isset($result["$v"]))
		$result["$v"]=0;
	$result["$v"]+=$mark;
	$phones["$v"]=$phone;
	$meanScore+=$mark;
}
$numParticipant=count($result);
$standardDeviation=0.0;
if($numParticipant>0)
{
	$meanScore/=$numParticipant;
	foreach($result as $m)
	{
		$standardDeviation+=(($m-$meanScore)*($m-$meanScore));
	}
	$standardDeviation/=$numParticipant;
	$standardDeviation=sqrt($standardDeviation);
}

//calculate ranks
arsort($result);
$rank=1;
foreach($result as $u=>$m)
{
	$ranks["$u"]=$rank;
	$rank++;
}

foreach ($sendsms as $to)
{
	if(isset($result["$to"]))
	{
		if(isset($phones["$to"]))
		{
			$user=$to;
			$phone=$phones["$to"];
			$pos = strpos($phone, '04');
			if($pos!==false)
			{
				$mark=$result["$to"];
				$rank=$ranks["$to"];
				$content="Your marks were $mark out of $totalPoints which ranks you $rank out of $numParticipant participants. The mean marks were $meanScore and the standard deviation was $standardDeviation. ".$gSiteName;
				sendSMS($phone,$content);
				
				$Query = "INSERT INTO quizresultsms VALUES ('$pageID','$user','$phone','$sender','$content',CURRENT_TIMESTAMP)";
				$queryexe = odbc_exec($connectionstring, $Query);
			}
		}
	}
}
$referer=$_SERVER['HTTP_REFERER'];
header("Location: $referer");
ob_end_flush();
exit;
?>