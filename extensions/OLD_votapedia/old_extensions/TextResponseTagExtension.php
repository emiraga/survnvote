<?php
old_stuff();
# Choice Tag WikiMedia extension
# with WikiMedia's extension mechanism it is possible to define
# a Choice Tag of this form
# <CHOICE> some text </CHOICE>
# the function registered by the extension gets the text between the
# tags as input and can transform it into a voting page (HTML code).
# Note: iText but directly
#       included in the HTML output. So WikiThe output is not interpreted as Wik markup is not supported.
# To activate the extension, include it from your LocalSettings.php
# with: include("extensions/YourExtensionName.php");

require_once( 'filerepo/Image.php' );
require_once("./SurveySettings.php");

$wgExtensionFunctions[] = "wfTextResponseTagExtension";

function wfTextResponseTagExtension() {
    global $wgParser;
    # register the extension with the WikiText parser
    # the first parameter is the name of the new tag.
    # In this case it defines the tag <example> ... </example>
    # the second parameter is the callback function for
    # processing the text between the tags
    $wgParser->setHook( "TextResponse", "renderTextResponse" );
	$wgParser->disableCache();
}


# The callback function for converting the input text to HTML output
function renderTextResponse( $input, $argv ) {
    # $argv is an array containing any arguments passed to the
    # extension like <TextResponse argument="foo" bar>..
    # Put this on the sandbox page:  (works in MediaWiki 1.5.5)
    #   <TextResponse argument="foo" argument2="bar">Testing text **example** in between the new tags</TextResponse>
	global $wgRequest,$wgUser,$wgParser,$wgTitle,$wgOut;
	global $gDataSourceName;
	global $gDBUserName;
	global $gDBUserPassword;
	$output='';
	
	$wgParser->disableCache();//disable cache because mobile and desktop skin requires different ways to render the barchart.

	//get the title of the page
	$pageTitle=$wgRequest->getVal( "title" ); //$wgTitle->getText(); doesn't work here because the special page is included in a normal wiki page.
	//get rid of the underbars in the pageTitle
	$trans = array("_" => " ");//, "hi" => "hello");
	$pageTitle=strtr($pageTitle, $trans);
	$encodedTitle=urlencode($pageTitle);
	
	//Give a warning message when there are more than one TextResponse tag in the page.
	$detectSecondTextResponseTag=md5($pageTitle).time();
	$detectSecondTextResponseTag2=md5($pageTitle).(time()-1);
	if(isset($_SESSION[$detectSecondTextResponseTag]) || isset($_SESSION[$detectSecondTextResponseTag2]))
	{
		return "<strong>Warning:</strong> A survey page can only have one &lt;TextResponse&gt; tag. <br />";
		exit;
	}
	$action=$wgRequest->getVal( "action" );
	//if($action != 'submit' && $_GET['purgecache']!='true')//the session variable should not be set when submitting the page.
	if($action != 'submit')
		if(isset($_GET['purgecache']))
			if($_GET['purgecache']!='true')
				$_SESSION[$detectSecondTextResponseTag]='1';

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
	global $gDBUserName;
	global $gDBUserPassword;
	global $gDataSourceName;
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
			$output.="<div><a href=\"./index.php?title=$encodedTitle&action=edit\">Edit</a> this page to modify your survey. </div>";
		}
		else
		{
			$output.="<div>This survey is created by $author and still under construction, you can <a href=\".index.php?title=$encodedTitle&action=edit\">edit</a> this page if you have permission form $author.</div>";
		}
	}
	$output.='<form action="./database/processTextResponse.php?" method="post"><table cellspacing="0" width="100%" style="font-size:large">';
	$encodedTitle=urlencode($pageTitle);
	$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";
	$content=explode("\n",$input);
	$i=0;
	
	
	//add sms voting label
	if($surveyStatus == 'active')
	{
		$output.='<tr><td>';
		if($wgRequest->getVal('useskin')!='mobileskin')
		{
			$output.='<img src="sms.gif" />';
		}
		else
		{
			$output.='<img src="smss.gif" />';
		}
		$output.= 'To participate; SMS your answer to +61 416906973';
		if($teleVoteAllowed!=2)
			$output.='or use a web browser to visit this page';
		$output.='.</td></tr>';
	}
	$output .= '<tr><td valign="top"><img src="./database/spacer.gif" />';//put an 250*1 spacer image above the floating text area so that the text doesn't get squashed by the graph when browser is less than full screen.
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

		$phone=$wgUser->getMobilePhone();
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"phone\" VALUE=\"$phone\" />";

		$resultsAtEnd='no';
		if(isset($argv["resultsatend"]))
			$resultsAtEnd=$argv["resultsatend"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"resultsatend\" VALUE=\"$resultsAtEnd\" />";

		$votesallowed='1';
		if(isset($argv["votesallowed"]))
			$votesallowed=$argv["votesallowed"];
		$output.="<INPUT TYPE=\"Hidden\" NAME=\"votesallowed\" VALUE=\"$votesallowed\" />";
	}

	$smsIndex=0;
	$lastCallDate='';
	$voted=false;
	$voter='';
	if($surveyStatus=='active' || $surveyStatus=='ended')
	{
		if($userName==$author && $surveyStatus=='ended')
			$output.='<SCRIPT>function loadXMLDoc(url){if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send(null);}else if (window.ActiveXObject){xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");if (xmlhttp){xmlhttp.onreadystatechange=xmlhttpChange;			xmlhttp.open("GET",url,true);xmlhttp.send();}}}function xmlhttpChange(){if (xmlhttp.readyState==4){if (xmlhttp.status==200){var response=xmlhttp.responseText;var temp = new Array();temp = response.split("<br/>");var pair=new Array();for(var i=0;i<temp.length;i++){ pair=temp[i].split("<=>");if(pair[0]!=""){ if(pair[0]=="added"){var image=document.getElementById("img"+pair[1]);image.setAttribute("onClick","deleteFromUserView("+pair[1]+")",0);image.innerHTML="<img alt=\"Delete From Audience View\" onmouseover=\"this.src='."'correctOver.gif'".'\" onmouseout=\"this.src='."'correct.gif'".'\" src=\"correct.gif\">";}else if(pair[0]=="deleted"){var image=document.getElementById("img"+pair[1]); image.setAttribute("onClick","addToUserView("+pair[1]+")",0); image.innerHTML="<img alt=\"Add to Audience View\" onmouseover=\"this.src='."'addOver.gif'".'\" onmouseout=\"this.src='."'add.gif'".'\" src=\"add.gif\">";}}}}}} function addToUserView(msgid){var addRequest = "/database/addToTextResponseUserView.php?surveyID='.$surveyID.'&id="+msgid; loadXMLDoc(addRequest);}function deleteFromUserView(msgid){var delRequest = "/database/deleteFromTextResponseUserView.php?surveyID='.$surveyID.'&id="+msgid; loadXMLDoc(delRequest);}</SCRIPT>';
			
		$output.='<style type="text/css">div.element{width: 100%;margin: 10;padding: 5px;font-family: Lucida Grande, Arial, sans-serif;font-size: 0.9em;background-color: #eee;border: 1px solid #ccc;}</style>';
			
		$output.='<tr><td><div id="sms">';
		
		//generate the list of sms
		if($userName==$author)
		{
			$Query3 = "SELECT * FROM textresponsesms WHERE surveyid = '$surveyID' ORDER BY time DESC";
			$queryexe3 = odbc_do($connectionstring, $Query3);
			while(odbc_fetch_row($queryexe3))
			{
				$id = odbc_result($queryexe3, 'id');
				$caller = odbc_result($queryexe3, 'sender');
				$username = odbc_result($queryexe3, 'username');
				$realname = odbc_result($queryexe3, 'realname');
				$content = odbc_result($queryexe3, 'sms');
				$time = odbc_result($queryexe3, 'time');
				$accepted = odbc_result($queryexe3, 'accepted');
				if($lastCallDate=='')
					$lastCallDate=$time;
				$smsIndex++;
				$output.='<div class="element" id="sms'.$id.'">';
				if($accepted==1)
					$output.='<a id="img'.$id.'" title="Delete From Audience View" onClick="deleteFromUserView('.$id.')"><img alt="Delete From Audience View" onmouseover="this.src='."'correctOver.gif'".'" onmouseout="this.src='."'correct.gif'".'" src="correct.gif"></a>&nbsp;';
				else
					$output.='<a id="img'.$id.'" title="Add to Audience View" onClick="addToUserView('.$id.')"><img alt="Add to Audience View" onmouseover="this.src='."'addOver.gif'".'" onmouseout="this.src='."'add.gif'".'" src="add.gif"></a>&nbsp;';

				$displayCaller=$username;
				if($realname!='')
					$displayCaller=$realname;
				else if($caller!='')
					$displayCaller=substr($caller,0,strlen($caller)-2).'**';
				
				if(strlen($username)==32)
					$displayCaller='Anonymous';
				$output.='<strong><span style="color:#128a12">';
				$output.="$displayCaller:</span></strong> $content";
				$output.='</div>';
			}
		}
		else//if($userName!=$author)
		{
			if($wgUser->isLoggedIn())//if the user has logged in, check the database to see if he participated in the quiz.
			{
				$voter=$userName;
			}
			else if(isset($_COOKIE['anonyuid']))//if the user has not logged in, check the cookie.
			{
				$voter=$_COOKIE['anonyuid'];
			}
			else//give an anonymous user id to the user.
			{
				$u = md5(uniqid(rand(), true));
				setcookie('anonyuid', $u, time()+10*365*24*3600,'/');//expire after ten years
			}
			$Query = "SELECT * FROM textresponsesms WHERE surveyid = $surveyID and username = '$voter'";
			$queryexe = odbc_do($connectionstring, $Query);
			if(odbc_fetch_row($queryexe))
			{
				$voted=true;
			}

			$Query3 = "SELECT * FROM textresponsesms WHERE surveyid = '$surveyID' AND ( accepted = 1 OR username='$voter' ) ORDER BY acceptedTime DESC";
			$queryexe3 = odbc_do($connectionstring, $Query3);
			while(odbc_fetch_row($queryexe3))
			{
				$id = odbc_result($queryexe3, 'id');
				$caller = odbc_result($queryexe3, 'sender');
				$username = odbc_result($queryexe3, 'username');
				$realname = odbc_result($queryexe3, 'realname');
				$content = odbc_result($queryexe3, 'sms');
				$time = odbc_result($queryexe3, 'time');
				$accetpedTime = odbc_result($queryexe3, 'acceptedTime');
				$content = stripslashes($content);
				if($lastCallDate=='')
					$lastCallDate=$accetpedTime;
				$smsIndex++;
				$output.='<div class="element" id="sms'.$id.'">';
				$displayCaller=$username;
				if($realname!='')
					$displayCaller=$realname;
				else if($caller!='')
					$displayCaller=substr($caller,0,strlen($caller)-2).'**';

				if(strlen($username)==32)
					$displayCaller='Anonymous';
				$output.='<strong><span style="color:#128a12">';
				$output.="$displayCaller:</span></strong> $content";
				$output.='</div>';
			}
		}
		$output.='</div>';
	}
	
	if($surveyStatus=='ended')
	{
		if($userName==$author)
		{
			if($wgRequest->getVal('useskin')!='mobileskin')
			{
				$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Restart survey" /></p>';
			}
			else//mobile skin
			{
				$output.='<p><input type="submit" name="Submit" value="Restart survey" /></p>';
			}
		}
		if($userName==$author)
			$output.="<div>  You can click <img src=\"add.gif\"> button to add incoming messages to an <a href=\".textResponseUserView.php?survey=$surveyID\" TARGET=\"_blank\">Audience View</a> so that only the selected messages are visible to the audience. You can also choose this <a href=\"textResponseUserView.php?survey=$surveyID&name=yes\" TARGET=\"_blank\">Audience View that shows the real name</a> of the sender.</div>";
		$output.='</td></tr>';
	}
	else if($surveyStatus=='ready')
	{
		if($userName==$author)
		{
			$output.='<p><input type="submit" name="Submit" value="Start survey" /></p></td></tr>';
		}
		else
		{
			$output.='</td></tr>';
		}
	}
	else if($surveyStatus=='active')
	{
		if($userName==$author)
			$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Finish survey" />&nbsp;&nbsp;<input type="submit" name="Submit" value="Reset survey" /></p></td></tr><tr><td valign="top">';
		else
		{
			if(!$voted && $teleVoteAllowed!=2)//web voting interface
			{
				if($wgUser->isLoggedIn())
				{
					$output.='<p style="margin:10px 10px 10px 10px"><textarea name="TextResponseAnswer" cols=35 rows=3></textarea><input type="submit" name="Submit" value="Enter my answer" /></p>';
					$output.="<INPUT TYPE=\"Hidden\" NAME=\"username\" VALUE=\"$userName\" />";
				}
				else if( $anonymousVoteAllowed )
					$output.='<p style="margin:10px 10px 10px 10px"><textarea name="TextResponseAnswer" cols=35 rows=3></textarea><input type="submit" name="Submit" value="Enter my answer" /></p>';
				else
					$output.='<p style="margin:10px 10px 10px 10px">This survey only allows registered users to answer, you have to login to enter your answer.</p>';
			}
			$output.='</td></tr><tr><td valign="top">';
		}
		//add total number of voters
		$output.='<div align="left" id="total" style="margin:10px 10px 10px 10px">&nbsp;</div>';
		//add count down timer
		$output.='<div align="left" id="countDownTime" style="margin:10px 10px 10px 10px">&nbsp;</div>';
		if($userName==$author)
			$output.="<div>  You can click <img src=\"add.gif\"> button to add incoming messages to an <a href=\"textResponseUserView.php?survey=$surveyID\" TARGET=\"_blank\">Audience View</a> so that only the selected messages are visible to the audience. You can also choose this <a href=\"textResponseUserView.php?survey=$surveyID&name=yes\" TARGET=\"_blank\">Audience View that shows the real name</a> of the sender.</div>";
		
		$output.='</td></tr>';
		
		if($wgRequest->getVal('useskin')!='mobileskin')
		{
			$encodedTitle=rawurlencode($pageTitle);

			//javascript that refreshes the graph
			$output.='<SCRIPT>var duration='.$timeleft.';var numSMS='.$smsIndex.';var displayNumSMS=30;var lastCallDate="'.$lastCallDate.'";var fader=new Array();var refreshCount=49;var timeRemaining=0;var timer;var requestURL = "/database/updateTextResponse';
			if($userName!=$author)
				$output.='Accepted';
			$output.='.php?surveyID='.$surveyID;
			if($userName!=$author)
				$output.='&voter='.urlencode($voter);
			$output.='";var countDownDate=new Date();var days=0;var daystr="";var hours=0;var hourstr="";var mins=0;var minstr="";var secs=0;var secstr="";var currDate=new Date();var currTime=0;var elapsedTime=0;var startTime=currDate.getTime();var nameOfCookie="'.$surveyID.'";var durationCookie = getCookie(nameOfCookie+"duration");if(durationCookie==null || durationCookie!=duration){setCookie(nameOfCookie+"duration", duration, 1);}var startCookie = getCookie(nameOfCookie);if(startCookie==null || durationCookie!=duration){setCookie(nameOfCookie, startTime, 1);}else{duration-=(startTime-startCookie)/1000;}function getCookie(NameOfCookie){if (document.cookie.length > 0){begin = document.cookie.indexOf(NameOfCookie+"="); if (begin!= -1){begin += NameOfCookie.length+1;end = document.cookie.indexOf(";", begin);if (end == -1) end = document.cookie.length;return unescape(document.cookie.substring(begin, end));} }return null; }function setCookie(NameOfCookie, value, expiredays){var ExpireDate = new Date ();ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));document.cookie = NameOfCookie + "=" + escape(value) + ((expiredays == null)? "":"; expires="+ ExpireDate.toGMTString());}var xmlhttp;var tryLoadURLAgain=false;function loadXMLDoc(url){if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();xmlhttp.onreadystatechange=xmlhttpChange;xmlhttp.open("GET",url,true);xmlhttp.send(null);}else if (window.ActiveXObject){xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");if (xmlhttp){xmlhttp.onreadystatechange=xmlhttpChange;			xmlhttp.open("GET",url,true);xmlhttp.send();}}}function xmlhttpChange(){if (xmlhttp.readyState==4){if (xmlhttp.status==200){var response=xmlhttp.responseText;var temp = new Array();temp = response.split("<br/>");var pair=new Array();for(var i=0;i<temp.length;i++){ pair=temp[i].split("<=>");if(pair[0]!=""){ if(pair[0]=="total"){document.getElementById(pair[0]).innerHTML="Number of participants = "+pair[1];}else if(pair[0]=="added"){var image=document.getElementById("img"+pair[1]);image.setAttribute("onClick","deleteFromUserView("+pair[1]+")",0);image.innerHTML="<img alt=\"Delete From Audience View\" onmouseover=\"this.src='."'correctOver.gif'".'\" onmouseout=\"this.src='."'correct.gif'".'\" src=\"correct.gif\">";}else if(pair[0]=="deleted"){var image=document.getElementById("img"+pair[1]); image.setAttribute("onClick","addToUserView("+pair[1]+")",0); image.innerHTML="<img alt=\"Add to Audience View\" onmouseover=\"this.src='."'addOver.gif'".'\" onmouseout=\"this.src='."'add.gif'".'\" src=\"add.gif\">";}else if(pair[0]!="nothing"){var caller=document.getElementById("sms");var c=caller.innerHTML;caller.innerHTML="<div id=\"sms"+pair[3]+"\" class=\"element\">';
			if($userName==$author)
				$output.='<a id=\"img"+pair[3]+"\" title=\"Add to Audience View\" onClick=\"addToUserView("+pair[3]+")\"><img alt=\"Add to Audience View\" onmouseover=\"this.src='."'addOver.gif'".'\" onmouseout=\"this.src='."'add.gif'".'\" src=\"add.gif\"></a>&nbsp;';
			$output.='<strong><span style=\"color:#128a12\">"+pair[0]+":</span></strong> "+pair[1]+"</div>"+c;lastCallDate=pair[2];fader.push("sms"+pair[3]);document.getElementById("sms"+pair[3]).style.opacity = 0;document.getElementById("sms"+pair[3]).style.filter = "alpha(opacity=0)";}}}tryLoadURLAgain=false;refreshCount=0;';
		$output.='}else{tryLoadURLAgain=true;}}}function update(){refreshCount+=1;timer=setTimeout("update()",100);if(fader.length>0){var ieop = parseFloat(document.getElementById( fader[0] ).style.opacity);ieop += 0.05;var op = ieop * 100;document.getElementById( fader[0] ).style.opacity = ieop;document.getElementById( fader[0] ).style.filter = "alpha(opacity="+op+")";if(document.getElementById( fader[0] ).style.opacity>=1.0){document.getElementById( fader[0] ).style.opacity=1.0;document.getElementById( fader[0] ).style.filter = "alpha(opacity=100)";fader.shift();}}if(refreshCount %10==0){currDate=new Date();currTime=currDate.getTime();elapsedTime= currTime-startTime;timeRemaining=duration-elapsedTime/1000;if(timeRemaining<0){clearTimeout(timer);window.location.reload();return;}countDownDate.setTime(timeRemaining*1000);	days=countDownDate.getUTCDate()-1;hours=countDownDate.getUTCHours();mins=countDownDate.getUTCMinutes();secs=countDownDate.getUTCSeconds();if(days>0) daystr=days+" Days "; else daystr=""; if(hours<10) hourstr="0"+hours; else hourstr=hours;if(mins<10) minstr="0"+mins; else minstr=mins;if(secs<10) secstr="0"+secs; else secstr=secs;document.getElementById("countDownTime").innerHTML="Time Remaining - "+daystr+hourstr+":"+minstr+":"+secstr+".";}if(refreshCount==50 || tryLoadURLAgain==true){tmp = new Date();tmp = "&calldate="+lastCallDate+"&time="+tmp.getTime();tryLoadURLAgain=false;loadXMLDoc(requestURL+tmp);}if(refreshCount==70){refreshCount=0;}}timer=setTimeout("update()",100);';
		if($userName==$author)
			$output.='function addToUserView(msgid){var addRequest = "./database/addToTextResponseUserView.php?surveyID='.$surveyID.'&id="+msgid; loadXMLDoc(addRequest);}function deleteFromUserView(msgid){var delRequest = "/database/deleteFromTextResponseUserView.php?surveyID='.$surveyID.'&id="+msgid; loadXMLDoc(delRequest);}</script>';
		else
			$output.='</SCRIPT>';
		}
	}
	
	if($surveyStatus == 'ready')
		$output .= '</table></form>';
	else
	{
		$output .= '</table></form><p><script>var d=new Date(); d.setTime('.$startTimeStamp.'*1000);document.write("Start Time: "+d.toLocaleString());</script></p><p><script>var d=new Date(); d.setTime('.$endTimeStamp.'*1000);document.write("End Time: "+d.toLocaleString());</script></p>';
	}
	//disconnect from database
	odbc_close($connectionstring);

    return $output;
}
?>