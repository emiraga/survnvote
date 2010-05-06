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
if( isset($_COOKIE['wikidbUserName']) )
	$userName = $_COOKIE['wikidbUserName'];
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

//------------------------------------------------------
//--- Case 1 --- author starting the survey
//------------------------------------------------------

if ($_POST["Submit"]=="Start survey")
{
	if( $userName != $author )
	{
	echo "You have no privilege to start/stop this survey. Please try login in again as $author.\n";
	echo "Currently, your user name is $userName";
	ob_end_flush();
	exit;
	}
        /*
	if ($page->isActivated())//if the page is already started, just redirect to the page.
	{
	header("Location: http://$site_location/index.php?title=$title&action=purge");
	ob_end_flush();
	exit;
	}*/

	$multipleVotingAllowed=1;
	if($_POST['multiplevoting']=='no')
		$multipleVotingAllowed=0;

	$anonymousVoteAllowed=0;
	if($_POST['anonymous']=='yes')
		$anonymousVoteAllowed=1;
		
	$duration=60;
	if(isset($_POST['duration']))
		$duration=$_POST['duration'];
		$duration *=60;
	
	$smsreply=0;
	if($_POST['smsreply']=='yes')
		$smsreply=1;
	
	$telephoneVoting=1;
	if($_POST['webonly']=='yes')
		$telephoneVoting=0;
	
	$phone='';
	if(isset($_POST['phone']))
		$phone=$_POST['phone'];

	$displayTop=3;
        if(isset($_POST['displaytop']))
		$displayTop=$_POST['displaytop'];

	$page->setAnonymousAllowed($anonymousVoteAllowed);
	$page->setDuration($duration);
	$page->setStartTime(date("Y-m-d H:i:s"));
	$page->setInvalidAllowed($multipleVotingAllowed);
	$page->setPhone($phone);
	$page->setSMSRequired($smsreply);
	$page->setTeleVoteAllowed($telephoneVoting);
	$page->setType(3);
	$page->setDisplayTop($displayTop);


	$questions = array();
	$questionIndex=0;
	foreach($_POST["question"] as $question)
	{
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
		$choiceIndex++;
		$choiceVO->setChoiceID($choice);
		$choices[] = $choiceVO;
	       }
             }
          // Insert $choices into Survey
	    $survey->setChoices($choices);
	    $questions[]=$survey;
          }

          $expositions = array();
	  $expositionIndex=0;
	  foreach($_POST["exposition"] as $exposition)
	  {
            echo $exposition."****";
            $presentationVO = new PresentationVO();
	    $presentationVO->setPresentation($exposition);
	    $expositionIndex++;
	    $presentationVO->setPresentationID($expositionIndex);
	    $presentationVO->setActive(0);
            $expositions[]=$presentationVO;
	   }
	   // $survey->setSurveyID($presentations);
	    $survey->setPresentations($expositions );


        $page->setSurveys($questions);
         //print_r($page);
	$surveyDAO->updatePage($page);

        $page=$surveyDAO->findByPage($title);

        //if($_POST["chosenpresentation"]!=0)

	if($telephoneVoting==1)//do not allocate telephone numbers if it is a web only survey.
	  $surveyDAO->requestReceivers($page);
        $surveyDAO->startSurvey($page);

}//end of starting survey

//-------------------------------------------------
//--- Case 2 --- author finishing the survey
//-------------------------------------------------

else if ($_POST["Submit"]=="Finish survey")
{
	if( $userName != $author )
	{
	echo "You have no privilege to start/stop this survey. Please login in as $author\n";
	ob_end_flush();
	exit;
	}
	$page->setEndTime(date("Y-m-d H:i:s"));
	$success=false;
	$surveyDAO->finishSurvey($page);
}

//-----------------------------------------------
//--- Case 3 --- author running presentation
//-----------------------------------------------
/*
else if ($_POST["Submit"]=="chosenpresentation")
{
	if( $userName != $author )
	{
	echo "You have no privilege to run this presentation. Please try login in again as $author.\n";
	echo "Currently, your user name is $userName";
	ob_end_flush();
	exit;
         }
*/
else if ($_POST["chosenexposition"])
{
  if( $userName != $author )
	{
	echo "You have no privilege to run this exposition. Please try login in again as $author.\n";
	echo "Currently, your user name is $userName";
	ob_end_flush();
	exit;
         }

     $chosenpres= $_POST["chosenexposition"];
     if($_POST["chosenexposition"]!=0)
     {
       $page = $surveyDAO->findByPage($title);
       $surveys = $page->getSurveys();

       foreach($surveys as $survey)
       {
        $surveyID=$survey->getSurveyID();
        $surveyDAO->activatePresentation($surveyID,$chosenpres);
       }
      header("Location: http://$site_location/index.php?title=$title&action=purge");
      ob_end_flush();
      exit;
      }

}//end run exposition

//-----------------------------------------------
//--- Case 4 --- not the author
//-----------------------------------------------
/*
if($_POST["Submit"]=="Enter My Vote" )
{
	$cookieID=$_POST['testCookie'];
	if( !isset($_COOKIE["testCookie$cookieID"]) )
	{
		echo 'You have to enable cookie to enter your vote. Please enable cookies in your browser settings and <a href="/index.php?title='.$title.'">vote again</a>.';
		ob_end_flush();
		exit;
	}
	if ($_COOKIE["testCookie$cookieID"]!=$_POST['testCookie'])
	{
		echo 'You have to enable cookie to enter your vote. Please enable cookies in your browser settings and <a href="/index.php?title='.$title.'">vote again</a>.';
		ob_end_flush();
		exit;
	}
}
*/

else if ($_POST["Submit"]=="Enter My Vote")
{
	$decodedTitle=urldecode($title);
	$check=md5($decodedTitle);
	$anonymousVoteAllowed=$page->isAnonymousAllowed();
	
	$page = $surveyDAO->findByPage($title);
        $surveys = $page->getSurveys();

       foreach($surveys as $survey)
       {
        $surveyID=$survey->getSurveyID();
        $surveyPresentations=$survey->getPresentations();
        }

	$pactiveID=0;
	foreach($surveyPresentations as $surveyPresentation)
       {
         $presentationID=$surveyPresentation->getPresentationID();
         $pactive=$surveyPresentation->getActive();

         if ($pactive==1)
           $pactiveID=$presentationID;
        }

	$u='';

	if( isset($_COOKIE['wikidbUserID']) )
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
				//generate a unique id for the user and set the cookie.
				//$u = md5(uniqid(rand(), true));
				//setcookie('anonyuid', $u, time()+10*365*24*3600,'/');//expire after ten years
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
	setcookie($check, 'voted', time()+24*3600,'/');
	

	$questionIndex=0;
	foreach($_POST["question"] as $question)
	{
	  $questionIndex++;
	  foreach($_POST["q$questionIndex"."choice"] as $choice)
             //echo $choice."******";
          $user->vote($surveyID,$choice,$pactiveID);
	 }
        
}

else
{
	echo 'Error: submit unknown.';
	ob_end_flush();
	exit;
}

//-----------------------------------------------
//---send http request to purge the page---------
//-----------------------------------------------

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
/*else if ($_POST["Submit"]=="run presentation")
{
	$pos=stripos($response1,'<input type="submit" name="Submit" value="run presentation" />');
}*/
else if ($_POST["Submit"]=="Finish survey")
{
	$pos=stripos($response1,'<input type="submit" name="Submit" value="Finish survey" />');
}
else if ($_POST["Submit"]=="Enter My Vote")
{
	$pos=stripos($response1,'<input type="submit" name="Submit" value="Enter My Vote" />');
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
/*	else if ($_POST["Submit"]=="run presentation")
	{
		$pos=stripos($response1,'<input type="submit" name="Submit" value="run presentation" />');
	} */
	else if ($_POST["Submit"]=="Finish survey")
	{
		$pos=stripos($response1,'<input type="submit" name="Submit" value="Finish survey" />');
	}
	else if ($_POST["Submit"]=="Enter My Vote")
	{
		$pos=stripos($response1,'<input type="submit" name="Submit" value="Enter My Vote" />');
	}
	$i++;
	if($i>10)
	  break;
}

//new page created successfully, redirect to the new page
//output the HTTP header
//header( 'Expires: -1' );
//header( 'Cache-Control: no-cache, no-store, max-age=0, must-revalidate' );
//header( 'Pragma: no-cache' );
header("Location: http://$site_location/index.php?title=$title&action=purge");
ob_end_flush();
exit;
?>