<?php

$userName=$_POST['AUTHOR'];
$siteName=$_SERVER['HTTP_HOST'];
$title = $_POST["TITLE"];
$title = stripslashes($title);
//validate title
$invalidChars  = array('&','#','+','<','>','[',']','|','{','}','/');
$title = str_replace($invalidChars, " ", $title);
$title = trim($title);
$wikiText='';
$titleLength=strlen($title);
if($titleLength>60)
{
	$wikiText.="===$title===\n";
	$title=substr($title,0,60);
	$title.='...';
}
$encodedTitle=urlencode($title);

$chcategory = $_POST["chosencategory"];
 if ($chcategory=='Select')
   $chcategory='General';
$choices = $_POST["CHOICES"];
$choices = stripslashes ($choices);
$numChoices = $_POST["NUMCHOICES"];
$author = $_POST["AUTHOR"];
$allowInvalidVotes = 'no';
if(isset($_POST["AllowInvalidVotes"]))
{
	if($_POST["AllowInvalidVotes"]=='true')
	{
		$allowInvalidVotes = 'yes';
	}
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

$votesallowed =1;
if(isset($_POST["votesallowed"]))
{
	$votesallowed = $_POST["votesallowed"];
	if(is_numeric ( $votesallowed ))
	{
		$votesallowed =floatval($votesallowed);
	}
	else
		$votesallowed=1;
}

$displaytop ='all';
if(isset($_POST["displaytop"]))
{
	$displaytop = $_POST["displaytop"];
	if(is_numeric ( $displaytop ))
	{
		$displaytop =floatval($displaytop);
	}
	else
		$displaytop='all';
}

$duration = $_POST["DURATION"];
if(is_numeric ( $duration ))
{
	$duration =floatval($duration);
}
else
{
	echo 'The duration must be a numeric value, please go back and try again.';
	ob_end_flush();
	exit;
}
if($telephoneVoting=='no')
{
	if($duration<0 || $duration>60*24*30)
	{
		echo 'The duration you entered is too big or invalid. A Web voting can run up to 30 days. Please go back and try again';
		ob_end_flush();
		exit;
	}
}
else
{
	if($duration<0 || $duration>480)
	{
		echo 'The duration you entered is too big or invalid. A Telephone voting can run up to 8 hours. Please go back and try again';
		ob_end_flush();
		exit;
	}
}

if($author == 'NULL')
{
	header("Location: http://$site_location/index.php?title=Special:Userlogin&returnto=Special:Voting"); /* Redirect to login page */

	/* Make sure that code below does not get executed when we redirect. */
	ob_end_flush();
	exit;
}

global $gDBUserName;
global $gDBUserPassword;
global $gDataSourceName;
//check whether the survey already exists
$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);

//SQL query
$Query = "SELECT * FROM page WHERE title = '$encodedTitle'";

//execute query
$queryexe = odbc_do($connectionstring, $Query);

//query database
if(odbc_fetch_row($queryexe))
{
	$author = odbc_result($queryexe, 'author');
	$startTime = odbc_result($queryexe, 'startTime');
	echo 'A survey with the same title already exists, please <a href="/index.php?title=Create_Survey">go back</a> and choose another title for your survey or watch the existing survey <a href="/index.php?title='.$encodedTitle.'">'.$title.'</a>.';
	//disconnect from database
	odbc_close($connectionstring);
	ob_end_flush();
	exit;
}
//disconnect from database
odbc_close($connectionstring);

require_once("../survey/surveyDAO.php");
//create a new Page
$page = new PageVO();
$page->setTitle($encodedTitle);
$page->setAuthor($author);
//Write data into Database
$surveyDAO = new SurveyDAO();
$databaseWritten=true;
if(!$surveyDAO->insertPage($page))
{   echo "This is a".$page->getQuestion()."<br>";
	echo "Question:".$page->getQuestion()."<br>";
	echo "Start at:".$page->getStartTime()."<br>";
	echo "End at:".$page->getEndTime()."<br>";
	echo "Duration:".$page->getDuration()."Min<br>";
	echo "Author:".$page->getAuthor()."<br>";
	echo "Anonymous vote allowed:".$page->isInvalidAllowed()."<br>";
	echo "Mobile phone:".$page->getPhone()."<br>";
	echo "SMS Required:".$page->isSMSRequired()."<br>";
	$databaseWritten=false;
}


//send http request to edit the page
$req = &new HTTP_Request("http://$site_location/index.php?");
$req->setMethod(HTTP_REQUEST_METHOD_GET);
$req->clearCookies();
$req->addCookie('wikidb_session', $sessionID);
$req->addCookie('wikidbUserID', $userID);
$req->addCookie('wikidbUserName', $userName);
//$req->addCookie('wikidb_session', $session);
//echo $sessionID." ".$userID." ".$userName." ".$session;
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

//echo "</br>$editToken</br>";

//send http request to create a voting page
$req = &new HTTP_Request("http://$site_location/index.php?");
$req->setMethod(HTTP_REQUEST_METHOD_POST);
$req->addHeader("Referer", "http://$site_location/index.php?title=$title&action=edit");

$req->clearCookies();
$req->addCookie('wikidb_session', $sessionID);
$req->addCookie('wikidbUserID', $userID);
$req->addCookie('wikidbUserName', $userName);

$req->clearPostData();

//title
$req->addQueryString('title', $title);

//action
$req->addQueryString('action', 'submit');

//main wiki text area
$wikiText.="<!--You can write your question inside the ==='''  '''=== mark below, e.g. ==='''Do you like blue sky?'''=== -->\n";
$wikiText.="==='''  '''===\n";
$wikiText.="<choice background=[[Image:Csiro_large.jpg]] multipleVoting=$allowInvalidVotes anonymous=$allowAnonymousVotes votesAllowed=$votesallowed duration=$duration SMSreply=$isSMSRequired telephoneVoting=$telephoneVoting webVoting=$webVoting resultsAtEnd=$resultsAtEnd displayTop=$displaytop>\n";

if(isset($_POST["CHOICES"]))
{
	$wikiText.=$choices;
}
else
{
	for($i=1;$i<=$numChoices;$i++)
	{
		$wikiText.="Choice $i\n";
	}
}
$wikiText.="</choice>\n    Created by $author\n[[Category:Surveys]]\n[[Category:Surveys by $author]]\n[[Category:Surveys in $chcategory]]\n[[Category:Simple Surveys]]";
$req->addPostData('wpTextbox1', $wikiText);
//$req->addPostData('wpTextbox1', "heyoooooooooooooooo");

//page summary
$req->addPostData('wpSummary', "");
$req->addPostData('wpMinoredit', "1");
$req->addPostData('wpWatchthis', "1");

//save page
$req->addPostData('wpSave', 'Save page');

//hidden inputs
$req->addPostData('wpSection', '');
$req->addPostData('wpEdittime', "$wpEdittime");
$req->addPostData('wpStarttime', "$wpStarttime");
$req->addPostData('wpEditToken', "$editToken");
//echo 'sending request';
$req->sendRequest();
$response1 = $req->getResponseBody();
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
$req->addPostData('wpEdittime', '25/02/2010T14:52:00');
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
$req->addPostData('wpWatchthis', "on");

//save page
$req->addPostData('wpSave', 'Save page');

//hidden inputs
$req->addPostData('wpSection', '');
$req->addPostData('wpEdittime', '21/03/2006T14:52:00');
$req->addPostData('wpEditToken', "$editToken");

$req->sendRequest();
$response1 = $req->getResponseBody();

//echo "Anonymous vote allowed:".$survey->isInvalidAllowed()."<br>";
//new page created successfully, redirect to the new page
if($databaseWritten)
	header("Location: http://$site_location/index.php?title=$encodedTitle");
ob_end_flush();
exit;
?>