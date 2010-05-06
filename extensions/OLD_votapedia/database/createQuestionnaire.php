<?php
require_once("../SurveySettings.php");
include('HTTP/Request.php');

$sessionID = $_COOKIE['wikidb_session'];
$userID = $_COOKIE['wikidbUserID'];
$userName = $_COOKIE['wikidbUserName'];
$session = $_COOKIE['wikidb_session'];

$siteName=$_SERVER['HTTP_HOST'];

$title = $_POST["TITLE"];
$title = stripslashes($title);

//validate title
$invalidChar  = '&';
$pos = strpos($title, $invalidChar);

$chcategory = $_POST["chosencategory"];
 if ($chcategory=='Select')
   $chcategory='General';

// Note our use of !==.  Simply != would not work as expected
// because the position of '&' was the 0th (first) character.
if ($pos !== false) {
   echo 'The anonymous survey title can not have "&" in it, please go back and try again.';
   exit;
}
$invalidChar  = '_';
$pos = strpos($title, $invalidChar);
if ($pos !== false) {
   echo 'The anonymous survey title can not have "_" in it, please go back and try again.';
   exit;
}

$title = trim($title);
$encodedTitle=urlencode($title);

$author = $_POST["AUTHOR"];
$createTime=date("Y-m-d H:i:s");

$allowInvalidVotes = 'no'; 
if(isset($_POST["AllowInvalidVotes"]))
{
	if($_POST["AllowInvalidVotes"]=='true')
		$allowInvalidVotes = 'yes';
}

$allowAnonymousVotes = 'no'; 
if(isset($_POST["AllowAnonymousVotes"]))
{
	if($_POST["AllowAnonymousVotes"]=='true')
		$allowAnonymousVotes = 'yes';
}

$mobilePhone = 'null'; 
if(isset($_POST["mobileNumber"]))
{
	$mobilePhone = $_POST["mobileNumber"];
	setcookie ('mobileNumber', $mobilePhone, time() + (365*60*60*24),'/');
}

$isSMSRequired = 'no';
if(isset($_POST["SMSRequired"]))
{
	if($_POST["SMSRequired"]!='no')
		$isSMSRequired = 'yes';
}

$telephoneVoting='yes';
$webVoting='yes';
if(isset($_POST["VOTINGTYPE"]))
{
	if($_POST["VOTINGTYPE"]=='telephone')
		$webVoting = 'no';
	else if ( $_POST["VOTINGTYPE"]=='web')
		$telephoneVoting = 'no';
}

$resultsAtEnd = 'no';
if(isset($_POST["resultsAtEnd"]))
{
	if($_POST["resultsAtEnd"]=='yes')
		$resultsAtEnd = 'yes';
}

$duration = $_POST["DURATION"];
if(is_numeric ( $duration ))
{
	$duration =floatval($duration);
}
else
{
	echo 'The duration must be a numeric value, please go back and try again.';
	exit;
}

if($telephoneVoting=='no')
{
	if($duration<0 || $duration>24*30)
	{
		echo 'The duration you entered is too big or invalid. A Web voting can run up to 30 days. Please go back and try again';
		exit;
	}
}
else
{
	if($duration<0 || $duration>8)
	{
		echo 'The duration you entered is too big or invalid. A Telephone voting can run up to 8 hours. Please go back and try again';
		exit;
	}
}

if($author == 'NULL')
{
	header("Location: http://$site_location/index.php?title=Special:Userlogin&returnto=Special:Voting"); /* Redirect to login page */
	exit;
}

global $gDBUserName;
global $gDBUserPassword;
global $gDataSourceName;
//check whether the survey already exists
$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);

//SQL query
$Query = "SELECT * FROM voting.page WHERE title = '$encodedTitle'";

//execute query
$queryexe = odbc_do($connectionstring, $Query);

//query database
if(odbc_fetch_row($queryexe))
{
	$author = odbc_result($queryexe, 'author');
	$startTime = odbc_result($queryexe, 'startTime');
	echo 'A survey with the same title already exists, please <a href="http://'.$site_location
	.'/index.php?title=Create_MultiQuestion_Survey">go back</a> and choose another title for your anonymous survey or watch '
	.'the existing survey <a href="http://'.$site_location.'/index.php?title='.$encodedTitle.'">'.$title.'</a>.';
	//disconnect from database
	odbc_close($connectionstring);
	exit;
}
//disconnect from database
odbc_close($connectionstring);

require_once("../survey/surveyDAO.php");

//create a new Page
$page = new PageVO();
$page->setTitle($encodedTitle);
$page->setAuthor($author);
$page->setCreateTime($createTime);
$page->setType(4);

//Write data into Database
$surveyDAO = new SurveyDAO();
$databaseWritten=true;

if(!$surveyDAO->insertPage($page))
{
    echo "This is a".$page->getTitle()."<br>";
	echo "Author:".$page->getAuthor()."<br>";
	echo "Created on:".$survey->getCreateTime()."<br>";
	$databaseWritten=false;
}

//send http request to edit the page
$req = &new HTTP_Request("http://$site_location/index.php?");
$req->setMethod(HTTP_REQUEST_METHOD_GET);
$req->clearCookies();
$req->addCookie('wikidb_session', $sessionID);
$req->addCookie('wikidbUserID', $userID);
$req->addCookie('wikidbUserName', $userName);
$req->addCookie('wikidb_session', $session);
//$req->addCookie('wikidbLoggedOut', $loggedOut);
$req->addQueryString('title', $title);
$req->addQueryString('action', 'edit');
$req->sendRequest();
$response1 = $req->getResponseBody();
//echo $response1;
$responseHeader=$req->getResponseHeader();

//find the session token in the response html
$pos=stripos($response1,'" name="wpEditToken" />')-34;//the edit token is 34 chars
$editToken=substr($response1,$pos,34);
//find wpStarttime in the response html
$pos=stripos($response1,'" name="wpStarttime" />')-14;//the edit token is 14 chars
$wpStarttime=substr($response1,$pos,14);
//find wpStarttime in the response html
$pos=stripos($response1,'" name="wpEdittime" />')-14;//the edit token is 14 chars
$wpEdittime=substr($response1,$pos,14);

//send http request to create a voting page
$req->setURL("http://$site_location/index.php?");
$req->setMethod(HTTP_REQUEST_METHOD_POST);
$req->clearPostData();

$req->clearCookies();
$req->addCookie('wikidb_session', $sessionID);
$req->addCookie('wikidbUserID', $userID);
$req->addCookie('wikidbUserName', $userName);

//title
$req->addQueryString('title', $title);

//action
$req->addQueryString('action', 'submit');

//main wiki text area
$wikiText="<!--Please use # to define the choices-->\n";
$wikiText.="<Questionnaire ResultsAtEnd=$resultsAtEnd multipleVoting=$allowInvalidVotes anonymous=$allowAnonymousVotes duration=$duration SMSreply=$isSMSRequired telephoneVoting=$telephoneVoting webVoting=$webVoting>\n";

$wikiText.="Question1\n#choice11\n#choice12\n#choice13\nQuestion2\n#choice21\n#choice22\n";

$wikiText.="</Questionnaire>\n";
$wikiText.="Created by $author\n[[Category:Surveys]]\n[[Category:Surveys by $author]]\n[[Category:Surveys in $chcategory]]\n[[Category:Questionnaires]]";
$req->addPostData('wpTextbox1', $wikiText);

//page summary
$req->addPostData('wpSummary', "New Questionnaire created by $author");
$req->addPostData('wpMinoredit', "1");
$req->addPostData('wpWatchthis', "1");

//save page
$req->addPostData('wpSave', 'Save page');

//hidden inputs
$req->addPostData('wpSection', '');
$req->addPostData('wpEdittime', "$wpEdittime");
$req->addPostData('wpStarttime', "$wpStarttime");
$req->addPostData('wpEditToken', "$editToken");

$req->sendRequest();
$response1 = $req->getResponseBody();
//$responseHeader=$req->getResponseHeader();
//echo $response1;

//create a sub category for the user
$req->clearPostData();
//title
$category="Category:Surveys by $author";
$req->addPostData('title', $category);

//action
$req->addPostData('action', 'submit');

//main wiki text area
$wikiText="[[Category:Surveys by author|$author]]";
$req->addPostData('wpTextbox1', $wikiText);

//page summary
$req->addPostData('wpSummary', "New survey created by $author");
$req->addPostData('wpMinoredit', "1");
$req->addPostData('wpWatchthis', "1");

//save page
$req->addPostData('wpSave', 'Save page');

//hidden inputs
$req->addPostData('wpSection', '');
$req->addPostData('wpEdittime', '21/03/2006T14:52:00');
$req->addPostData('wpEditToken', "$editToken");

$req->sendRequest();
$response1 = $req->getResponseBody();

//create the category for the survey category, eg. Science
$req->clearPostData();
//title
$bcategory="Category:Surveys in $chcategory";
$req->addPostData('title', $bcategory);

$wikiText="[[Category:Surveys in subject|$chcategory]]";
$req->addPostData('wpTextbox1', $wikiText);

//page summary
$req->addPostData('wpSummary', "New survey created by $author");
$req->addPostData('wpMinoredit', "1");
$req->addPostData('wpWatchthis', "1");

//save page
$req->addPostData('wpSave', 'Save page');

//hidden inputs
$req->addPostData('wpSection', '');
$req->addPostData('wpEdittime', '21/03/2006T14:52:00');
$req->addPostData('wpEditToken', "$editToken");

$req->sendRequest();
$response1 = $req->getResponseBody();

//header("Location: /index.php?title=$encodedTitle");

//new page created successfully, redirect to the new page
if($databaseWritten)
	header("Location: http://$site_location/index.php?title=$encodedTitle&action=edit");

exit;
?>