<?php
old_stuff();
# Quiz Tag WikiMedia extension
# with WikiMedia's extension mechanism it is possible to define
# a Quiz Tag of this form
# <Quiz>
# Question 1
# #great   
# #good
# #bad
# Question 2
# #answer   
# #another answer
# </Quiz>
# the function registered by the extension gets the text between the
# tags as input and can transform it into a voting page (HTML code).
# Note: iText but directly
#       included in the HTML output. The output is not interpreted as Wik markup is not supported.
# To activate the extension, include it from your LocalSettings.php
# with: include("extensions/YourExtensionName.php");

require_once( 'filerepo/Image.php' );
require_once("./SurveySettings.php");

$wgExtensionFunctions[] = "wfQuizTagExtension";

function wfQuizTagExtension() {
    global $wgParser;
    # register the extension with the WikiText parser
    # the first parameter is the name of the new tag.
    # In this case it defines the tag <Quiz> ... </Quiz>
    # the second parameter is the callback function for
    # processing the text between the tags
    $wgParser->setHook( "Quiz", "renderQuiz" );
	$wgParser->disableCache();
}

//calculate median:
//number median ( number arg1, number arg2 [, number ...] )
//number median ( array numbers )
function median()
{
   $args = func_get_args();

   switch(func_num_args())
   {
       case 0:
           trigger_error('median() requires at least one parameter',E_USER_WARNING);
           return false;
           break;

       case 1:
           $args = array_pop($args);
           // fallthrough

       default:
           if(!is_array($args)) {
               trigger_error('median() requires a list of numbers to operate on or an array of numbers',E_USER_NOTICE);
               return false;
           }

           sort($args);
          
           $n = count($args);
           $h = intval($n / 2);

           if($n % 2 == 0) {
               $median = ($args[$h] + $args[$h-1]) / 2;
           } else {
               $median = $args[$h];
           }

           break;
   }
  
   return $median;
}

# The callback function for converting the input text to HTML output
function renderQuiz( $input, $argv ) {
    # $argv is an array containing any arguments passed to the
    # extension like <Quiz argument="foo" bar>..
	global $wgRequest,$wgUser,$wgParser,$wgTitle,$wgOut;
	global $gVotingDBname;
	global $gDBUserName;
	global $gDBUserPassword;
	$output='';

	$wgParser->disableCache();//disable cache because mobile and desktop skin requires different ways to render the barchart.

	//get the title of the page
	$pageTitle=$wgRequest->getVal( "title" ); //$wgTitle->getText(); doesn't work here because the special page is included in a normal wiki page.
	//get rid of the underbars in the pageTitle
	$trans = array("_" => " ");//, "hi" => "hello");
	$title=strtr($pageTitle, $trans);
	$encodedTitle=urlencode($title);

	//Give a warning message when there are more than one choice tag in the page.
	$detectSecondQuizTag=md5($title).time();
	$detectSecondQuizTag2=md5($title).(time()-1);
	if(isset($_SESSION[$detectSecondQuizTag]) || isset($_SESSION[$detectSecondQuizTag2]))
	{
		return "<strong>Warning:</strong> A survey page can only have one &lt;Quiz&gt; tag. Please <a href=\"index.php?title=$encodedTitle&action=edit\">edit</a> this page and put all your choices in ONE &lt;Quiz&gt; tag. If you need more than one quiz, put all of them inside the &lt;Quiz&gt; tag.<br />";
		exit;
	}
	$action=$wgRequest->getVal( "action" );
	//if($action != 'submit' && $_GET['purgecache']!='true')//the session variable should not be set when submitting the page.
	if($action != 'submit')
		if(isset($_GET['purgecache']))
			if($_GET['purgecache']!='true')
				$_SESSION[$detectSecondQuizTag]='1';
	
	global $gDBUserName;
	global $gDBUserPassword;
	global $gDataSourceName;
	$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);

	//SQL query
	$Query = "SELECT * FROM page WHERE title = '$encodedTitle'";

	//execute query
	$queryexe = odbc_do($connectionstring, $Query);

	//query database
	$pageID = -1;
	$resultIsNull=TRUE;
	$pageStatus='ended';
	$startTime='';
	$endTime='';
	$now='';
	//$showGraph=true;
	$teleVoteAllowed=1;
	$anonymousVoteAllowed=true;
	$outsideAustralia=true;
	$now=date("Y-m-d H:i:s");
	$initDate= date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
	if(odbc_fetch_row($queryexe))
	{
		$resultIsNull=FALSE;
		//collect results
		$author = odbc_result($queryexe, 'author');
		$startTime = odbc_result($queryexe, 'startTime');
		$endTime = odbc_result($queryexe, 'endTime');
		$pageID = odbc_result($queryexe, 'pageID');
		$outsideAustralia = odbc_result($queryexe, 'invalidAllowed');
		//$showGraph = odbc_result($queryexe, 'showGraph');
		
		if ($startTime - $initDate==0)
			$pageStatus = 'ready';
		else if ($endTime>$now)
			$pageStatus = 'active';
		else if ($endTime< $now)
			$pageStatus = 'ended';
		$teleVoteAllowed = odbc_result($queryexe, 'teleVoteAllowed');
		$anonymousVoteAllowed = odbc_result($queryexe, 'anonymousAllowed');
	}
	else
	{
		if(!isset($_GET['purgecache']) && $wgUser->isLoggedIn())
		{
			$author=$wgUser->getName();
			$insertSQL="INSERT INTO page (title,author,startTime,endTime,createTime,duration) VALUES ('$encodedTitle','$author','$initDate','$initDate','$now',1)";
			odbc_do($connectionstring, $insertSQL);
			//execute query
			$queryexe2 = odbc_do($connectionstring, $Query);
		
			//query database
			if(odbc_fetch_row($queryexe2))
			{
				//collect results
				$author = odbc_result($queryexe2, 'author');
				$startTime = odbc_result($queryexe2, 'startTime');
				$endTime = odbc_result($queryexe2, 'endTime');
				$pageID = odbc_result($queryexe2, 'pageID');
		
		
				if ($startTime - $initDate==0)
					$surveyStatus = 'ready';
				else if ($endTime>$now)
					$surveyStatus = 'active';
				else if ($endTime< $now)
					$surveyStatus = 'ended';
				$teleVoteAllowed = odbc_result($queryexe2, 'teleVoteAllowed');
				$anonymousVoteAllowed = odbc_result($queryexe2, 'anonymousAllowed');
			}
		}
	}
	
	//determine whether the current user is the creator of the survey.
	$userName=$wgUser->getName();
	$currTimeStamp=time();
	$startTimeStamp=strtotime($startTime);
	$endTimeStamp=strtotime($endTime);
	$timeleft=$endTimeStamp-$currTimeStamp;

	//add instructions
	if($pageStatus=='ready')
	{
		if($userName==$author)
		{
			$output.="<div><a href=\"./index.php?title=$encodedTitle&action=edit\">Edit</a> this page to add your question and survey information here and <a href=\"./index.php?title=$encodedTitle&action=edit\">edit</a> the choices below. You can also add more questions and choices.</div>";
		}
		else
		{
			$output.="<div>This quiz is only visible to $userName please log in to view.</div>";
			odbc_close($connectionstring);
			return $output;
		}
	}
	
	//* Added by INTEG
	if (substr($input,0,1) == "\n")
		$input=substr($input,1,strlen($input));
	if (substr($input,strlen($input)-1,1) == "\n")
		$input=substr($input,0,strlen($input)-1);	
	// Added by INTEG *//
	
	$content=explode("\n",$input);

	$output.='<form action="./database/processQuiz.php?" method="post">';
	$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";
	if($pageStatus=='ready' || $pageStatus=='ended')
	{
		/*$resultsAtEnd='no';
		if(isset($argv["resultsatend"]))
			$resultsAtEnd=$argv["resultsatend"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"resultsatend\" VALUE=\"$resultsAtEnd\" />";
		*/
		$subtractWrong='no';
		if(isset($argv["subtractwrong"]))
			$subtractWrong=$argv["subtractwrong"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"subtractwrong\" VALUE=\"$subtractWrong\" />";
		
		$multipleVoteAllowed='no';
		if(isset($argv["multiplevoting"]))
			$multipleVoteAllowed=$argv["multiplevoting"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"multiplevoting\" VALUE=\"$multipleVoteAllowed\" />";
		
		$anonymousVoteAllowed='yes';
		if(isset($argv["anonymous"]))
			$anonymousVoteAllowed=$argv["anonymous"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"anonymous\" VALUE=\"$anonymousVoteAllowed\" />";
		
		$duration='1';
		if(isset($argv["duration"]))
			$duration=$argv["duration"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"duration\" VALUE=\"$duration\" />";
		
		$SMSreply='no';
		if(isset($argv["smsreply"]))
			$SMSreply=$argv["smsreply"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"smsreply\" VALUE=\"$SMSreply\" />";
		
		$telephoneVoting='yes';
		if(isset($argv["telephonevoting"]))
			$telephoneVoting=$argv["telephonevoting"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"telephonevoting\" VALUE=\"$telephoneVoting\" />";
		
		$webVoting='yes';
		if(isset($argv["webvoting"]))
			$webVoting=$argv["webvoting"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"webvoting\" VALUE=\"$webVoting\" />";
		
		$phone=$wgUser->getMobilePhone();
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"phone\" VALUE=\"$phone\" />";
	}
	
	if($pageStatus=='ended')
	{		
		$survey = array();
		$surveyID = array();
		$points = array();
		$answer = array();//the index of the correct answer
		//gete the surveys
		$Query = "SELECT * FROM survey WHERE pageID = $pageID ORDER BY surveyID";
		$queryexe = odbc_do($connectionstring, $Query);
		while(odbc_fetch_row($queryexe))
		{
			$survey[] = odbc_result($queryexe, 'question');
			$surveyID[] = odbc_result($queryexe, 'surveyID');
			$points[] = odbc_result($queryexe, 'points');
			$answer[] = odbc_result($queryexe, 'answer');
		}
		
		$questionIndex=0;
		$score=0;
		$totalPoints=0;
		$numQuestions=0;
		$numParticipant=0;
		$Query = "SELECT * FROM view_quiz_result WHERE pageid = $pageID";
		$queryexe = odbc_do($connectionstring, $Query);
		$numParticipant = odbc_num_rows($queryexe);
		$meanScore=0;
		$medianScore=0;
		$highestScore=0;
		$lowestScore=0;
		$scoreArray= array();
		if($userName==$author)
		if(isset($_GET['viewresult']))
		if($_GET['viewresult']=='details')
		{
			while(odbc_fetch_row($queryexe))
			{
				$s=odbc_result($queryexe, 'marks');
				$scoreArray[] = $s;
				$meanScore+=$s;
			}
			if($numParticipant>0)
			{
				$meanScore/=$numParticipant;
				$medianScore=median($scoreArray);
				$highestScore=max($scoreArray);
				$lowestScore=min($scoreArray);
			}
		}
		
		foreach ($surveyID as $s )
		{
			$questionWiki=$survey[$questionIndex];
			$questionWiki=urldecode($questionWiki);
			$parsedQuestion=$wgParser->parse($questionWiki,$wgTitle, $wgOut->parserOptions(), false ,false);
			$question=$parsedQuestion->getText();
			$explosion=explode('//',$question);
			$question=$explosion[0];
			$comment='';
			if(count($explosion)>1)
				$comment=$explosion[1];
			$questionPoint=$points[$questionIndex];
			$totalPoints+=$questionPoint;
			$questionAnswer=$answer[$questionIndex];
			$questionIndex+=1;
			$numQuestions+=1;
			
			//get the choices from the database
			$savedChoice = array();
			$vote = array();
			$Query = "SELECT * FROM surveychoice WHERE surveyID = $s ORDER BY choiceID";
			$queryexe = odbc_do($connectionstring, $Query);
			while(odbc_fetch_row($queryexe))
			{
				$savedChoice[] = odbc_result($queryexe, 'choice');
				$vote[]  = odbc_result($queryexe, 'vote');
			}
			$totalVotes=0;
			$maxVote=0;
			foreach( $vote as $v )
			{
				$totalVotes+=$v;
				if($v>$maxVote)
					$maxVote=$v;
			}
					
			$chosenAnswer=0;
			$voted=false;
			$Query='';
			if($wgUser->isLoggedIn())//if the user has logged in, check the database to see if he participated in the quiz.
			{
				$mobilePhone=$wgUser->getMobilePhone();
				if($mobilePhone!='')
					$Query = "SELECT * FROM view_quiz_result_detail WHERE surveyID = $s and voterid = '$mobilePhone'";
				else
					$Query = "SELECT * FROM view_quiz_result_detail WHERE surveyID = $s and voterid = '$userName'";
			}
			else if(isset($_COOKIE['anonyuid']))//if the user has not logged in, check the cookie.
			{
				$anonyuid=$_COOKIE['anonyuid'];
				$Query = "SELECT * FROM view_quiz_result_detail WHERE surveyID = $s and voterid = '$anonyuid'";
			}
			else//give an anonymous user id to the user.
			{
				$u = md5(uniqid(rand(), true));
				setcookie('anonyuid', $u, time()+10*365*24*3600,'/');//expire after ten years
			}
			
			if($Query!='')
			{
				$queryexe = odbc_do($connectionstring, $Query);
				while(odbc_fetch_row($queryexe))
				{
					$chosenAnswer = odbc_result($queryexe, 'chosenAnswerid');
					$voted=true;
				}
			}

			
			//add question
			if($userName==$author || $voted==false)
			{
				$output.="<p style=\"font-size:large\">$questionIndex. $question <span style=\"color:#ea6d55\">($questionPoint points)</span></p>";
			}
			else
			{
				$isCorrect='ea6d55';//red		
				if($chosenAnswer == $questionAnswer)
				{
					$output.="<img src=\"correct.gif\" />&nbsp;";
					$isCorrect='128a12';//green
					$score+=$questionPoint;
				}
				else
				{
					$output.="<img src=\"wrong.gif\" />&nbsp;";
				}
				$output.="<p style=\"font-size:large\">$questionIndex. $question <span style=\"color:#$isCorrect\">($questionPoint points)</span></p>";
			}
			
			//add choices
			$choiceIndex=0;
			$output.='<table cellspacing="3" style="font-size:large">';
			foreach ($savedChoice as $choiceWiki)
			{
				$choiceWiki=urldecode($choiceWiki);
				$choiceWiki = substr($choiceWiki,1);
				$choiceWiki=trim($choiceWiki);
				$parsedChoice=$wgParser->parse($choiceWiki,$wgTitle, $wgOut->parserOptions(), false ,false);
				$choice=$parsedChoice->getText();
				if($choice!="")
				{
					$v=$vote[$choiceIndex];	
					$ci=chr(65+$choiceIndex);
					$choiceIndex++;
					$isCorrect='ea6d55';//red
					if($questionAnswer==$choiceIndex)
						$isCorrect='128a12';//green
					$percent=0;
					if($totalVotes==0)
						$percent=0;
					else
						$percent = round($v/$totalVotes*100.0);
					$percent2=0;
					if($maxVote==0)
						$percent2=0;
					else
						$percent2 = round($v/$maxVote*100.0);
					
					
					if($wgRequest->getVal('useskin')!='mobileskin')
					{
						$barWidth=$percent*3;
						$output.="<tr><td>&nbsp;&nbsp;";
						if($isCorrect=='128a12')
							$output.='<img src="correct.gif" />';
						else
							$output.='<img src="blank15.gif" />';
						$output.="<label><span style=\"color:#$isCorrect\">($ci)</span> $choice</label></td><td>";
						$output.="<img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$barWidth\" height=\"10\" border=\"1\" align=\"top\"/> $percent% ($v)";
						$output.="</td></tr>";
					}
					else//use mobile skin
					{
						$output.="<tr><td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;<label><span style=\"color:#$isCorrect\">($ci)</span> $choice</label>";
						$output.="<br /><img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$percent%\" height=\"10\" border=\"1\" align=\"top\"/> $percent% ($v)";
						$output.="</td></tr>";
					}
					
				}
			}
			$output.='</table>';
			if($comment!='')
			{
				$output.="<table style=\"background-color:#F0F0F0;border:thin dotted #00FF00;margin-left:30px\"><tr><td><span style=\"color:#128a12;font-size:large\">$comment</span></td></tr></table>";
			}
			//$output.='</p>';
			
		}//end foreach surveyID as $s
		
		if($userName==$author)
		{
			//add hidden inputs for Continueing the survey
			$type='';
			$questionIndex=0;
			$choiceIndex=0;
			$correctAnswer=array();
			//get surveys from wiki text
			foreach ($content as $wiki)
			{		
				$original=trim($wiki);
				$wiki=trim($wiki);//get rid of the white spaces.
				$type='';
				if(strpos($wiki,'#')!==0  && strpos($wiki,'*')!==0 && strpos($wiki,'//')!==0)
				{
					$type = 'question';
				}
				else if(strpos($wiki,'#')===0)
				{
					$type = 'choice';
				}
				else if(strpos($wiki,'*')===0)
				{
					$type = 'mchoice';
				}
				else if(strpos($wiki,'//')===0)
				{
					$type = 'comment';
				}
				else
					continue;
					
				if($type=='question')
				{
					$wiki=trim($wiki);
					$choiceIndex=0;
					$point =1;
					//get the point
					$e=strlen($wiki)-1;
					if(strripos($wiki,')')===$e)
					{
						$b=strripos($wiki,'(');
						if($b!==false)
						{
							$v = substr($wiki,$b+1,$e-$b-1);
							$point = floatval($v);
							$wiki=substr($wiki,0,$e-($e-$b));
							$wiki=trim($wiki);
						}
					}
					
					if($wiki!="")
					{
						if($questionIndex>0)
							$output.="</ul>";//close the choice list of the previous question
						$wiki=urlencode($wiki);
						$output.="<INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$wiki\" />";
						$questionIndex++;
						$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."point\" VALUE=\"$point\" />";
					}
				}
				else if($type=='choice' || $type=='mchoice')
				{
					$wiki = substr($wiki,1);
					$wiki=trim($wiki);
					//get the correct answer
					$e=strlen($wiki)-3;
					$correctChoice=false;
					if(strripos($wiki,'(x)')===$e)
					{
						$correctChoice=true;
						$original=substr($original,0, strlen($original)-3);
						$original=trim($original);
						$wiki=substr($wiki,0,strlen($wiki)-3);
						$wiki=trim($wiki);
					}
					
					if($wiki!="")
					{
						$wiki=urlencode($wiki);
						$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$original\" />";
						
						$choiceIndex++;
						if($correctChoice)
						{
							$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."answer\" VALUE=\"$choiceIndex\" />";
							$correctAnswer[$questionIndex]=$choiceIndex;
						}
					}
				}
				else if($type=='comment')
				{
					$wiki=trim($wiki);
					$wiki=urlencode($wiki);
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."comment\" VALUE=\"$wiki\" />";
				}
			}
			for($i=1;$i<$questionIndex+1;$i++)
			{
				if(!isset($correctAnswer[$i]))
					$output.="<p style=\"font-size:large;color:red\"><strong>Warning: Question $i does not have an answer.</strong></p>";
			}
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Continue survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p>';
			}
			else//mobile skin
			{
				$output.='<p><input type="submit" name="Submit" value="Continue survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p>';
			}
		}
		$output.="</form>";
		if($userName==$author)
		{
			//$output.='<div align="center" id="total" style="font-size: large">Number of participants = '.$numParticipant.'</div>';
			if(isset($_GET['viewresult']))
			{
				if($_GET['viewresult']=='details')
					$output.='<div align="left" id="view details" style="font-size: small"><a href="index.php?title='.$encodedTitle.'&action=purge&viewresult=hide">Hide detailed result <img src="hidedetails.gif" /></a></div>';
				else
					$output.='<div align="left" id="view details" style="font-size: small"><a href="index.php?title='.$encodedTitle.'&action=purge&viewresult=details">View detailed result <img src="viewdetails.gif" /></a></div>';
			}
			else
				$output.='<div align="left" id="view details" style="font-size: small"><a href="index.php?title='.$encodedTitle.'&action=purge&viewresult=details">View detailed result <img src="viewdetails.gif" /></a></div>';
		}
		else if($voted)
		{
			$output.='<div align="center" id="mark" style="font-size: large">Your score = '.$score.' / '.$totalPoints.'</div>';
		}
		if($userName==$author)
		if(isset($_GET['viewresult']))
		if($_GET['viewresult']=='details')
		{
			$displayRealName=false;
			if(isset($_GET['realname']))
				if($_GET['realname']=='yes')
					$displayRealName=true;
					
			//print result summary
			$output.='<p><table CELLSPACING=3>';
			$output.='<tr><td>Total Possible Points:</td><td>'.$totalPoints.'</td><td>&nbsp;</td><td>Median Score:</td><td>'.$medianScore.'</td><td>&nbsp;</td><td>Highest Score:</td><td>'.$highestScore.'</td></tr>';
			$output.='<tr><td>Number Of Participants:</td><td>'.$numParticipant.'</td><td>&nbsp;</td><td>Mean Score:</td><td>'.$meanScore.'</td><td>&nbsp;</td><td>Lowest Score:</td><td>'.$lowestScore.'</td></tr>';
			$output.='</table></p>';
			
			$output.="<form action=\"./database/sendQuizResultSMS.php?\" method=\"post\"><table><tr><td><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#cbdced style=\"font-size: large\">";
			$output.="<INPUT TYPE=\"Hidden\" NAME=\"page\" VALUE=\"|$pageID|\" />";
			$output.="<INPUT TYPE=\"Hidden\" NAME=\"totalpoint\" VALUE=\"$totalPoints\" />";
			$output.="<INPUT TYPE=\"Hidden\" NAME=\"sender\" VALUE=\"$author\" />";
			$Query = "SELECT * FROM view_quiz_result WHERE pageid = $pageID ORDER BY marks DESC";
			$queryexe = odbc_do($connectionstring, $Query);
			$voter='';
			$output.="<tr><th>SMS</th><th>User Name</th>";
			if($displayRealName)
				$output.="<th>Real Name</th>";
			$output.="<th>Score</th>";
			for($i=1;$i<=$numQuestions;$i++)
				$output.="<th>Q$i</th>";
			$output.="</tr>";
			$markCount=array();//count the occurance of each mark
			while(odbc_fetch_row($queryexe))
			{
				$v = odbc_result($queryexe, 'voterid');
				if($v==NULL)
					$v = odbc_result($queryexe, 'phone');
				$marks =  odbc_result($queryexe, 'marks'); 
				$phone = odbc_result($queryexe, 'phone'); 
				if($displayRealName)
					$realName = odbc_result($queryexe, 'realname'); 
				if(isset($markCount["$marks"]))
					$markCount["$marks"]+=1;
				else
					$markCount["$marks"]=1;
					
				$l=strlen($v);
				if($l==32)
					$name='anonymous';
				else
					$name=$v;
				
				$output.="<tr align=\"center\">";
				
				//check if the sms has been sent
				$Query2 = "SELECT * FROM quizresultsms WHERE pageID = '|$pageID|' AND mobile = '$phone'";
				$queryexe2 = odbc_do($connectionstring, $Query2);
				if(odbc_fetch_row($queryexe2))
				{
					$output.="<td><span style=\"color:#128a12\">SMS Sent</span></td>";
				}
				else
				{
					$output.="<td><INPUT TYPE=\"checkbox\" NAME=\"sendsms[]\" VALUE=\"$name\" /></td>";
				}
				
				$output.="<td>$name</td>";
				if($displayRealName)
					$output.="<td>$realName</td>";
				$output.="<td>$marks</td>";
				
				foreach($surveyID as $survey)
				{
					$Query2 = "SELECT * FROM view_quiz_result_detail WHERE surveyID = $survey AND voterid = '$phone'";
					$queryexe2 = odbc_do($connectionstring, $Query2);
					if(odbc_fetch_row($queryexe2))
					{
						$chosenAnswer =  odbc_result($queryexe2, 'chosenAnswerid'); 
						$correctAnswer =  odbc_result($queryexe2, 'correctAnswerID'); 
						$questionPoint =  odbc_result($queryexe2, 'points'); 
						$isCorrect='ea6d55';//red	
						$p='0';	
						if($chosenAnswer == $correctAnswer)
						{
							$isCorrect='128a12';//green
							$p="+$questionPoint";
						}
						$output.="<td><span style=\"color:#$isCorrect\">$p</span></td>";
					}
					else
						$output.="<td>0</td>";
							
					
				}
			}
			$output.="</table>";
			$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Send result by SMS" /></form>';
			$text='Display real name';
			if($displayRealName)
			{
				$url='index.php?'.$_SERVER['QUERY_STRING'];
				$url = str_replace('&realname=yes', "", $url);
				$text='Hide real name';
			}
			else
			{
				$url='index.php?'.$_SERVER['QUERY_STRING'].'&realname=yes';
			}
			$output.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$url\">$text</a></p></td>";
			
			//generate histogram for the mark distribution
			ksort($markCount);//sort by the mark
			require_once("utkgraph/Quizhistogram.php");
			$url=generateQuizHistogram($markCount,$pageID,$endTime);
			$output.="<td><p>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"$url\" /></p></td></table>";
			
		}
		
		$output .='<p><script>var d=new Date(); d.setTime('.$startTimeStamp.'*1000);document.write("Start Time: "+d.toLocaleString());</script></p><p><script>var d=new Date(); d.setTime('.$endTimeStamp.'*1000);document.write("End Time: "+d.toLocaleString());</script></p>';
	}
	else if($pageStatus=='ready')
	{		
		$type='';
		$questionIndex=0;
		$choiceIndex=0;
		$correctAnswer=array();
		$output.='<table cellspacing="5" style="font-size:large">';
		//get surveys from wiki text
		foreach ($content as $wiki)
		{		
			$original=trim($wiki);//get rid of the white spaces.
			$wiki=$original;
			$type='';
			$point=1;
			
			if(strpos($wiki,'#')!==0 && strpos($wiki,'*')!==0 && strpos($wiki,'//')!==0)
			{
				$type = 'question';
			}
			else if(strpos($wiki,'#')===0)
			{
				$type = 'choice';
			}
			else if(strpos($wiki,'*')===0)
			{
				$type = 'mchoice';
			}
			else if(strpos($wiki,'//')===0)
			{
				$type = 'comment';
			}
			else
				continue;
				
			
			if($type=='question')
			{
				$wiki=trim($wiki);
				$choiceIndex=0;
				//get the point
				$e=strlen($wiki)-1;
				if(strripos($wiki,')')===$e)
				{
					$b=strripos($wiki,'(');
					if($b!==false)
					{
						$v = substr($wiki,$b+1,$e-$b-1);
						$point = floatval($v);
						$wiki=substr($wiki,0,$e-($e-$b));
						$wiki=trim($wiki);
					}
				}
				$parsedWiki=$wgParser->parse($wiki,$wgTitle, $wgOut->parserOptions(), false ,false);
				$question=$parsedWiki->getText();
				if($question!="")
				{
					if($questionIndex>0)
						$output.="</ul>";//close the choice list of the previous question
					$wiki=urlencode($wiki);
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$wiki\" />";
					$questionIndex++;
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."point\" VALUE=\"$point\" />";
					$output.="<tr><td><label id=\"q$questionIndex\">$questionIndex. $question <span style=\"color:#ea6d55\">($point points)</span></label></td></tr>";
				}
			}
			else if($type=='choice' || $type=='mchoice')
			{
				$wiki = substr($wiki,1);
				$wiki=trim($wiki);
				//get the correct answer
				$e=strlen($wiki)-3;
				$correctChoice=false;
				if(strripos($wiki,'(x)')===$e)
				{
					$correctChoice=true;
					$original=substr($original,0, strlen($original)-3);
					$original=trim($original);
					$wiki=substr($wiki,0,strlen($wiki)-3);
					$wiki=trim($wiki);
				}
				
				$parsedWiki=$wgParser->parse($wiki,$wgTitle, $wgOut->parserOptions(), false ,false);
				$choice=$parsedWiki->getText();
				if($choice!="")
				{
					$wiki=urlencode($wiki);
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$original\" />";
					
					$ci=chr(65+$choiceIndex);
					$choiceIndex++;
					if($correctChoice)
					{
						$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."answer\" VALUE=\"$choiceIndex\" />";
						$correctAnswer[$questionIndex]=$choiceIndex;
					}
						
					$output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<label id=\"q$questionIndex c$choiceIndex\"><span style=\"color:#ea6d55\">($ci)</span> $choice</label></td></tr>";
				}
			}
			else if($type=='comment')
			{
				$wiki=trim($wiki);
				$wiki=urlencode($wiki);
				$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."comment\" VALUE=\"$wiki\" />";
			}
		}
		$output.='</table>';
		for($i=1;$i<$questionIndex+1;$i++)
		{
			if(!isset($correctAnswer[$i]))
				$output.="<p style=\"font-size:large;color:red\"><strong>Warning: Question $i does not have an answer.</strong></p>";
		}
		if($userName==$author)
		{
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Start survey" /></p>';
			}
			else//mobile skin
			{
				$output.='<p><input type="submit" name="Submit" value="Start survey" /></p>';
			}
		}
		$output .='</form>';
	}
	else if($pageStatus=='active')
	{
		if(!$anonymousVoteAllowed && !$wgUser->isLoggedIn() && $teleVoteAllowed!=2)
		{
				$wgOut->addHTML('<pre>   Notice: This survey only allows registered votApedia users to vote, you have to login to enter your vote. </pre>');
				global $wgRequest;
				require_once( "SpecialUserlogin.php" );
				$form = new LoginForm( $wgRequest );
				$form->execute();
				$wgOut->setPageTitle($title);
				return;
		}
		
		$survey = array();
		$surveyID = array();
		$surveyIndex = array();
		$i=1;
		//gete the surveys
		$Query = "SELECT * FROM survey WHERE pageID = $pageID ORDER BY surveyID";
		$queryexe = odbc_do($connectionstring, $Query);
		while(odbc_fetch_row($queryexe))
		{
			$id = odbc_result($queryexe, 'surveyID');
			$q = odbc_result($queryexe, 'question');
			$survey[] = $q;
			$surveyID[] = $id;
			$surveyIndex["$id"]=$i;
			$i++;
		}
		
		$output.='<style type="text/css">div.element{width: 200px;background-color: #eee;border: 1px solid #ccc;}</style>';
		if($wgRequest->getVal('useskin')!='mobileskin')
			$output.='<table><tr><td width="100%" valign="top">';
			
		$questionIndex=0;
		$numParticipant=0;
		$Query = "SELECT * FROM view_quiz_result WHERE pageid = $pageID";
		$queryexe = odbc_do($connectionstring, $Query);
		$numParticipant = odbc_num_rows($queryexe);
		
		foreach ($surveyID as $sid )
		{
			//check whether the user has already voted
			$voted=false;
			$votedChoice=array();
			if($wgUser->isLoggedIn())//if the user has logged in, check the database to see if he voted before.
			{
				$mobilePhone=$wgUser->getMobilePhone();
				if($mobilePhone=='')
					$Query = "SELECT * FROM surveyrecord WHERE surveyID = $sid and voterID = '$userName'";
				else
					$Query = "SELECT * FROM surveyrecord WHERE surveyID = $sid and voterID = '$mobilePhone'";
				$queryexe = odbc_do($connectionstring, $Query);
				while(odbc_fetch_row($queryexe))
				{
					$votedChoice[]=odbc_result($queryexe, 'choiceID');
					$voted=true;
				}
			}
			else if(isset($_COOKIE['anonyuid']))//if the user has not logged in, check the cookie.
			{
				$anonyuid=$_COOKIE['anonyuid'];
				$Query = "SELECT * FROM surveyrecord WHERE surveyID = $sid and voterID = '$anonyuid'";
				$queryexe = odbc_do($connectionstring, $Query);
				while(odbc_fetch_row($queryexe))
				{
					$votedChoice[]=odbc_result($queryexe, 'choiceID');
					$voted=true;
					
				}
			}
			else//give an anonymous user id to the user.
			{
				$u = md5(uniqid(rand(), true));
				setcookie('anonyuid', $u, time()+10*365*24*3600,'/');//expire after ten years
			}
		
			$questionWiki=$survey[$questionIndex];
			$questionWiki=urldecode($questionWiki);
			$parsedQuestion=$wgParser->parse($questionWiki,$wgTitle, $wgOut->parserOptions(), false ,false);
			$question=$parsedQuestion->getText();
			$explosion=explode('//',$question);
			$question=$explosion[0];
			$questionIndex++;
			$receiver = array();
			$savedChoice = array();
			$vote = array();
			//get the choices
			$Query = "SELECT * FROM surveychoice WHERE surveyID = $sid ORDER BY choiceID";
			$queryexe = odbc_do($connectionstring, $Query);
			while(odbc_fetch_row($queryexe))
			{
				$receiver[] = odbc_result($queryexe, 'receiver');
				$savedChoice[] = odbc_result($queryexe, 'choice');
				$vote[]  = odbc_result($queryexe, 'vote');
			}
			$totalVotes=0;
			$maxVote=0;
			foreach( $vote as $v )
			{
				$totalVotes+=$v;
				if($v>$maxVote)
					$maxVote=$v;
			}
			if($voted)	
				$output.="<p style=\"font-size:large\"><INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$questionIndex\" /><span style=\"color:#128a12\">$questionIndex</span>. $question</p>";
			else
				$output.="<p style=\"font-size:large\"><INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$questionIndex\" />$questionIndex. $question</p>";
		
			$choiceIndex=0;//choice index
			$output.='<table cellspacing="5" style="font-size:large">';
			foreach( $savedChoice as $c)
			{
				$c=urldecode($c);
				$type = 'choice';
				if(strpos($c,'#')===0)
				{
					$type = 'choice';
					$c = substr($c,1);
					$c=trim($c);
				}
				else if(strpos($c,'*')===0)
				{
					$type = 'mchoice';
					$c = substr($c,1);
					$c=trim($c);
				}

				$parsedChoice=$wgParser->parse($c,$wgTitle, $wgOut->parserOptions(), false, false );
				$c=$parsedChoice->getText();
				$r=$receiver[$choiceIndex];
				
				$len=strlen($r);
				$r1=substr($r,0,$len-2);//the first few digits
				$r2=substr($r,$len-2,2);//the last two digits
				$v=$vote[$choiceIndex];
				$ci=chr(65+$choiceIndex);
				$choiceIndex++;
				$percent=0;
				if($totalVotes==0)
					$percent=0;
				else
					$percent = round($v/$totalVotes*100.0);
				$barWidth=$percent*3;
				$percent2=0;
				if($maxVote==0)
					$percent2=0;
				else
					$percent2 = round($v/$maxVote*65.0);
				if($wgRequest->getVal('useskin')!='mobileskin')
				{
					if($userName==$author || $teleVoteAllowed==2)
					{
						$output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#ea6d55\">($ci)</span> $c</td>";
						if($teleVoteAllowed)
						{
							$l=strlen($r1);
							$areaCode="02";
							if($outsideAustralia)
								$areaCode="+61 2";
							if($l==2)//old PBX only returns 4 digits telephone number, add 6216 in the front.
								$output.="<td><img src=\"telephone.gif\" />$areaCode 6216$r1<span style=\"color:#FF0000\">$r2 </span>";
							else if($l==6)//new PBX returns 8 digits telephone number.
								$output.="<td><img src=\"telephone.gif\" />$areaCode $r1<span style=\"color:#FF0000\">$r2 </span>";
						}	
						$output.="</td></tr>";
					}
					else
					{
						$checked='';
						if($voted)
						{
							foreach($votedChoice as $ch)
								if($ch==$choiceIndex)
									$checked='checked="checked"';
						}
						$inputType='radio';
						if($type=='mchoice')
							$inputType='checkbox';
						
						$output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE=\"$inputType\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$sid+"."$choiceIndex\" $checked> $c</td></tr>";
					}
				}
				else//use mobile skin
				{
					if($userName==$author || $teleVoteAllowed==2)
					{
						$output.="<tr><td>&nbsp;&nbsp;<span style=\"color:#ea6d55\">($ci)</span> $c";
						if($teleVoteAllowed)
						{
							$l=strlen($r1);
							$areaCode="02";
							if($outsideAustralia)
								$areaCode="+61 2";
							if($l==2)//old PBX only returns 4 digits telephone number, add 6216 in the front.
								$output.="<br/>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"telephone.gif\" />$areaCode 6216$r1<span style=\"color:#FF0000\">$r2 </span>";
							else if($l==6)//new PBX returns 8 digits telephone number.
								$output.="<br/>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"telephone.gif\" />$areaCode $r1<span style=\"color:#FF0000\">$r2 </span>";
						}
						$output.="</td></tr>";
					}
					else
					{			
						$checked='';
						if($voted)
						{
							foreach($votedChoice as $ch)
								if($ch==$choiceIndex)
									$checked='checked="checked"';
						}
						$inputType='radio';
						if($type=='mchoice')
							$inputType='checkbox';
							
						$output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE=\"$inputType\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$sid+"."$choiceIndex\" $checked> $c</td></tr>";
					}
				}
			}
			$output.='</table>';
		}//end foreach($surveyID as $s)
		if($wgRequest->getVal('useskin')!='mobileskin')
			$output.='</td><td width=200px valign="top" align="right"><div align="center" style="font-size:120%;padding:.1em;margin: 0;color:#999">Answers Received</div><div id="caller" align="right" cellpadding="2">';
		else
			$output.='<div align="center" style="font-size:120%;padding:.1em;margin: 0;color:#999">Answers Received</div><div id="caller" align="center" cellpadding="2">';
		//display received calls
		$Query = "SELECT * FROM view_recent_call3 WHERE pageID = $pageID ORDER BY voteDate DESC";
		$queryexe = odbc_do($connectionstring, $Query);
		$numCallers=0;
		$lastCallDate='';
		$callerRecorded=array();
		while(odbc_fetch_row($queryexe))
		{
			$caller = odbc_result($queryexe, 'voterID');
			if(!isset($callerRecorded["$caller"]))
			{
				$id = odbc_result($queryexe, 'surveyid');
				$voteDate = odbc_result($queryexe, 'voteDate');
				if($lastCallDate=='') $lastCallDate = $voteDate;
				$realname = odbc_result($queryexe, 'user_real_name');
				$questionIndex = $surveyIndex["$id"];
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
				$numCallers++;
				//get the answer record for this caller
				$Query2 = "SELECT * FROM view_recent_call2 WHERE pageID = $pageID AND voterID='$caller' AND voteDate<> '$voteDate' ORDER BY voteDate DESC";
				$queryexe2 = odbc_do($connectionstring, $Query2);
				while(odbc_fetch_row($queryexe2))
				{
					$question = odbc_result($queryexe2, 'surveyid');
					$questionIndex = $surveyIndex["$id"];
					$answers.=" Q$questionIndex";
				}
				$output.='<div id="'.$displayCaller.'" align="center" class="element"><strong>'.$displayCaller.'  <span style="color:#128a12">'.$answers.'</span></strong></div>';
			}
		}
		$output.='</div>';
		if($wgRequest->getVal('useskin')!='mobileskin')
			$output.='</td></tr></table>';
			
		if($userName!=$author && $teleVoteAllowed!=2)
		{
			if($wgUser->isLoggedIn())
				$output.="<INPUT TYPE=\"Hidden\" NAME=\"username\" VALUE=\"$userName\" />";
			$output .= '<p style="margin:10px 0px 0px 20px"><input type="submit" name="Submit" value="Submit" /></p>';
		}
		
		//$output.='<img src="./database/spacer.gif" /><img src="./database/spacer2.gif" />';
		
		//add sms voting label
		if( $teleVoteAllowed && $userName==$author)
		{
			$output.='<div style="font-size: large">';
			if($wgRequest->getVal('useskin')!='mobileskin')
				$output.='<img src="sms.gif" />';
			else
				$output.='<img src="smss.gif" />';
			
			$countryCode="";
			if($outsideAustralia)
				$countryCode=" +61";
			$output.=' To answer; ring a number above, use a web browser to visit this page or SMS the <span style="color:#FF0000">red</span> digits corresponding to your choice to'.$countryCode.' 416906973.</div>';
		}
		//add total number of voters
		$output.='<div align="center" id="total" style="font-size: large">&nbsp;</div>';
		//add count down timer
		$output.='<div align="center" id="countDownTime" style="font-size: large">&nbsp;</div>';
		
		
		if($userName==$author)
		{
			$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Finish survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p>';
		}
		$output.='</form>';
		
		//javascript that refreshes the graph
		$output.='<SCRIPT>var duration='.$timeleft.';var numCallers='.$numCallers.';var displayNumCallers=30;var lastCallDate="'.$lastCallDate.'";var fader=new Array();var refreshCount=49;var timeRemaining=0;var timer;var requestURL = "/database/updateQuiz.php?pageID='.$pageID.'";var countDownDate=new Date();var days=0;var daystr="";var hours=0;var hourstr="";var mins=0;var minstr="";var secs=0;var secstr="";var currDate=new Date();var currTime=0;var elapsedTime=0;var startTime=currDate.getTime();var nameOfCookie="'.$sid.'";var durationCookie = getCookie(nameOfCookie+"duration");if(durationCookie==null || durationCookie!=duration){setCookie(nameOfCookie+"duration", duration, 1);}var startCookie = getCookie(nameOfCookie);if(startCookie==null || durationCookie!=duration){setCookie(nameOfCookie, startTime, 1);}else{duration-=(startTime-startCookie)/1000;}function getCookie(NameOfCookie){if (document.cookie.length > 0){begin = document.cookie.indexOf(NameOfCookie+"="); if (begin!= -1){begin += NameOfCookie.length+1;end = document.cookie.indexOf(";", begin);if (end == -1) end = document.cookie.length;return unescape(document.cookie.substring(begin, end));} }return null; }function setCookie(NameOfCookie, value, expiredays){var ExpireDate = new Date ();ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));document.cookie = NameOfCookie + "=" + escape(value) + ((expiredays == null)? "":"; expires="+ ExpireDate.toGMTString());}var xmlhttp;var tryLoadURLAgain=false;function loadXMLDoc(url){if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send(null);}else if (window.ActiveXObject){xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");if (xmlhttp){xmlhttp.onreadystatechange=xmlhttpChange;			xmlhttp.open("GET",url,true);xmlhttp.send();}}}function xmlhttpChange(){if (xmlhttp.readyState==4){if (xmlhttp.status==200){var response=xmlhttp.responseText;var temp = new Array();temp = response.split("<br/>");var pair=new Array();for(var i=0;i<temp.length;i++){ pair=temp[i].split("=");if(pair[0]!=""){ if(pair[0]=="total"){document.getElementById(pair[0]).innerHTML="Number of participants = "+pair[1];}else if(pair[0]!="nothing"){var caller=document.getElementById("caller");var divOfCaller=document.getElementById(pair[1]);if(divOfCaller)caller.removeChild(divOfCaller); else numCallers++;var c=caller.innerHTML;caller.innerHTML="<div id=\""+pair[1]+"\" align=\"center\" class=\"element\"><strong>"+pair[1]+" <span style=\"color:#128a12\">"+pair[0]+"</span></strong></div>"+c;lastCallDate=pair[2];fader.push(pair[1]);document.getElementById(pair[1]).style.opacity = 0;document.getElementById(pair[1]).style.filter = "alpha(opacity=0)";if(numCallers>displayNumCallers){caller.removeChild(caller.childNodes[displayNumCallers+1]);numCallers--;}}}}tryLoadURLAgain=false;refreshCount=0;';
		$output.='}else{tryLoadURLAgain=true;}}}function update(){refreshCount+=1;timer=setTimeout("update()",100);if(fader.length>0){var ieop = parseFloat(document.getElementById( fader[0] ).style.opacity);ieop += 0.05;var op = ieop * 100;document.getElementById( fader[0] ).style.opacity = ieop;document.getElementById( fader[0] ).style.filter = "alpha(opacity="+op+")";if(document.getElementById( fader[0] ).style.opacity>=1.0){document.getElementById( fader[0] ).style.opacity=1.0;document.getElementById( fader[0] ).style.filter = "alpha(opacity=100)";fader.shift();}}if(refreshCount %10==0){currDate=new Date();currTime=currDate.getTime();elapsedTime= currTime-startTime;timeRemaining=duration-elapsedTime/1000;if(timeRemaining<0){clearTimeout(timer);window.location.reload();return;}countDownDate.setTime(timeRemaining*1000);	days=countDownDate.getUTCDate()-1;hours=countDownDate.getUTCHours();mins=countDownDate.getUTCMinutes();secs=countDownDate.getUTCSeconds();if(days>0) daystr=days+" Days "; else daystr=""; if(hours<10) hourstr="0"+hours; else hourstr=hours;if(mins<10) minstr="0"+mins; else minstr=mins;if(secs<10) secstr="0"+secs; else secstr=secs;document.getElementById("countDownTime").innerHTML="Time Remaining - "+daystr+hourstr+":"+minstr+":"+secstr+".";}if(refreshCount==50 || tryLoadURLAgain==true){tmp = new Date();tmp = "&calldate="+lastCallDate+"&time="+tmp.getTime();tryLoadURLAgain=false;loadXMLDoc(requestURL+tmp);}if(refreshCount==70){refreshCount=0;}}timer=setTimeout("update()",100);</SCRIPT>';
	}
	
	//disconnect from database
	odbc_close($connectionstring);
	
    return $output;
}
?>