<?php
old_stuff();
# Questionnaire Tag WikiMedia extension
# with WikiMedia's extension mechanism it is possible to define
# a Questionnaire Tag of this form
# <Questionnaire>
#   Question 1
#   #great
#   #good
#   #bad
#   Question 2
#   #answer
#   #another answer
# </Questionnaire>
# the function registered by the extension gets the text between the
# tags as input and can transform it into a voting page (HTML code).
# Note: iText but directly
#       included in the HTML output. The output is not interpreted as Wik markup is not supported.
# To activate the extension, include it from your LocalSettings.php
# with: include("extensions/YourExtensionName.php");

require_once( 'filerepo/Image.php' );
require_once("./SurveySettings.php");

$wgExtensionFunctions[] = "wfQuestionnaireTagExtension";

function wfQuestionnaireTagExtension() {
    global $wgParser;
    # register the extension with the WikiText parser
    # the first parameter is the name of the new tag.
    # In this case it defines the tag <Questionnaire> ... </Questionnaire>
    # the second parameter is the callback function for
    # processing the text between the tags
    $wgParser->setHook( "Questionnaire", "renderQuestionnaire" );
	$wgParser->disableCache();
}

/*function getline( $fp, $delim )
{
   $result = "";
   while( !feof( $fp ) )
   {
       $tmp = fgetc( $fp );
       if( $tmp == $delim )
           return $result;
       $result .= $tmp;
   }
   return $result;
}*/

# The callback function for converting the input text to HTML output
function renderQuestionnaire( $input, $argv ) {
    # $argv is an array containing any arguments passed to the
    # extension like <Questionnaire argument="foo" bar>..
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
	$detectSecondQuestionnaireTag=md5($title).time();
	$detectSecondQuestionnaireTag2=md5($title).(time()-1);
	if(isset($_SESSION[$detectSecondQuestionnaireTag]) || isset($_SESSION[$detectSecondQuestionnaireTag2]))
	{
		return "<strong>Warning:</strong> A survey page can only have one &lt;Questionnaire&gt; tag. Please <a href=\"index.php?title=$encodedTitle&action=edit\">edit</a> this page and put all your choices in ONE &lt;Questionnaire&gt; tag. If you need more than one set of choices, create multiple &lt;choice&gt; tags inside the &lt;Questionnaire&gt; tag.<br />";
		exit;
	}
	$action=$wgRequest->getVal( "action" );
	//if($action != 'submit' && $_GET['purgecache']!='true')//the session variable should not be set when submitting the page.
	if($action != 'submit')
		if(isset($_GET['purgecache']))
			if($_GET['purgecache']!='true')
				$_SESSION[$detectSecondQuestionnaireTag]='1';
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
	$showGraph=true;
	$teleVoteAllowed=1;
	$anonymousVoteAllowed=true;
	$now=date("Y-m-d H:i:s");
	$initDate= date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
	$outsideAustralia=true;
	if(odbc_fetch_row($queryexe))
	{
		$resultIsNull=FALSE;
		//collect results
		$author = odbc_result($queryexe, 'author');
		$startTime = odbc_result($queryexe, 'startTime');
		$endTime = odbc_result($queryexe, 'endTime');
		$pageID = odbc_result($queryexe, 'pageID');
		$showGraph = odbc_result($queryexe, 'showGraph');
		$outsideAustralia = odbc_result($queryexe, 'invalidAllowed');

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
			$output.="<div>This survey is created by $author and still under construction, you can <a href=\"index.php?title=$encodedTitle&action=edit\">edit</a> this page if you have permission form $author.</div>";
		}
	}
	
	//* Added by INTEG
	if (substr($input,0,1) == "\n")
		$input=substr($input,1,strlen($input));
	if (substr($input,strlen($input)-1,1) == "\n")
		$input=substr($input,0,strlen($input)-1);	
	// Added by INTEG *//
	
	$content=explode("\n",$input);

	//$output .= '<img src="./database/spacer.gif" />';//put an 250*1 spacer image above the choices so that the text doesn't get squashed by the graph when browser is less than full screen.
	$output.='<form action="./database/processQuestionnaire.php?" method="post"><table cellspacing="5" style="font-size:large">';
	$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";
	if($pageStatus=='ready' || $pageStatus=='ended')
	{
		$resultsAtEnd='no';
		if(isset($argv["resultsatend"]))
			$resultsAtEnd=$argv["resultsatend"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"resultsatend\" VALUE=\"$resultsAtEnd\" />";

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
		//gete the surveys
		$Query = "SELECT * FROM survey WHERE pageID = $pageID ORDER BY surveyID";
		$queryexe = odbc_do($connectionstring, $Query);
		while(odbc_fetch_row($queryexe))
		{
			$survey[] = odbc_result($queryexe, 'question');
			$surveyID[] = odbc_result($queryexe, 'surveyID');
		}

		$questionIndex=0;
		$numParticipant=0;
		foreach ($surveyID as $s )
		{
			$questionWiki=$survey[$questionIndex];
			$questionWiki=urldecode($questionWiki);
			$parsedQuestion=$wgParser->parse($questionWiki,$wgTitle, $wgOut->parserOptions(), false ,false);
			$question=$parsedQuestion->getText();
			$questionIndex+=1;
			$receiver = array();
			$savedChoice = array();
			$vote = array();
			
			//get the choices
			$Query = "SELECT * FROM surveychoice WHERE surveyID = $s ORDER BY choiceID";
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
			if($totalVotes>$numParticipant)
				$numParticipant=$totalVotes;
			$output.="<tr><td colspan=2>$questionIndex. $question</td></tr>";

			//add choices
			$choiceIndex=0;
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
						$output.="<tr><td><ul><li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$choiceIndex.jpg)\"><label>$ci. $choice</label></li></ul></td><td><img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$barWidth\" height=\"10\" border=\"1\" align=\"top\"/> $percent% ($v)</td></tr>";
					}
					else//use mobile skin
					{
						$output.="<tr><td colspan=2><ul><li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$choiceIndex.jpg)\"><label>$ci. $choice</label><br /><img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$percent%\" height=\"10\" border=\"1\" align=\"top\"/> $percent% ($v)</li></ul></td></tr>";
					}
				}
			}

		}//end foreach surveyID as $s


		//add hidden inputs for continueing the survey
		if($userName==$author)
		{
			$type='';
			$questionIndex=0;
			$choiceIndex=0;
			//get surveys from wiki text
			foreach ($content as $wiki)
			{
				$original=trim($wiki);
				$wiki=trim($wiki);//get rid of the white spaces.
				$type='';
				if(strpos($wiki,'#')!==0  && strpos($wiki,'*')!==0)
				{
					$type = 'question';
					//$wiki = substr($wiki,1);
					$wiki=trim($wiki);
					$choiceIndex=0;
				}
				else if(strpos($wiki,'#')===0)
				{
					$type = 'choice';
					$wiki = substr($wiki,1);
					$wiki=trim($wiki);
				}
				else if(strpos($wiki,'*')===0)
				{
					$type = 'mchoice';
					$wiki = substr($wiki,1);
					$wiki=trim($wiki);
				}
				else
					continue;
	
				$parsedWiki=$wgParser->parse($wiki,$wgTitle, $wgOut->parserOptions(), false ,false);
				if($type=='question')
				{
					$question=$parsedWiki->getText();
					if($question!="")
					{
						$wiki=urlencode($wiki);
						$output.="<INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$original\" />";
						$questionIndex++;
					}
				}
				else if($type=='choice' || $type=='mchoice')
				{
					$choice=$parsedWiki->getText();
					if($choice!="")
					{
						$wiki=urlencode($wiki);
						$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$original\" />";
					}
				}
			}
			
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$output.='<tr><td colspan=2><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Continue survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p></td></tr>';
			}
			else//mobile skin
			{
				$output.='<tr><td colspan=2><p><input type="submit" name="Submit" value="Continue survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p></td></tr>';
			}
		}
		$output.='</table>';
		$output.="</form>";
		$output.='<div align="center" id="total" style="font-size: large">Number of voters = '.$numParticipant.'</div>';
		$output .='<p><script>var d=new Date(); d.setTime('.$startTimeStamp.'*1000);document.write("Start Time: "+d.toLocaleString());</script></p><p><script>var d=new Date(); d.setTime('.$endTimeStamp.'*1000);document.write("End Time: "+d.toLocaleString());</script></p>';
	}
	else if($pageStatus=='ready')
	{
		$type='';
		$questionIndex=0;
		$choiceIndex=0;
		//get surveys from wiki text
		foreach ($content as $wiki)
		{
			$original=trim($wiki);//get rid of the white spaces.
			$wiki=$original;
			$type='';
			if(strpos($wiki,'#')!==0 && strpos($wiki,'*')!==0)
			{
				$type = 'question';
				//$wiki = substr($wiki,1);
				$wiki=trim($wiki);
				$choiceIndex=0;
			}
			else if(strpos($wiki,'#')===0)
			{
				$type = 'choice';
				$wiki = substr($wiki,1);
				$wiki=trim($wiki);
			}
			else if(strpos($wiki,'*')===0)
			{
				$type = 'mchoice';
				$wiki = substr($wiki,1);
				$wiki=trim($wiki);
			}
			else
				continue;

			//$parsedWiki=$wgParser->parse($wiki,$wgTitle, $wgOut->parserOptions(), false ,false);
			$parsedWiki=$wgParser->parse($wiki,$wgTitle, $wgOut->ParserOptions(), false ,false);
			if($type=='question')
			{
				$question=$parsedWiki->getText();
				if($question!="")
				{
					if($questionIndex>0)
						$output.="</ul>";//close the choice list of the previous question
					$wiki=urlencode($wiki);
					$output.="<tr><td colspan=2><INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$original\" />";
					$questionIndex++;

					$output.="<label id=\"q$questionIndex\">$questionIndex. $question</label></td></tr>";
				}
			}
			else if($type=='choice' || $type=='mchoice')
			{
				$choice=$parsedWiki->getText();
				if($choice!="")
				{
					$wiki=urlencode($wiki);
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$original\" />";
					$ci=chr(65+$choiceIndex);
					$choiceIndex++;
					$percent=10;
					if($wgRequest->getVal('useskin')!='mobileskin')
					{
						$barWidth=$percent*3;
						$output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<label id=\"q$questionIndex c$choiceIndex\">$ci. $choice</label></td><td><img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$barWidth\" height=\"10\" border=\"1\" align=\"top\"/> $percent%</td></tr>";
					}
					else //mobile skin uses horizontal bars
					{
						$output.="<tr><td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;<label id=\"q$questionIndex c$choiceIndex\">$ci. $choice</label><br /><img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$percent%\" height=\"10\" border=\"1\" align=\"top\"/> $percent%</td></tr>";
					}
				}
			}
		}
		if($userName==$author)
		{
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$output.='<tr><td><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Start survey" /></p></td><td></td></tr>';
			}
			else//mobile skin
			{
				$output.='<tr><td colspan=2><p><input type="submit" name="Submit" value="Start survey" /></p></td></tr>';
			}
		}
		$output .='</table></form>';
	}
	else if($pageStatus=='active')
	{
		if(!$anonymousVoteAllowed && !$wgUser->isLoggedIn())
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
		//gete the surveys
		$Query = "SELECT * FROM survey WHERE pageID = $pageID ORDER BY surveyID";
		$queryexe = odbc_do($connectionstring, $Query);
		while(odbc_fetch_row($queryexe))
		{
			$survey[] = odbc_result($queryexe, 'question');
			$surveyID[] = odbc_result($queryexe, 'surveyID');
		}

		$questionIndex=0;
		foreach ($surveyID as $sid )
		{
			//check whether the user has already voted
			$voted=false;
			$votedChoice=array();
			if($wgUser->isLoggedIn())//if the user has logged in, check the database to see if he voted before.
			{
				$mobilePhone=$wgUser->getMobilePhone();
				if($mobilePhone!='')
					$Query = "SELECT * FROM surveyrecord WHERE surveyID = $sid and voterID = '$mobilePhone'";
				else
					$Query = "SELECT * FROM surveyrecord WHERE surveyID = $sid and voterID = '$userName'";
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
			$output.="<tr><td colspan=3><INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$questionIndex\" />$questionIndex. $question</td></tr>";

			$choiceIndex=0;//choice index
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
					if($userName==$author)
					{
						$output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;$ci. $c</td>";
						if($teleVoteAllowed==1 || $teleVoteAllowed==2)
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
						if($showGraph)
						{
							$output.="</td><td><img name=\"q$questionIndex"."c$choiceIndex\" src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$barWidth\" height=\"10\" border=\"1\" align=\"top\"/><span id=\"q$questionIndex"."c$choiceIndex"."p\">&nbsp;$percent%&nbsp;($v)</span></td></tr>";
						}
						else
						{
							$output.="</td><td></td></tr>";
						}
					}
					else 
					{
						if( $teleVoteAllowed==2 )
						{
							$output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;$ci. $c</td>";
							if($showGraph)
							{
								$output.="<td><img name=\"q$questionIndex"."c$choiceIndex\" src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$barWidth\" height=\"10\" border=\"1\" align=\"top\"/><span id=\"q$questionIndex"."c$choiceIndex"."p\">&nbsp;$percent%&nbsp;($v)</span></td><td></td></tr>";
							}
							else
							{
								$output.="<td></td><td></td></tr>";
							}
						}
						else
						{
							$output.="<tr><td>";
	
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
	
							$output.="&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE=\"$inputType\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$sid+"."$choiceIndex\" $checked> $c</td>";
	
							if($showGraph)
								$output.="<td colspan=2><img name=\"q$questionIndex"."c$choiceIndex\" src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$barWidth\" height=\"10\" border=\"1\" align=\"top\"/><span id=\"q$questionIndex"."c$choiceIndex"."p\">&nbsp;$percent%&nbsp;($v)</span></td></tr>";
							else
								$output.="<td colspan=2></td></tr>";
						}
					}	
				}
				else//use mobile skin
				{
					if($userName==$author)
					{
						$output.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;$ci. $c";
						if($teleVoteAllowed==1 || $teleVoteAllowed==2)
						{
							$l=strlen($r1);
							$areaCode="02";
							if($outsideAustralia)
								$areaCode="+61 2";
							if($l==2)//old PBX only returns 4 digits telephone number, add 6216 in the front.
								$output.="<div align=right><img src=\"telephone.gif\" />$areaCode 6216$r1<span style=\"color:#FF0000\">$r2 </span></div>";
							else if($l==6)//new PBX returns 8 digits telephone number.
								$output.="<div align=right><img src=\"telephone.gif\" />$areaCode $r1<span style=\"color:#FF0000\">$r2 </span></div>";
						}
						$output.="<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$percent2%\" height=\"10\" border=\"1\" align=\"top\"/> $percent2%&nbsp;($v)</p></td></tr>";
					}
					else
					{
						$output.="<tr><td colspan=3>";

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

						$output.="&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE=\"$inputType\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$sid+"."$choiceIndex\" $checked> $c</td>";

						if($showGraph)
							$output.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"./utkgraph/ChoiceColor/Choice$choiceIndex.jpg\" width=\"$percent2%\" height=\"10\" border=\"1\" align=\"top\"/> $percent%&nbsp;($v)</td></tr>";
						else
							$output.="</td></tr>";
					}
				}
			}
		}//end foreach($surveyID as $s)

		if($userName!=$author)
		{
			if($wgUser->isLoggedIn())
				$output.="<INPUT TYPE=\"Hidden\" NAME=\"username\" VALUE=\"$userName\" />";
			if($teleVoteAllowed!=2)//telephone only survey
				$output .= '<tr><td colspan=3><p style="margin:10px 0px 0px 20px"><input type="submit" name="Submit" value="Enter My Vote" /></p></td></tr>';
		}

		if($wgRequest->getVal('useskin')!='mobileskin')
		{
			$output.='<tr><td><img src="./database/spacer.gif" /></td><td><img src="./database/spacer2.gif" /></td><td></td></tr></table>';
		}
		else
		{
			$output.='</table>';
		}

		//add sms voting label
		if( $teleVoteAllowed!=0 && $userName==$author)
		{
			$output.='<div style="font-size: large">';
			if($wgRequest->getVal('useskin')!='mobileskin')
				$output.='<img src="sms.gif" />';
			else
				$output.='<img src="smss.gif" />';
			$output.=' To vote; ring a number above';
			if($teleVoteAllowed!=2)
				$output.=', use a web browser to visit this page';
			$countryCode="";
			if($outsideAustralia)
				$countryCode=" +61";
			$output.='or SMS the <span style="color:#FF0000">red</span> digits corresponding to your choice to'.$countryCode.' 416906973.</div>';
		}
		//add total number of votes
		$output.='<div align="center" id="total" style="font-size: large">&nbsp;</div>';
		//add count down timer
		$output.='<div align="center" id="countDownTime" style="font-size: large">&nbsp;</div>';


		if($userName==$author)
		{
			$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Finish survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p>';
		}
		$output.='</form>';

		//javascript that refreshes the graph
		$output.='<SCRIPT>var duration='.$timeleft.';var refreshCount=4;var timeRemaining=0;var timer;var requestURL = "/database/updateQuestionnaire.php?pageTitle='.$encodedTitle.'";var countDownDate=new Date();var days=0;var daystr="";var hours=0;var hourstr="";var mins=0;var minstr="";var secs=0;var secstr="";var currDate=new Date();var currTime=0;var elapsedTime=0;var startTime=currDate.getTime();var nameOfCookie="'.$sid.'";var durationCookie = getCookie(nameOfCookie+"duration");if(durationCookie==null || durationCookie!=duration){setCookie(nameOfCookie+"duration", duration, 1);}var startCookie = getCookie(nameOfCookie);if(startCookie==null || durationCookie!=duration){setCookie(nameOfCookie, startTime, 1);}else{duration-=(startTime-startCookie)/1000;}function getCookie(NameOfCookie){if (document.cookie.length > 0){begin = document.cookie.indexOf(NameOfCookie+"="); if (begin!= -1){begin += NameOfCookie.length+1;end = document.cookie.indexOf(";", begin);if (end == -1) end = document.cookie.length;return unescape(document.cookie.substring(begin, end));} }return null; }function setCookie(NameOfCookie, value, expiredays){var ExpireDate = new Date ();ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));document.cookie = NameOfCookie + "=" + escape(value) + ((expiredays == null)? "":"; expires="+ ExpireDate.toGMTString());}var xmlhttp;var tryLoadURLAgain=false;function loadXMLDoc(url){if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send(null);}else if (window.ActiveXObject){xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");if (xmlhttp){xmlhttp.onreadystatechange=xmlhttpChange;			xmlhttp.open("GET",url,true);xmlhttp.send();}}}function xmlhttpChange(){if (xmlhttp.readyState==4){if (xmlhttp.status==200){var response=xmlhttp.responseText;var temp = new Array();temp = response.split(";");var pair=new Array();for(var i=0;i<temp.length;i++){ pair=temp[i].split("=");var percent=pair[1]/3.0;if(pair[0]!=""){ if(pair[0]=="total"){document.getElementById(pair[0]).innerHTML="Number of voters = "+pair[1];}';
		if($showGraph)
			$output.='else{document.getElementById(pair[0]+"p").innerHTML="&nbsp;"+percent.toFixed(0)+"% ("+pair[2]+")"; document.images[pair[0]].width=pair[1];}';
		$output.='} }}else{tryLoadURLAgain=true;}}}function update(){currDate=new Date();currTime=currDate.getTime();elapsedTime= currTime-startTime;timeRemaining=duration-elapsedTime/1000;timer=setTimeout("update()",1000);if(timeRemaining<0){clearTimeout(timer);window.location.reload();return;}refreshCount+=1;countDownDate.setTime(timeRemaining*1000);	days=countDownDate.getUTCDate()-1;hours=countDownDate.getUTCHours();mins=countDownDate.getUTCMinutes();secs=countDownDate.getUTCSeconds();if(days>0) daystr=days+" Days "; else daystr=""; if(hours<10) hourstr="0"+hours; else hourstr=hours;if(mins<10) minstr="0"+mins; else minstr=mins;if(secs<10) secstr="0"+secs; else secstr=secs;document.getElementById("countDownTime").innerHTML="Time Remaining - "+daystr+hourstr+":"+minstr+":"+secstr+".";if(refreshCount==5 || tryLoadURLAgain==true){tmp = new Date();tmp = "&time="+tmp.getTime();tryLoadURLAgain=false;loadXMLDoc(requestURL+tmp);}if(refreshCount==7){refreshCount=0;}}timer=setTimeout("update()",1000);</SCRIPT>';
	}

	//disconnect from database
	odbc_close($connectionstring);

    return $output;
}
?>