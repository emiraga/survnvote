<?php
//
//add marks together of multiple quizes
//
require_once("../SurveySettings.php");
include('HTTP/Request.php');
ob_start();
$sessionID = $_COOKIE['wikidb_session'];
$userID = $_COOKIE['wikidbUserID'];
$userName = $_COOKIE['wikidbUserName'];
$session = $_COOKIE['wikidb_session'];
//$loggedOut = $_COOKIE['wikidbLoggedOut'];
$referer=$_SERVER['HTTP_REFERER'];
if(isset($HTTP_POST_VARS["selectedQuizs"]))
	$selectedQuizs = $HTTP_POST_VARS["selectedQuizs"];
else
{
	echo 'Please select two or more quizs to combine their results.';
	$selectedQuizs = array();
}
$action = $_POST['submit'];

$siteName=$_SERVER['HTTP_HOST'];

global $gDataSourceName;
global $gDBUserName;
global $gDBUserPassword;
$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
if($action=='Combine Results of Selected Quizs')
{
	echo '<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#cbdced ><TR><th>User Name</th><th>Total Mark</th></TR>';
	$record=array();
	foreach ($selectedQuizs as $quiz)
	{
		//SQL query
		$Query = "SELECT * FROM view_quiz_result WHERE pageID = '$quiz'";

		//execute query
		$queryexe = odbc_do($connectionstring, $Query);

		$pageID=0;
		//query database
		while(odbc_fetch_row($queryexe))
		{
			$user = odbc_result($queryexe, 'voterid');
			$mark = odbc_result($queryexe, 'marks');
			if($user==NULL)
				$user = odbc_result($queryexe, 'phone');
			if( !isset($record["$user"]) )
				$record["$user"]=0;
			$record["$user"]+=$mark;
		}
		
	}//end foreach
	arsort($record);
	foreach($record as $u=>$m)
		echo "<TR><TD>$u</TD><TD>$m</TD></TR>";
	echo '</TABLE>';
}//end if( $action=='Combine Results of Selected Quizs')

ob_end_flush();
exit;
?>