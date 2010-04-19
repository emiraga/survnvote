<?php
# Choice Tag WikiMedia extension
# with WikiMedia's extension mechanism it is possible to define
# a Choice Tag of this form
# <CHOICE> some text </CHOICE>
# the function registered by the extension gets the text between the
# tags as input and can transform it into a voting page (HTML code).
# Note: iText but directly
#       included in the HTML output. So WikiThe output is not interpreted as Wik markup is not supported.

$wgExtensionFunctions[] = "wfChoiceTagExtension";

require_once("$IP/extensions/votapedia/VotapediaSettings.php");

function wfChoiceTagExtension() {
    global $wgParser;
    # register the extension with the WikiText parser
    # the first parameter is the name of the new tag.
    # In this case it defines the tag <example> ... </example>
    # the second parameter is the callback function for
    # processing the text between the tags
    $wgParser->setHook( "choice", "renderChoice" );
	$wgParser->disableCache();
}

function getline( $fp, $delim )
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
}

# The callback function for converting the input text to HTML output
function renderChoice( $input, $argv ) {
    # $argv is an array containing any arguments passed to the
    # extension like <choice argument="foo" bar>..
    # Put this on the sandbox page:  (works in MediaWiki 1.5.5)
    #   <choice argument="foo" argument2="bar">Testing text **example** in between the new tags</choice>
	global $wgRequest,$wgUser,$wgParser,$wgTitle,$wgOut;
	global $gDataSourceName;
	global $gDBUserName;
	global $gDBUserPassword;
	global $vpScriptPath;
	$output='';

	$wgParser->disableCache();//disable cache because mobile and desktop skin requires different ways to render the barchart.

	//get the title of the page
	$pageTitle=$wgRequest->getVal( "title" ); //$wgTitle->getText(); doesn't work here because the special page is included in a normal wiki page.
	//get rid of the underbars in the pageTitle
	$trans = array("_" => " ");//, "hi" => "hello");
	$pageTitle=strtr($pageTitle, $trans);
	$encodedTitle=urlencode($pageTitle);

	//Give a warning message when there are more than one choice tag in the page.
	$detectSecondChoiceTag=md5($pageTitle).time();
	$detectSecondChoiceTag2=md5($pageTitle).(time()-1);
	if(isset($_SESSION[$detectSecondChoiceTag]) || isset($_SESSION[$detectSecondChoiceTag2]))
	{
		return "<strong>Warning:</strong> A survey page can only have one &lt;choice&gt; tag. Please <a href=\"index.php?title=$encodedTitle&action=edit\">edit</a> this page and put all your choices in ONE &lt;choice&gt; tag. If you need more than one set of choices, create another survey instead of using multiple &lt;choice&gt; tags.<br />";
		exit;
	}
	$action=$wgRequest->getVal( "action" );
	//if($action != 'submit' && $_GET['purgecache']!='true')//the session variable should not be set when submitting the page.
	if($action != 'submit')
		if(isset($_GET['purgecache']))
			if($_GET['purgecache']!='true')
				$_SESSION[$detectSecondChoiceTag]='1';

	$background='null';
	if(isset($argv['background']))
	{
		$imagelink=$argv['background'];
		$pos=false;
		$pos=stripos($imagelink,'[Image:');
		if($pos!=false)//the image is specified as an internal link
		{
			$pos2=stripos($imagelink,']]');
			$length=$pos2-$pos+1;
			$imagename=substr($imagelink,$pos+7,$length-8);
			$title = Title::makeTitleSafe( NS_IMAGE, $imagename );
			$img = new Image( $title );
			if($img->exists())
			{
				$siteName=$_SERVER['HTTP_HOST'];
				$background=urlencode("http://".$siteName.$img->getURL());
			}
		}
		else//the image is specified as an external link
		{
			$background=urlencode($imagelink);
		}
	}
	$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);

	//SQL query
	$Query = "SELECT * FROM page WHERE title = '$encodedTitle'";

	//execute query
	$queryexe = odbc_do($connectionstring, $Query);

	//query database
	$resultIsNull=TRUE;
	$surveyStatus='ended';
	$startTime='';
	$endTime='';
	$now='';
	$pageID='';
	$author='';
	$teleVoteAllowed=1;
	$anonymousVoteAllowed=true;
	$votesAllowed=1;
	$now=date("Y-m-d H:i:s");
	$initDate= date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
	$outsideAustralia=true;
	//echo $encodedTitle;
	if(odbc_fetch_row($queryexe))
	{
		$resultIsNull=FALSE;
		//collect results
		$author = odbc_result($queryexe, 'author');
		$startTime = odbc_result($queryexe, 'startTime');
		$endTime = odbc_result($queryexe, 'endTime');
		$pageID = odbc_result($queryexe, 'pageID');
		$votesAllowed = odbc_result($queryexe, 'votesAllowed');
		$outsideAustralia = odbc_result($queryexe, 'invalidAllowed');

		if ($startTime - $initDate==0)
			$surveyStatus = 'ready';
		else if ($endTime>$now)
			$surveyStatus = 'active';
		else if ($endTime< $now)
			$surveyStatus = 'ended';
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

	//find the surveyID
	$surveyID=0;
	if($surveyStatus=='active' || $surveyStatus=='ended')
	{
		$Query2 = "SELECT * FROM survey WHERE pageID = '$pageID'";
		$queryexe = odbc_do($connectionstring, $Query2);
		if(odbc_fetch_row($queryexe))
		{
			$surveyID = odbc_result($queryexe, 'surveyID');
		}
	}
	//determine whether the current user is the creator of the survey.
	$userName=$wgUser->getName();
	if($wgUser->isLoggedIn())
		$wgUser->setCookies();
	$currTimeStamp=time();
	$startTimeStamp=strtotime($startTime);
	$endTimeStamp=strtotime($endTime);
	$timeleft=$endTimeStamp-$currTimeStamp;

	//add instructions
	if($surveyStatus=='ready')
	{
		if($userName==$author)
		{
			$output.="<div><a href=\"/index.php?title=$encodedTitle&action=edit\">Edit</a> this page to add your question and survey information here and <a href=\"/index.php?title=$encodedTitle&action=edit\">edit</a> the choices below. You can also add more choices.</div>";
		}
		else
		{
			$output.="<div>This survey is created by $author and still under construction, you can <a href=\"index.php?title=$encodedTitle&action=edit\">edit</a> this page if you have permission form $author.</div>";
		}
	}
	$output.='<form action="/database/processSurvey.php?" method="post"><table cellspacing="0" style="font-size:large">';
	$encodedTitle=urlencode($pageTitle);
	$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";
	$content=explode("\n",$input);
	$i=0;
	$output .= '<tr><td valign="top"><img src="'.$vpScriptPath.'/images/spacer.gif" /><ul>';//put an 250*1 spacer image above the choices so that the text doesn't get squashed by the graph when browser is less than full screen.
	if($surveyStatus=='ready' || $surveyStatus=='ended')
	{
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";

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

		//$phone=$wgUser->getMobilePhone();
		$phone='';
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"phone\" VALUE=\"$phone\" />";

		$resultsAtEnd='no';
		if(isset($argv["resultsatend"]))
			$resultsAtEnd=$argv["resultsatend"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"resultsatend\" VALUE=\"$resultsAtEnd\" />";

		$displaytop='0';
		if(isset($argv["displaytop"]))
			$displaytop=$argv["displaytop"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"displaytop\" VALUE=\"$displaytop\" />";

		$votesallowed='1';
		if(isset($argv["votesallowed"]))
			$votesallowed=$argv["votesallowed"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"votesallowed\" VALUE=\"$votesallowed\" />";
	}

	if($surveyStatus=='ended')
	{
		$receiver = array();
		$savedChoice = array();
		$vote = array();
		//get the telephone number
		$Query = "SELECT * FROM surveyChoice WHERE surveyID = $surveyID";
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

		//add choices
		foreach ($content as $choiceWiki)
		{

			$parsedChoice=$wgParser->parse($choiceWiki,$wgTitle, $wgOut->mParserOptions, false ,false);
			$choice=$parsedChoice->getText();
			if($choice!=""){
				$i++;
				$colorIndex=fmod($i,50);//only 50 different colors are available
				if($wgRequest->getVal('useskin')!='mobileskin')
				{
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"choice[]\" VALUE=\"$choiceWiki\" />";
					$output.="<li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$colorIndex.jpg)\"><label id=\"q$i\">$i. $choice</label></li>";
				}
				else //use mobile skin
				{
					$v=$vote[$i-1];
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

					$output.="<INPUT TYPE=\"Hidden\" NAME=\"choice[]\" VALUE=\"$choiceWiki\" />";
					$output.="<li><label id=\"q$i\">$i. $choice <br />($v votes, $percent%)</label><p><img src=\"./utkgraph/ChoiceColor/Choice$colorIndex.jpg\" width=\"$percent2%\" height=\"10\" border=\"1\" align=\"top\"/></p></li>";
				}
			}
		}

		if($userName==$author)
		{
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$output.='</ul><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Continue survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p>';
			}
			else//mobile skin
			{
				$output.='</ul><p><input type="submit" name="Submit" value="Continue survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p>';
			}
		}
		else
		{
			$output.='</ul>';
		}

		if($wgRequest->getVal('useskin')!='mobileskin')
		{
			$encodedTitle=rawurlencode($pageTitle);
			$output.='</td><td valign="top"><div style="margin:0px 0px 0px 40px"><img src="loading.gif" alt="Graph" name="refresh"/></div></td></tr><SCRIPT>var imageSwap=true;var myImage=new Image();var image = "/utkgraph/finishForJun.php?pageTitle='.$encodedTitle.'&background='.$background.'";var xmlhttp;var tryLoadURLAgain=true;function loadXMLDoc(url){if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send(null);}else if (window.ActiveXObject){xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");if (xmlhttp){xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send();}}}function xmlhttpChange(){	if (xmlhttp.readyState==4){if (xmlhttp.status==200){var url=xmlhttp.responseText;var begin=url.indexOf("/utkgraph");myImage.src = url.substring(begin, 5000);imageSwap=false;tryLoadURLAgain=false;}else{tryLoadURLAgain=true;}}}function update(){timer=setTimeout("update()",1000);if(tryLoadURLAgain==true){tryLoadURLAgain=false;tmp = new Date();tmp = "&time="+tmp.getTime();loadXMLDoc(image+tmp);}if(imageSwap==false)if(myImage.complete){document.images["refresh"].src=myImage.src;myImage=new Image();clearTimeout(timer);return;}}update();</SCRIPT>';
		}
		else//use mobile skin
		{
			$output.='</td></tr>';
		}

	}
	else if($surveyStatus=='ready')
	{
		//get choices from wiki text
		foreach ($content as $choiceWiki)
		{
			$parsedChoice=$wgParser->parse($choiceWiki,$wgTitle, $wgOut->parserOptions(), false ,false);
			$choice=$parsedChoice->getText();

			if($choice!="")
			{
                $choiceContent=strip_tags($choice);
				$choiceWiki=urlencode($choiceWiki);
			    $output.="<INPUT TYPE=\"Hidden\" NAME=\"choice[]\" VALUE=\"$choiceWiki\" />";
				$i++;
				$colorIndex=fmod($i,50);//only 50 different colors are available
				if($wgRequest->getVal('useskin')!='mobileskin')
				{
					$output.="<li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$colorIndex.jpg)\"><label id=\"q$i\">$i. $choice</label></li>";
				}
				else //mobile skin uses horizontal bars
				{
					$percent=rand(30,100);
					$output.="<li><label id=\"q$i\">$i. $choice</label><p><img src=\"./utkgraph/ChoiceColor/Choice$colorIndex.jpg\" width=\"$percent%\" height=\"10\" border=\"1\" align=\"top\"/></p></li>";
				}
			}
		}
		if($userName==$author)
		{
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$output.='</ul><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Start survey" /></p></td><td valign="top"><div style="margin:0px 0px 0px 40px"><img src="/utkgraph/displayGraph.php?pageTitle='.$encodedTitle.'&background='.$background.'" alt="sample graph" /></div></td></tr>';
			}
			else//mobile skin
			{
				$output.='</ul><p><input type="submit" name="Submit" value="Start survey" /></p></td></tr>';
			}
		}
		else
		{
			$output.='</ul></td></tr>';
		}
	}
	else if($surveyStatus=='active')
	{
		if(!$anonymousVoteAllowed && !$wgUser->isLoggedIn())
		{
				$wgOut->addHTML('<pre>   Notice: This survey only allows registered votApedia users to vote, you have to login to enter your vote. </pre>');
				global $wgRequest;
				require_once( "SpecialUserlogin.php" );
				$form = new LoginForm( $wgRequest );
				$form->execute();
				$wgOut->setPageTitle($pageTitle);
				return;
		}

		$receiver = array();
		$savedChoice = array();
		$vote = array();
		//get the telephone number
		$Query = "SELECT * FROM surveyChoice WHERE surveyID = $surveyID";
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
		$voted=false;
		if($wgUser->isLoggedIn())
		{
			//check the database to see whether the user has already voted
			//$mobilePhone=$wgUser->getMobilePhone();
			
			if($mobilePhone!='')
				$Query = "SELECT * FROM surveyrecord WHERE surveyID = $surveyID and voterID = '$mobilePhone'";
			else
				$Query = "SELECT * FROM surveyrecord WHERE surveyID = $surveyID and voterID = '$userName'";
			$queryexe = odbc_do($connectionstring, $Query);
			if(odbc_fetch_row($queryexe))
			{
				$voted=true;
			}
		}
		else if(isset($_COOKIE['anonyuid']))//if the user has not logged in, check the cookie.
		{
			$anonyuid=$_COOKIE['anonyuid'];
			$Query = "SELECT * FROM surveyrecord WHERE surveyID = $surveyID and voterID = '$anonyuid'";
			$queryexe = odbc_do($connectionstring, $Query);
			if(odbc_fetch_row($queryexe))
			{
				$votedChoice=odbc_result($queryexe, 'choiceID');
				$voted=true;
			}
		}
		else//give an anonymous user id to the user.
		{
			$u = md5(uniqid(rand(), true));
			setcookie('anonyuid', $u, time()+10*365*24*3600,'/');//expire after ten years
		}

		foreach( $savedChoice as $c)
		{
			$c=urldecode($c);
			$parsedChoice=$wgParser->parse($c,$wgTitle, $wgOut->mParserOptions, false ,false);
			$c=$parsedChoice->getText();
			$r=$receiver[$i];
			$len=strlen($r);
			$r1=substr($r,0,$len-2);//the first few digits
			$r2=substr($r,$len-2,2);//the last two digits
			$v=$vote[$i];
			$i++;
			$colorIndex=fmod($i,50);//only 50 different colors are available
			if($wgRequest->getVal('useskin')!='mobileskin')
			{

				if($userName==$author)
				{
					$output.="<li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$colorIndex.jpg)\"><label id=\"q$i\">$i. $c<br /></label>";
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
					$output.='</li>';
				}
				else
				{
					if($voted || $teleVoteAllowed==2)
					{
						$percent=0;
						if($totalVotes==0)
							$percent=0;
						else
							$percent = round($v/$totalVotes*100.0);
						$output.="<li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$colorIndex.jpg)\"><label id=\"q$i\">$i. $c</label></li>";
					}
					else
					{
						$output.="<li>";
						$n=0;
						for($n=0;$n<$votesAllowed;$n++)
						{
							$output.="<input type=\"radio\" name=\"choice$n\" value=\"$surveyID"."+$i\" />";
						}
						$output.="<label id=\"q$i\">$c</label></li>";
					}
				}
			}
			else
			{
				if($userName==$author)
				{
					$percent=0;
					if($totalVotes==0)
						$percent=0;
					else
						$percent = round($v/$totalVotes*100.0);
					$output.="<li><label id=\"q$i\">$i. $c <br /></label>";
					if($teleVoteAllowed==1 || $teleVoteAllowed==2)
					{
						$l=strlen($r1);
						if($outsideAustralia)
							$areaCode="+61 2";
						if($l==2)//old PBX only returns 4 digits telephone number, add 6216 in the front.
							$output.="<div align=right><img src=\"telephone.gif\" />$areaCode 6216$r1<span style=\"color:#FF0000\">$r2 </span><br />($v votes)</div>";
						else if($l==6)//new PBX returns 8 digits telephone number.
							$output.="<div align=right><img src=\"telephone.gif\" />$areaCode $r1<span style=\"color:#FF0000\">$r2 </span><br />($v votes)</div>";
					}
					$output.="<br /><p><img src=\"./utkgraph/ChoiceColor/Choice$colorIndex.jpg\" width=\"$percent%\" height=\"10\" border=\"1\" align=\"top\"/></p></li>";
				}
				else
				{
					if($voted || $teleVoteAllowed==2)
					{
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
						$output.="<li><label id=\"q$i\">$i. $c ($v votes)</label><p><img src=\"./utkgraph/ChoiceColor/Choice$colorIndex.jpg\" width=\"$percent2%\" height=\"10\" border=\"1\" align=\"top\"/></p></li>";
					}
					else
					{
						$output.="<li>";
						$n=0;
						for($n=0;$n<$votesAllowed;$n++)
						{
							$output.="<input type=\"radio\" name=\"choice$n\" value=\"$surveyID"."+$i\" />";
						}
						$output.="<label id=\"q$i\">$c</label></li>";
					}
				}
			}
		}
		if($userName==$author)
		{
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$encodedTitle=rawurlencode($pageTitle);
				$output.='</ul><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Finish survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p></td><td valign="top"><div style="margin:0px 0px 0px 40px"><img src="loading.gif" alt="Graph" name="refresh"/></div><div id="countDownTime">&nbsp;</div></td></tr>';

				//javascript that refreshes the graph
				$output.='<SCRIPT>var duration='.$timeleft.';var timeRemaining=0;var timer;var imageSwap=true;var refreshCount=4;var myImage=new Image();var image = "/utkgraph/totalgraphForJundgprogress.php?pageTitle='.$encodedTitle.'&background='.$background.'";var countDownDate=new Date();var days=0;var daystr="";var hours=0;var hourstr="";var mins=0;var minstr="";var secs=0;var secstr="";var currDate=new Date();var currTime=0;var elapsedTime=0;var startTime=currDate.getTime();var nameOfCookie="'.$surveyID.'";var durationCookie = getCookie(nameOfCookie+"duration");if(durationCookie==null || durationCookie!=duration){setCookie(nameOfCookie+"duration", duration, 1);}var startCookie = getCookie(nameOfCookie);if(startCookie==null || durationCookie!=duration){setCookie(nameOfCookie, startTime, 1);}else{duration-=(startTime-startCookie)/1000;}function getCookie(NameOfCookie){if (document.cookie.length > 0){begin = document.cookie.indexOf(NameOfCookie+"="); if (begin!= -1){begin += NameOfCookie.length+1;end = document.cookie.indexOf(";", begin);if (end == -1) end = document.cookie.length;return unescape(document.cookie.substring(begin, end));} }return null; }function setCookie(NameOfCookie, value, expiredays){var ExpireDate = new Date ();ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));document.cookie = NameOfCookie + "=" + escape(value) + ((expiredays == null)? "":"; expires="+ ExpireDate.toGMTString());}var xmlhttp;var tryLoadURLAgain=false;function loadXMLDoc(url){if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send(null);}else if (window.ActiveXObject){xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");if (xmlhttp){xmlhttp.onreadystatechange=xmlhttpChange;			xmlhttp.open("GET",url,true);xmlhttp.send();}}}function xmlhttpChange(){if (xmlhttp.readyState==4){if (xmlhttp.status==200){var url=xmlhttp.responseText;var begin=url.indexOf("/utkgraph");myImage.src = url.substring(begin, 5000);imageSwap=false;}else{tryLoadURLAgain=true;}}}function update(){currDate=new Date();currTime=currDate.getTime();elapsedTime= currTime-startTime;timeRemaining=duration-elapsedTime/1000;timer=setTimeout("update()",1000);if(timeRemaining<0){clearTimeout(timer);window.location.reload();return;}refreshCount+=1;countDownDate.setTime(timeRemaining*1000);	months=countDownDate.getUTCMonth();days=countDownDate.getUTCDate()-1;hours=countDownDate.getUTCHours();mins=countDownDate.getUTCMinutes();secs=countDownDate.getUTCSeconds();if(months>0) monthstr=months+" Months "; else monthstr="";if(days>0) daystr=days+" Days "; else daystr=""; if(hours<10) hourstr="0"+hours; else hourstr=hours;if(mins<10) minstr="0"+mins; else minstr=mins;if(secs<10) secstr="0"+secs; else secstr=secs;document.getElementById("countDownTime").innerHTML="Time Remaining - "+monthstr+daystr+hourstr+":"+minstr+":"+secstr+".";if(refreshCount==5 || tryLoadURLAgain==true){tmp = new Date();tmp = "&time="+tmp.getTime();tryLoadURLAgain=false;loadXMLDoc(image+tmp);}if(imageSwap==false)if(myImage.complete){document.images["refresh"].src=myImage.src;myImage=new Image();imageSwap=true;refreshCount=0;}if(refreshCount==25){refreshCount=4;}}timer=setTimeout("update()",1000);</SCRIPT>';
			}
			else//mobile skin
			{
				$output.='</ul><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Finish survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p></td></tr>';
			}
		}
		else
		{

			if($voted || $teleVoteAllowed==2)
			{
				if($wgRequest->getVal('useskin')!='mobileskin')
				{
					$encodedTitle=rawurlencode($pageTitle);
					$output.='</ul></td><td valign="top"><div style="margin:0px 0px 0px 40px"><img src="loading.gif" alt="Graph" name="refresh"/></div><div id="countDownTime">&nbsp;</div></td></tr>';

					//javascript that refreshes the graph
					$output.='<SCRIPT>var duration='.$timeleft.';var timeRemaining=0;var timer;var imageSwap=true;var refreshCount=4;var myImage=new Image();var image = "/utkgraph/totalgraphForJundgprogress.php?pageTitle='.$encodedTitle.'&background='.$background.'";var countDownDate=new Date();var days=0;var daystr="";var hours=0;var hourstr="";var mins=0;var minstr="";var secs=0;var secstr="";var currDate=new Date();var currTime=0;var elapsedTime=0;var startTime=currDate.getTime();var nameOfCookie="'.$surveyID.'";var durationCookie = getCookie(nameOfCookie+"duration");if(durationCookie==null || durationCookie!=duration){setCookie(nameOfCookie+"duration", duration, 1);}var startCookie = getCookie(nameOfCookie);if(startCookie==null || durationCookie!=duration){setCookie(nameOfCookie, startTime, 1);}else{duration-=(startTime-startCookie)/1000;}function getCookie(NameOfCookie){if (document.cookie.length > 0){begin = document.cookie.indexOf(NameOfCookie+"="); if (begin!= -1){begin += NameOfCookie.length+1;end = document.cookie.indexOf(";", begin);if (end == -1) end = document.cookie.length;return unescape(document.cookie.substring(begin, end));} }return null; }function setCookie(NameOfCookie, value, expiredays){var ExpireDate = new Date ();ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));document.cookie = NameOfCookie + "=" + escape(value) + ((expiredays == null)? "":"; expires="+ ExpireDate.toGMTString());}var xmlhttp;var tryLoadURLAgain=false;function loadXMLDoc(url){if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send(null);}else if (window.ActiveXObject){xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");if (xmlhttp){xmlhttp.onreadystatechange=xmlhttpChange;			xmlhttp.open("GET",url,true);xmlhttp.send();}}}function xmlhttpChange(){if (xmlhttp.readyState==4){if (xmlhttp.status==200){var url=xmlhttp.responseText;var begin=url.indexOf("/utkgraph");myImage.src = url.substring(begin, 5000);imageSwap=false;}else{tryLoadURLAgain=true;}}}function update(){currDate=new Date();currTime=currDate.getTime();elapsedTime= currTime-startTime;timeRemaining=duration-elapsedTime/1000;timer=setTimeout("update()",1000);if(timeRemaining<0){clearTimeout(timer);window.location.reload();return;}refreshCount+=1;countDownDate.setTime(timeRemaining*1000);	months=countDownDate.getUTCMonth();days=countDownDate.getUTCDate()-1;hours=countDownDate.getUTCHours();mins=countDownDate.getUTCMinutes();secs=countDownDate.getUTCSeconds();if(months>0) monthstr=months+" Months "; else monthstr="";if(days>0) daystr=days+" Days "; else daystr=""; if(hours<10) hourstr="0"+hours; else hourstr=hours;if(mins<10) minstr="0"+mins; else minstr=mins;if(secs<10) secstr="0"+secs; else secstr=secs;document.getElementById("countDownTime").innerHTML="Time Remaining - "+monthstr+daystr+hourstr+":"+minstr+":"+secstr+".";if(refreshCount==5 || tryLoadURLAgain==true){tmp = new Date();tmp = "&time="+tmp.getTime();tryLoadURLAgain=false;loadXMLDoc(image+tmp);}if(imageSwap==false)if(myImage.complete){document.images["refresh"].src=myImage.src;myImage=new Image();imageSwap=true;refreshCount=0;}if(refreshCount==25){refreshCount=4;}}timer=setTimeout("update()",1000);</SCRIPT>';
				}
				else
					$output .= '</ul>';
			}
			else
			{
				if($wgUser->isLoggedIn())
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"username\" VALUE=\"$userName\" />";
				$output .= '</ul><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Enter My Vote" /></p>';
			}
		}
	}

	//add sms voting label
	if($surveyStatus == 'active' && $teleVoteAllowed!=0 && $userName==$author)
	{
		$output.='<tr><td colspan=2>';
		if($wgRequest->getVal('useskin')!='mobileskin')
		{
			$output.='<img src="sms.gif" />';
		}
		else
		{
			$output.='<img src="smss.gif" />';
		}
		$output.= 'To vote; ring a number above';
		if($teleVoteAllowed!=2)
			$output.=', use a web browser to visit this page';
		$countryCode="";
		if($outsideAustralia)
			$countryCode=" +61";
		$output.=' or SMS the <span style="color:#FF0000">red</span> digits corresponding to your choice to'.$countryCode.' 416906973.</td></tr>';
	}
	/*
	$background=urldecode($background);
	$startDate=date("d/m/Y H:i", $startTimeStamp);
	$endDate=date("d/m/Y H:i", $endTimeStamp);*/
	if($surveyStatus == 'ready')
		$output .= '</table></form>';
	else
		$output .= '</table></form><p><script>var d=new Date(); d.setTime('.$startTimeStamp.'*1000);document.write("Start Time: "+d.toLocaleString());</script></p><p><script>var d=new Date(); d.setTime('.$endTimeStamp.'*1000);document.write("End Time: "+d.toLocaleString());</script></p>';//<p>Start time: $startTime<br />End time: $endTime<br />Now: $now<br />background:$background<br />Time Remaining: $timeleft seconds<br />$tt<br />$ttt<br />$tttt<br />background:$background</p>
	//$output .= "background:$background";
	//$output .="StartTime=$startTime<br />";
	//$output .="EndTime=$endTime<br />";

	//disconnect from database
	odbc_close($connectionstring);

    return $output;
}
?>