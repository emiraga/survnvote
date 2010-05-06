<?php
//
// Delete page from Mediawiki
// Delete page from voting using SurveyDAO
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
$selectedSurveys = $HTTP_POST_VARS["selectedSurveys"];
$action = $_POST['submit'];

$siteName=$_SERVER['HTTP_HOST'];

global $gDataSourceName;
global $gDBUserName;
global $gDBUserPassword;
$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
$databaseWritten=true;
if($action=='Delete Selected Surveys')
{
	foreach ($selectedSurveys as $survey)
	{
		//SQL query
		$Query = "SELECT * FROM page WHERE title = '$survey'";

		//execute query
		$queryexe = odbc_do($connectionstring, $Query);

		$pageID=0;
		//query database
		if(odbc_fetch_row($queryexe))
		{
			$pageID = odbc_result($queryexe, 'pageID');
		}

		$decodedTitle=urldecode($survey);

		//send http request to edit the page
		$req = &new HTTP_Request("http://$siteName/index.php?");
		$req->setMethod(HTTP_REQUEST_METHOD_GET);
		$req->clearCookies();
		$req->addCookie('wikidb_session', $sessionID);
		$req->addCookie('wikidbUserID', $userID);
		$req->addCookie('wikidbUserName', $userName);
		$req->addCookie('wikidb_session', $session);
		$req->addCookie('wikidbLoggedOut', $loggedOut);
		$req->addQueryString('title', $decodedTitle);
		$req->addQueryString('action', 'delete');
		$req->sendRequest();
		$response1 = $req->getResponseBody();
		//echo $response1;
		$responseHeader=$req->getResponseHeader();

		//find the session token in the response html
		$pos=stripos($response1,'wpEditToken')+20;//the edit token is 32 chars
		$editToken=substr($response1,$pos,32);

		//send http request to create a voting page
		$req->setURL("http://$siteName/index.php?");
		$req->setMethod(HTTP_REQUEST_METHOD_POST);
		$req->clearPostData();
		$req->addCookie('wikidbToken', $token);
		//echo "wpEditToken=$editToken, title=$survey";
		//title

		$req->addPostData('title', $decodedTitle);

		//action
		$req->addPostData('action', 'delete');

		$req->addPostData('wpEditToken', $editToken);
		$req->addPostData('wpConfirmB', 'Confirm');
		$req->addPostData('wpReason', "Deleted by $userName from referer page: $referer.");

		$req->sendRequest();
		$response1 = $req->getResponseBody();
		//echo $response1;

		require_once("../survey/surveyDAO.php");
		//delete Survey from the database
		$surveyDAO = new SurveyDAO();

		if(!$surveyDAO->deletePage($survey))
		{
			echo "failed in surveyDAO->deleteSurvey($survey)";
			$databaseWritten=false;
		}
	}//end foreach
}//end if( $action=='Delete Selected Surveys')

//new page created successfully, redirect to the new page
if($databaseWritten)
	header("Location: http://$siteName/index.php?title=Special:DeleteSurvey");
ob_end_flush();
exit;
?>