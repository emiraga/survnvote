<?php
//
// Send updates to AJAX calls
//
require_once("../SurveySettings.php");

$title='';
if(isset($_GET["pageTitle"]))
{
	$title = $_GET["pageTitle"];
}
$title=stripslashes($title);
$encodedTitle=urlencode($title);

$connectionstring = odbc_connect($gVotingDBname, $gDBUserName, $gDBUserPassword);

//SQL query
$Query = "SELECT * FROM page WHERE title = '$encodedTitle'";

//execute query
$queryexe = odbc_do($connectionstring, $Query);

$pageID = -1;
$showGraph=true;
if(odbc_fetch_row($queryexe))
{
	$pageID = odbc_result($queryexe, 'pageID');
	$showGraph = odbc_result($queryexe, 'showGraph');
}

//SQL query
$Query2 = "SELECT * FROM view_current_survey WHERE title = '$encodedTitle'";

//execute query
$queryexe2 = odbc_do($connectionstring, $Query2);

//query database
$surveyID = array();
$choiceID = array();
$vote = array();
$total=0;
while(odbc_fetch_row($queryexe2))
{
	$pageID = odbc_result($queryexe2, 'pageID');
	$surveyID[] = odbc_result($queryexe2, 'surveyID');
	$choiceID[] = odbc_result($queryexe2, 'choiceID');
	$v = odbc_result($queryexe2, 'vote');
	$vote[] = $v;
}


$questionIndex=0;
$s=$surveyID[0];
$i=0;
$totalVotes= array();
$totalVotes[]=0;
foreach ($choiceID as $c)
{
	if($s!=$surveyID[$i])
	{
		$questionIndex++;
		$s=$surveyID[$i];
		$totalVotes[]=0;
	}
	
	$totalVotes[$questionIndex]+=$vote[$i];
	$i++;
}
$total=max($totalVotes);
echo "total=$total;";

if($showGraph)
{
	$questionIndex=1;
	$choiceIndex=1;
	$s=$surveyID[0];
	$i=0;
	foreach ($choiceID as $c)
	{
		if($s!=$surveyID[$i])
		{
			$questionIndex++;
			$s=$surveyID[$i];
			$choiceIndex=1;
		}
		$width=1;
		if($totalVotes[$questionIndex-1]>0)
			$width= $vote[$i] / $totalVotes[$questionIndex-1] *300;//the maximum width of the bar is 300 pixels.
		
		echo "q$questionIndex"."c$choiceIndex=$width=".$vote[$i].";";
		$i++;
		$choiceIndex++;
	}
}
odbc_close($connectionstring);
?>