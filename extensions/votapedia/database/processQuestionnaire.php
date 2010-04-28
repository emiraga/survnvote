<?php
include('HTTP/Request.php');
require_once("../survey/surveyDAO.php");
ob_start();

$siteName=$_SERVER['HTTP_HOST'];

$title='';
if(isset($_POST["title"]))
{
	$title = $_POST["title"];
}

$userName = '';
if( isset($_POST['username']))
	$userName=$_POST['username'];

$sessionID='';
if( isset($_COOKIE['wikidb_session']) )
	$sessionID = $_COOKIE['wikidb_session'];
$userID='';
if( isset($_COOKIE['wikidbUserID']) )
	$userID = $_COOKIE['wikidbUserID'];
//$token = $_COOKIE['wikidbToken'];
//$loggedOut = $_COOKIE['wikidbLoggedOut'];

if($userName == 'NULL')
{
	header("Location: http://$site_location/index.php?title=Special:Userlogin&returnto=$title"); /* Redirect to login page */

	/* Make sure that code below does not get executed when we redirect. */
	ob_end_flush();
	exit;
}

$surveyDAO = new SurveyDAO();
$page = $surveyDAO->findByPage($title);
$author = $page->getAuthor();

if($_POST["Submit"]=="Reset survey")
{
	$surveyDAO->deleteSurvey($title);
}

if ($_POST["Submit"]=="Start survey")
{
	if ($page->isActivated())//if the page is already started, just redirect to the page.
	{
		header("Location: http://$site_location/index.php?title=$title&action=purge");
		ob_end_flush();
		exit;
	}

	$multipleVotingAllowed=1;
	if($_POST['multiplevoting']=='no')
		$multipleVotingAllowed=0;

	$anonymousVoteAllowed=0;
	if($_POST['anonymous']=='yes')
		$anonymousVoteAllowed=1;

	$duration=1;
	if(isset($_POST['duration']))
		$duration=$_POST['duration'];
	$duration*=60.0;//convert hours to minutes.

	$smsreply=0;
	if($_POST['smsreply']=='yes')
		$smsreply=1;

	$telephoneVoting=1;//both telephone voting and web voting are allowed
	if($_POST['telephonevoting']=='no')
		$telephoneVoting=0;//web voting only
	if($_POST['webvoting']=='no')
		$telephoneVoting=2;//telephone voting only

	$phone='';
	if(isset($_POST['phone']))
		$phone=$_POST['phone'];

	$showGraph=1;
	if($_POST['resultsatend']=='yes')
		$showGraph=0;

	$page->setAnonymousAllowed($anonymousVoteAllowed);
	$page->setDuration($duration);
	$page->setStartTime(date("Y-m-d H:i:s"));
	$page->setInvalidAllowed($multipleVotingAllowed);
	$page->setPhone($phone);
	$page->setShowGraph($showGraph);
	$page->setSMSRequired($smsreply);
	$page->setTeleVoteAllowed($telephoneVoting);
	$page->setType(4);

	$questions = array();
	$questionIndex=0;
	foreach($_POST["question"] as $question)
	{
		$question=stripslashes($question);
		$survey = new SurveyVO();
		$survey->setQuestion($question);
		$questionIndex++;
		$choices = array();
		$choiceIndex=0;
		foreach($_POST["q$questionIndex".'choice'] as $choice)
		{
			$choice=stripslashes($choice);
			//create each Choice.
			if ($choice != null)
			{
			 $choiceVO = new ChoiceVO();
			 $choiceVO->setChoice($choice);
			 $choices[] = $choiceVO;
			}
		}
		// Insert $choices into Survey
		$survey->setChoices($choices);
		$questions[]=$survey;
	}
	$page->setSurveys($questions);
	$surveyDAO->updatePage($page);

	$page=$surveyDAO->findByPage($title);

	if($telephoneVoting==1 || $telephoneVoting==2)//do not allocate telephone numbers if it is a web only survey.
		$surveyDAO->requestReceivers($page);
}
else if ($_POST["Submit"]=="Continue survey")
{
        $multipleVotingAllowed=1;
	if($_POST['multiplevoting']=='no')
		$multipleVotingAllowed=0;

	$anonymousVoteAllowed=0;
	if($_POST['anonymous']=='yes')
		$anonymousVoteAllowed=1;

	$duration=1;
	if(isset($_POST['duration']))
		$duration=$_POST['duration'];
	$duration*=60.0;//convert hours to minutes.

	$smsreply=0;
	if($_POST['smsreply']=='yes')
		$smsreply=1;

	$telephoneVoting=1;//both telephone voting and web voting are allowed
	if($_POST['telephonevoting']=='no')
		$telephoneVoting=0;//web voting only
	if($_POST['webvoting']=='no')
		$telephoneVoting=2;//telephone voting only

	$phone='';
	if(isset($_POST['phone']))
		$phone=$_POST['phone'];

	$showGraph=1;
	if($_POST['resultsatend']=='yes')
		$showGraph=0;

	$page->setAnonymousAllowed($anonymousVoteAllowed);
	$page->setDuration($duration);
	$page->setInvalidAllowed($multipleVotingAllowed);
	$page->setPhone($phone);
	$page->setShowGraph($showGraph);
	$page->setSMSRequired($smsreply);
	$page->setTeleVoteAllowed($telephoneVoting);
	$page->setType(4);

	$surveyDAO->continueSurvey($page);

	$page=$surveyDAO->findByPage($title);
	if($telephoneVoting==1 || $telephoneVoting==2)//do not allocate telephone numbers if it is a web only survey.
		$surveyDAO->requestReceivers($page);
}
else if ($_POST["Submit"]=="Finish survey")
{
	/*if( $userName != $author )
	{
	echo "You have no privilege to start/stop this survey. Please login in as $author\n";
	ob_end_flush();
	exit;
	}*/
	$page->setEndTime(date("Y-m-d H:i:s"));
	$success=false;
	$surveyDAO->finishSurvey($page);
}
else if ($_POST["Submit"]=="Enter My Vote")
{
	$decodedTitle=urldecode($title);
	$check=md5($decodedTitle);
	$anonymousVoteAllowed=$page->isAnonymousAllowed();
	$u='';
	if( isset($_POST['username']) )
		$u=$userName;
	else
	{
		if($anonymousVoteAllowed)
		{
			if( isset($_COOKIE['anonyuid']) )
			{
				$u=$_COOKIE['anonyuid'];
			}
			else
			{
				echo 'You have to enable cookie to enter your vote. Please enable cookies in your browser settings and <a href="/index.php?title='.$title.'">vote again</a>.';
				ob_end_flush();
				exit;
			}
		}
		else
		{
			echo "This survey only allow registered votApedia users to vote, you have to login to enter your vote.\n";
			ob_end_flush();
			exit;
		}
	}
	include_once("../survey/usr.php");
	$user = new Usr($u);

	$questionIndex=0;
	foreach($_POST["question"] as $question)
	{
		$questionIndex++;
		foreach($_POST["q$questionIndex"."choice"] as $choice)
		{
			$pieces=explode('+',$choice);
			//echo $u.'vote('.$pieces[0].','.$pieces[1].')';
			$user->vote($pieces[0], $pieces[1]);
		}
	}
}
else if($_POST["Submit"]=="Reset survey")
{
	//reset the start time and end time.
	$initDate= date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
	$page->setStartTime($initDate);
	$page->setEndTime($initDate);

	//Write data into Database
	$databaseWritten=true;
	if(!$surveyDAO->updatePage($page))
	{   echo "This is a".$page->getTitle()."<br>";
		echo "Author:".$page->getAuthor()."<br>";
		echo "Created on:".$survey->getCreateTime()."<br>";
		$databaseWritten=false;
	}
	$surveyDAO->deleteSurvey($title);
}
else
{
	echo 'Error: submit unknown.';
	ob_end_flush();
	exit;
}

//send http request to purge the page
$req = &new HTTP_Request("http://$site_location/index.php?");
$req->setMethod(HTTP_REQUEST_METHOD_GET);
$req->clearCookies();
$req->addCookie('wikidb_session', $sessionID);
$req->addCookie('wikidbUserID', $userID);
$req->addCookie('wikidbUserName', $userName);
$req->addQueryString('title', $title);
$req->addQueryString('action', 'purge');
$req->addQueryString('purgecache', 'true');
$req->sendRequest();
$response1 = $req->getResponseBody();

$pos=false;
if ($_POST["Submit"]=="Start survey")
{
	$pos=stripos($response1,'<input type="submit" name="Submit" value="Start survey" />');
}
else if ($_POST["Submit"]=="Finish survey")
{
	$pos=stripos($response1,'<input type="submit" name="Submit" value="Finish survey" />');
}
else if ($_POST["Submit"]=="Enter My Vote")
{
	//$pos=stripos($response1,'still under construction');
}
else if ($_POST["Submit"]=="Restart survey")
{
	$pos=stripos($response1,'<input type="submit" name="Submit" value="Restart survey" />');
}
$i=0;
while ($pos!=false)
{
	//purge the page twice
	$req = &new HTTP_Request("http://$site_location/index.php?");
	$req->setMethod(HTTP_REQUEST_METHOD_GET);
	$req->clearCookies();
	$req->addCookie('wikidb_session', $sessionID);
	$req->addCookie('wikidbUserID', $userID);
	$req->addCookie('wikidbUserName', $userName);
	$req->addQueryString('title', $title);
	$req->addQueryString('action', 'purge');
	$req->addQueryString('purgecache', 'true');
	$req->sendRequest();
	$response1 = $req->getResponseBody();

	if ($_POST["Submit"]=="Start survey")
	{
		$pos=stripos($response1,'<input type="submit" name="Submit" value="Start survey" />');
	}
	else if ($_POST["Submit"]=="Finish survey")
	{
		$pos=stripos($response1,'<input type="submit" name="Submit" value="Finish survey" />');
	}
	else if ($_POST["Submit"]=="Enter My Vote")
	{
		//$pos=stripos($response1,'still under construction');
	}
	else if ($_POST["Submit"]=="Restart survey")
	{
		$pos=stripos($response1,'<input type="submit" name="Submit" value="Restart survey" />');
	}
	$i+=1;
	if($i>10)
		break;
}

//new page created successfully, redirect to the new page
//output the HTTP header
//header( 'Expires: -1' );
//header( 'Cache-Control: no-cache, no-store, max-age=0, must-revalidate' );
//header( 'Pragma: no-cache' );
header("Location: http://$site_location/index.php?title=$title");
ob_end_flush();
exit;
?>