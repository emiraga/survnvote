<?php
old_stuff();
# RankExposition Tag WikiMedia extension
# with WikiMedia's extension mechanism it is possible to define
# a RankExposition Tag of this form
# <RankExposition>
# #Question
# ##Choice1
# ##Choice2
# ##.......
# *Exposition1
# *Exposition2
# *......
#
# </RankExposition>
# the function registered by the extension gets the text between the
# tags as input and can transform it into a voting page (HTML code).
# Note: iText but directly
#       included in the HTML output. So WikiThe output is not interpreted as Wiki markup is not supported.
# To activate the extension, include it from your LocalSettings.php
# with: include("extensions/YourExtensionName.php");

require_once( 'filerepo/Image.php' );
require_once("./SurveySettings.php");

$wgExtensionFunctions[] = "wfRankExpositionTagExtension";

function wfRankExpositionTagExtension() {
    global $wgParser;
    # register the extension with the WikiText parser
    # the first parameter is the name of the new tag.
    # In this case it defines the tag <RankExposition> ... </RankExposition>
    # the second parameter is the callback function for
    # processing the text between the tags
    $wgParser->setHook( "RankExposition", "renderRankExposition" );
}


# The callback function for converting the input text to HTML output
function renderRankExposition( $input, $argv=array() ) {
    # $argv is an array containing any arguments passed to the
    # extension like <RankExposition argument="foo" bar>..
    	global $wgRequest,$wgUser,$wgParser,$wgTitle,$wgOut;
	global $gDataSourceName;
	global $gDBUserName;
	global $gDBUserPassword;
	$output="";

	$wgParser->disableCache();//disable cache because mobile and desktop skin requires different ways to render the barchart.

	//get the title of the page
	$pageTitle=$wgRequest->getVal( "title" ); //$wgTitle->getText(); doesn't work here because the special page is included in a normal wiki page.
	//get rid of the underbars in the pageTitle
	$trans = array("_" => " ");//, "hi" => "hello");
	$pageTitle=strtr($pageTitle, $trans);
	$encodedTitle=urlencode($pageTitle);

	//Give a warning message when there are more than one RankExposition tag in the page.
	$detectSecondRankExpositionTag=md5($pageTitle).time();
	$detectSecondRankExpositionTag2=md5($pageTitle).(time()-1);
	if(isset($_SESSION[$detectSecondRankExpositionTag]) || isset($_SESSION[$detectSecondRankExpositionTag2]))
	{
		return "<strong>Warning:</strong> A survey page can only have one &lt;RankExposition&gt; tag. Please <a href=\"index.php?title=$encodedTitle&action=edit\">edit</a> this page and put all your choices in ONE &lt;RankExposition&gt; tag. If you need more than one set of choices, create another survey.<br />";
		exit;
	}
	$action=$wgRequest->getVal( "action" );
	//if($action != 'submit' && $_GET['purgecache']!='true')//the session variable should not be set when submitting the page.
	if($action != 'submit')
		if(isset($_GET['purgecache']))
			if($_GET['purgecache']!='true')
				$_SESSION[$detectSecondRankExpositionTag]='1';

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
	$now=date("Y-m-d H:i:s");
	$initDate= date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
	if(odbc_fetch_row($queryexe))
	{
		$resultIsNull=FALSE;
		//---get the results---
		$author = odbc_result($queryexe, 'author');
		$startTime = odbc_result($queryexe, 'startTime');
		$endTime = odbc_result($queryexe, 'endTime');
		$pageID = odbc_result($queryexe, 'pageID');

          //---categorising page status---
          //------------------------------
		if ($startTime - $initDate==0)
			$pageStatus = 'ready';
		else if ($endTime>$now)
			$pageStatus = 'active';
		else if ($endTime<$now)
			$pageStatus = 'ended';
		$teleVoteAllowed = odbc_result($queryexe, 'teleVoteAllowed');
                $anonymousVoteAllowed = odbc_result($queryexe, 'anonymousAllowed');
	}

	else
	{
          if($wgUser->isLoggedIn())
	   {
		$author=$wgUser->getName();
		$insertSQL="INSERT INTO page (title,author,startTime,endTime,createTime,duration) VALUES ('$encodedTitle','$author','$initDate','$initDate','$now',1)";
		odbc_do($connectionstring, $insertSQL);
		//execute query
		$queryexe2 = odbc_do($connectionstring, $Query);
		
		//query database
		if(odbc_fetch_row($queryexe2))
		{
		 //---get the results---
		  $author = odbc_result($queryexe2, 'author');
		  $startTime = odbc_result($queryexe2, 'startTime');
		  $endTime = odbc_result($queryexe2, 'endTime');
		  $pageID = odbc_result($queryexe2, 'pageID');

		
		    if ($startTime - $initDate==0)
		      	$pageStatus = 'ready';
		    else if ($endTime>$now)
		        $pageStatus = 'active';
	            else if ($endTime<$now)
			$pageStatus = 'ended';
			$teleVoteAllowed = odbc_result($queryexe2, 'teleVoteAllowed');
			$anonymousVoteAllowed = odbc_result($queryexe2, 'anonymousAllowed');
		  }
	     }
	  }
	
 //$output.=$pageStatus."<br>";
 //$output.=$author;
	//---determine whether the current user is the creator of the survey---
	$userName=$wgUser->getName();
	$currTimeStamp=time();
	$startTimeStamp=strtotime($startTime);
	$endTimeStamp=strtotime($endTime);
	$timeleft=$endTimeStamp-$currTimeStamp;
	


//-----------------------------------------------------
//Status 'Ready'
//-----------------------------------------------------
if($pageStatus=='ready')
{
    //--add some instructions
    if($userName==$author)
	{
	  $output.="<div>Click <a href=\"./index.php?title=$encodedTitle&action=edit\">here</a> or click the edit tab above to edit this page, which includes editing your question, choices, expositions and survey information.</div>";
	 }
    else
	{
	  $output.="<div>This survey is created by $author and still under construction, you can <a href=\"./index.php?title=$encodedTitle&action=edit\">edit</a> this page if you have permission from $author.</div>";
     }
}

//-----------------------

	//* Added by INTEG
	if (substr($input,0,1) == "\n")
		$input=substr($input,1,strlen($input));
	if (substr($input,strlen($input)-1,1) == "\n")
		$input=substr($input,0,strlen($input)-1);	
	// Added by INTEG *//
	
$content=explode("\n",$input);
$output.='<form action="./database/processRankExposition.php?" method="post"><table cellspacing="0" style="font-size:large">';

$encodedTitle=urlencode($pageTitle);
$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";


//-----------------------------------------------------
//Status 'Ready'
//-----------------------------------------------------

if($pageStatus=='ready')
{
//---get the parameters---
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
		
$webOnly='no';
if(isset($argv["webonly"]))
  $webOnly=$argv["webonly"];
$output.="<INPUT TYPE=\"Hidden\" NAME=\"webonly\" VALUE=\"$webOnly\" />";

$phone=$wgUser->getMobilePhone();
$output.="<INPUT TYPE=\"Hidden\" NAME=\"phone\" VALUE=\"$phone\" />";

$displayTop=3;
if(isset($argv["displaytop"]))
  $displayTop=$argv["displaytop"];
$output.="<INPUT TYPE=\"Hidden\" NAME=\"displaytop\" VALUE=\"$displayTop\" />";


//---get question and choices from wiki text---
//---------------------------------------------
//$i=0;
$type='';
$choiceIndex=0;
$questionIndex=0;
foreach ($content as $wiki)
{
 $wiki=trim($wiki);//get rid of the white spaces.
 $type='';
 if(strpos($wiki,'#')===FALSE)
    continue;//ignore things other than questions and choices.
	if(strpos($wiki,"##")===0)
	{
	 $type = 'choice';
         $wiki = substr($wiki,2);
         $wiki = trim($wiki);
	 }
	else if (strpos($wiki,"#")===0)
	{
	 $type = 'question';
	 $wiki = substr($wiki,1);
	 $wiki = trim($wiki);
	  }

        else
	  continue;

     $parsedWiki=$wgParser->parse($wiki,$wgTitle, $wgOut->ParserOptions(), false, false);
			
      if($type=='question')
      {
	$question=$parsedWiki->getText();
	if($question!="")
	{
          if($questionIndex>0)
	  $output.="</ul>";//close the choice list of the previous question
	  $wiki=urlencode($wiki);
	  $output.="<INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$wiki\" />";
	  $questionIndex++;

	  $output.="<label id=\"q$questionIndex\"><strong>$questionIndex. $question</strong></label>";
         /*
         $wiki=urlencode($wiki);
         $output.="<INPUT TYPE=\"Hidden\" NAME=\"question\" VALUE=\"$wiki\" />";
         $output.="<H4>$question</H4><ul>"; */
         }
        $choiceIndex=0;

	}

       else if($type=='choice')
       {
	 $choice=$parsedWiki->getText();
	  if($choice!="")
	  {
            $wiki=urlencode($wiki);
	    $output.="<INPUT TYPE=\"Hidden\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$wiki\" />";
	    $ci=chr(65+$choiceIndex);
	    $choiceIndex++;
	    //$percent=rand(20,70);
	    if($wgRequest->getVal('useskin')!='mobileskin')
	    {
	      $output.="<ul><li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$choiceIndex.jpg)\"><label id=\"q$questionIndex c$choiceIndex\">$ci. $choice</label></li></ul>";
	      }
	    else //mobile skin
	    {
	      $output.="<ul><li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$choiceIndex.jpg)\"><label id=\"q$questionIndex c$choiceIndex\">$ci. $choice</label><br /></li></ul>";
	     }

	    }
	  }
}//end of wiki text here



//---get the current top Exposition---
//--------------------------------------

$output.="<fieldset>
      <legend><H3>Current Top $displayTop</H3></legend>";

$runP='No Active Exposition';
   $output.= "<label><strong> $runP </strong></label>";
$output.="</fieldset>";


//---get Exposition from wiki text---
//-------------------------------------
$exposition=array();
$j=0;
foreach ($content as $wiki)
{
  $wikip=trim($wiki);//get rid of the white spaces.
  $type='';
  if (strpos($wikip,'*')===0)
  {
    $type = 'exposition';
    $wikip = substr($wikip,1);
    $wikip = trim($wikip);
    }
  $parsedWiki=$wgParser->parse($wikip,$wgTitle, $wgOut->ParserOptions(), false, false);
  if($type=='exposition')
  {
    $exposition[$j]=$parsedWiki->getText();
    if($exposition[$j]!="")
     {
       $j++;
       $wikip=urlencode($wikip);
       $output.="<INPUT TYPE=\"Hidden\" NAME=\"exposition[]\" VALUE=\"$wikip\" />";

      }
   }
 }


$output.="<br><H4>Choose current exposition</H4><select name=\"chosenexposition\">";
$output.="<option value=\"None\">Exposition List</option>";
$expositionIndex=0;
$k=0;
while ($k < $j)
{
  $chopres=$exposition[$k];
  $output.=$chopres;
  $k++;
  $expositionIndex++;
  $output.="<option value=\"$expositionIndex\">$k. $chopres</option>";

  }

$output.="</select><p>";

//---start the survey button---
//-----------------------------
if($userName==$author)
{
   if($wgRequest->getVal('useskin')!='mobileskin')
   {
      $output.='</ul><p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Start survey" /></p></td>';
     }
   else//mobile skin
   {
      $output.='</ul><p><input type="submit" name="Submit" value="Start survey" /></p></td>';
     }
}
else
  $output.='</ul></td>';

$output.='</table></form>';

}//end of ready status

//-------------------------------------------------------------
//Status 'Active'
//-------------------------------------------------------------

else if($pageStatus=='active')
{
    $output.='<table cellspacing="7" style="font-size:large">';
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

 //---get the data from the database

  $survey = array();
  $surveyID = array();
  //---get the surveys---
	$sQuery = "SELECT * FROM survey WHERE pageID = $pageID ORDER BY surveyID";
	$squeryexe = odbc_do($connectionstring, $sQuery);
	while(odbc_fetch_row($squeryexe))
	{
	 $survey[] = odbc_result($squeryexe, 'question');
	 $surveyID[] = odbc_result($squeryexe, 'surveyID');
	 }

       //---get the active exposition
        $currsurveyID=$surveyID[0];
        $activeQuery = "Select * from presentation where surveyID=$currsurveyID and active=1";
        $numprsQuery = "Select count(presentation) as numprs from presentation where surveyID=$currsurveyID and active=1";

        $activequeryexe = odbc_do($connectionstring, $activeQuery);
        $numprsqueryexe = odbc_do($connectionstring, $numprsQuery);

        while (odbc_fetch_row($numprsqueryexe))
        {
          $numprs = odbc_result($numprsqueryexe,'numprs');
         }  
        
        $active = 'No exposition currently active';
        if ($numprs!=0)
        {
          while (odbc_fetch_row($activequeryexe))
          {
           $active = odbc_result($activequeryexe,'presentation');
           $active = urldecode ($active);
           }
         }
        
        $output.="<tr><td colspan=3><H4><span style=\"color:#0099FF\">$active</span></H4></td></tr>";

        //---get the question(s)
	$questionIndex=0;
	foreach ($surveyID as $sid )
	{
	 //---check whether the user has already voted---
	 $voted=false;
	 $check=md5($sid);
	 $votedChoice=0;
	 if($wgUser->isLoggedIn())//-->if the user has logged in, check the database to see if he voted before.
	 {
	   $vQuery = "SELECT * FROM surveyrecord WHERE surveyID = $sid and voterID = '$userName'";
	   $vqueryexe = odbc_do($connectionstring, $vQuery);
	   if(odbc_fetch_row($vqueryexe))
	   {
	     $votedChoice=odbc_result($vqueryexe, 'choiceID');
	     $voted=true;
	    }
	  }
         else if(isset($_COOKIE[$check]))//-->if the user has not logged in, check the cookie.
	{
	  $votedChoice=$_COOKIE[$check];
	  $voted=true;
	 }
	  $testCookie='';
	  if(!$voted)
	  {
	    $testCookie=time();
	    setcookie ("testCookie$testCookie", $testCookie, time() + (60*60*24));
	   }
		
	$questionWiki=$survey[$questionIndex];
	$questionWiki=urldecode($questionWiki);
	$parsedQuestion=$wgParser->parse($questionWiki,$wgTitle, $wgOut->ParserOptions(), false ,false);
	$question=$parsedQuestion->getText();
	$questionIndex++;
	$receiver = array();
	$savedChoice = array();

	//---get the choices
	$chQuery = "SELECT * FROM surveychoice WHERE surveyID = $sid ORDER BY choiceID";
	$chqueryexe = odbc_do($connectionstring, $chQuery);
	while(odbc_fetch_row($chqueryexe))
	{
	  $receiver[] = odbc_result($chqueryexe, 'receiver');
	  $savedChoice[] = odbc_result($chqueryexe, 'choice');
	  }

     $output .="<INPUT TYPE=\"Hidden\" NAME=\"question[]\" VALUE=\"$question\" />";
     $output.="<tr><td colspan=3>$questionIndex. $question</td></tr>";
		
	$choiceIndex=0;//choice index
	foreach( $savedChoice as $ch)
	{
	  $ch=urldecode($ch);
	  $parsedChoice=$wgParser->parse($ch,$wgTitle, $wgOut->ParserOptions(), false, false );
	  $ch=$parsedChoice->getText();
	  $r=$receiver[$choiceIndex];
	  //---displaying phone numbers---

	  $len=strlen($r);
	  $r1=substr($r,0,$len-2);//the first few digits
	  $r2=substr($r,$len-2,2);//the last two digits

	  $ci=chr(65+$choiceIndex);
	  $choiceIndex++;

	  if($wgRequest->getVal('useskin')!='mobileskin')
	  {
	   if($userName==$author)
	   {
	    $output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;$ci. $ch</td>";
	    
	    if($teleVoteAllowed)
	     {
		$l=strlen($r1);
		if($l==2)//old PBX only returns 4 digits telephone number, add 6216 in the front.
		  $output.="<td><img src=\"telephone.gif\" />+61 2 6216$r1<span style=\"color:#FF0000\">$r2 </span>";
		else if($l==6)//new PBX returns 8 digits telephone number.
		  $output.="<td><img src=\"telephone.gif\" />+61 2 $r1<span style=\"color:#FF0000\">$r2 </span>";
		}
            $output.="</td></tr>";
	     }
	   else
	   {
	    $output.="<tr><td><form action=\"./database/processRankExposition.php?\" method=\"post\">";
	    $output .="<INPUT TYPE=\"Hidden\" NAME=\"testCookie\" VALUE=\"$testCookie\" />";
	    //$output.="<INPUT TYPE=\"Hidden\" NAME=\"choice\" VALUE=\"$sid+"."$choiceIndex\" />";

            $checked='';
             if($voted)
	     {
               if ($votedChoice==$ch)
                 $checked = 'checked';
               $output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE=\"Radio\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$choiceIndex\" $checked>$ch</td>";
               //$output.="<ul><li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$choiceIndex.jpg)\"><label id=\"q$questionIndex c$choiceIndex\">$ci. $ch</label></li></ul>";
	      }
             else
             {
	       $output.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE=\"Radio\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$choiceIndex\">$ch</td>";
              }

	    $output.="<td colspan=2></td></tr>";
            }
           }
	  else//use mobile skin
	  {
	    if($userName==$author)
	    {
	     $output.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;$ci. $ch";
	     
	     if($teleVoteAllowed)
	     {
		$l=strlen($r1);
		if($l==2)//old PBX only returns 4 digits telephone number, add 6216 in the front.
		  $output.="<div align=right><img src=\"telephone.gif\" />+61 2 6216$r1<span style=\"color:#FF0000\">$r2 </span></div>";
		else if($l==6)//new PBX returns 8 digits telephone number.
		  $output.="<div align=right><img src=\"telephone.gif\" />+61 2 $r1<span style=\"color:#FF0000\">$r2 </span></div>";
		}

	     $output.="<p>&nbsp;&nbsp;</p></td></tr>";
	     }
	    else
	    {
	     $output.="<tr><td colspan=3><form action=\"./database/processRankExposition.php?\" method=\"post\">";
	     $output .="<INPUT TYPE=\"Hidden\" NAME=\"testCookie\" VALUE=\"$testCookie\" />";
	     $output.="<INPUT TYPE=\"Hidden\" NAME=\"choice\" VALUE=\"$sid+"."$choiceIndex\" />";
	
             $output.="<tr><td colspan=3>";

	     $checked='';
	     if($voted)
	     {
	      foreach($votedChoice as $ch)
	      	if($ch==$choiceIndex)
		$checked='checked="checked"';
	      }
	     $inputType='radio';

             $output.="&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE=\"$inputType\" NAME=\"q$questionIndex"."choice[]\" VALUE=\"$sid+"."$choiceIndex\" $checked> $c</td>";

	     $output.="</td></tr>";

              }
            }
	   }
      }//end foreach($surveyID as $sid)


//---get the current top exposition---
//--------------------------------------

//---get exposition data from database---

$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
//SQL query
  $pQuery = "SELECT presentationID, presentation, marks FROM view_presentation_page_mark where pageID=$pageID and marks is not NULL ORDER BY marks DESC";
  $numPQuery = "SELECT count(*) as numpres FROM view_presentation_page_mark where pageID=$pageID and marks is not NULL";
  $topQuery="Select displayTop from page where pageID=$pageID";

//execute query
  $pqueryexe = odbc_do($connectionstring, $pQuery);
  $numpqueryexe = odbc_do($connectionstring, $numPQuery);
  $topqueryexe = odbc_do($connectionstring, $topQuery);

  while(odbc_fetch_row($numpqueryexe))
   {
     $numpres= odbc_result($numpqueryexe,'numpres');
    }
  while(odbc_fetch_row($topqueryexe))
   {
     $Top= odbc_result($topqueryexe,'displayTop');
    }

$output.="<head><META HTTP-EQUIV=\"Refresh\" CONTENT=10></head>";

$output.="<tr><td><fieldset>";
//$output.="<legend><H3>Current Top $Top</H3></legend><br>";

if ($numpres==0)
 {
   $runP='No Exposition with votes';
   $output.="<legend><H3>Current Top Exposition</H3></legend><br>";
   $output.= "<label><strong> $runP </strong></label>";
   $output.="</fieldset></tr></td>";
  }
else
 {
  $runP=array();
  $runM=array();
  $i=1;
  while(odbc_fetch_row($pqueryexe))
    {
       $runExposition = odbc_result($pqueryexe, 'presentation');
       $runMark = odbc_result($pqueryexe, 'marks');
       $runExposition = urldecode($runExposition);
       $runP[$i] = $runExposition;
       $runM[$i] = $runMark;
       $i++;
      }

 $a=$i-1;

  if ($a>$Top)
   {
     $output.="<legend><H3>Current Top $Top</H3></legend>";
     $j=1;
     while ($j<=$Top)
     {
       $output.= "<label><ul><li> $runP[$j] </ul></label>";
       $j++;
      }
    }
  else
    {
     $output.="<legend><H3>Current Top $a</H3></legend>";
     $j=1;
     while ($j<=$a)
     {
       $output.= "<label><ul><li> $runP[$j] </ul></label>";
       $j++;
      }
    }
  $output.="</fieldset></tr></td>";
 }

//---enter my vote button---

if($userName!=$author)
{
   if($wgUser->isLoggedIn()&& !$voted)
     //$output.="<tr><td><form action=\"/database/processRankExposition.php?\" method=\"post\">" ;
     $output.="<INPUT TYPE=\"Hidden\" NAME=\"username\" VALUE=\"$userName\" />";
     $output .= '<tr><td colspan=3><p style="margin:10px 0px 0px 20px"><input type="submit" name="Submit" value="Enter My Vote" /></p></td></tr></form></td></tr>';
 }

 //---Choose Exposition---
//--------------------------
if($userName==$author)
{
//query database for Exposition list
  $surveyfirst=$surveyID[0];
  $plistQuery="SELECT presentationID, presentation FROM presentation where surveyID=$surveyfirst";

//execute query
  $plistqueryexe = odbc_do($connectionstring, $plistQuery);

$output.='<tr><td><form action="./database/processRankExposition.php?" method="post">';
$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";
$output.="<tr><td><H4>Choose current exposition</H4><select name=\"chosenexposition\" onChange=\"submit()\">";
$output.="<option value=\"None\">Select Exposition</option>";

  $p=0;
  while (odbc_fetch_row ($plistqueryexe))
  {
    $listExposition = odbc_result($plistqueryexe, 'presentation');
    $presID = odbc_result($plistqueryexe, 'presentationID');
    $listExposition = urldecode($listExposition);
    $output.="<option value=\"$presID\">$presID. $listExposition</option>";
    }

$output.="</select></td></tr>";
$output.="</form></td></tr>";

}//end choose Exposition

//---run Exposition button---
/*
if($userName==$author)
{
   $output.='<tr><td><form action="./database/processRankExposition.php?" method="post">';
   $output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";
   $output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="run RankExposition" /></p></td></tr></form>';
  }
*/

     if($wgRequest->getVal('useskin')!='mobileskin')
     {
      $output.='<tr><td><img src="./database/spacer.gif" /></td><td><img src="./database/spacer2.gif" /></td><td></td></tr></table>';
      }
     else
     {
      $output.='</table>';
      }

//---add sms voting label---

if( $teleVoteAllowed && $userName==$author)
{
  $output.='<div style="font-size: large">';
  if($wgRequest->getVal('useskin')!='mobileskin')
    $output.='<img src="sms.gif" />';
  else
    $output.='<img src="smss.gif" />';
    $output.=' To vote; ring a number above, use a web browser to visit this page or SMS the <span style="color:#FF0000">red</span> digits corresponding to your choice to +61 416906973.</div>';
 }



//---finish survey button--- 

		if($userName==$author)
		{
			$output.='<form action="./database/processRankExposition.php?" method="post">';
			$output.="<INPUT TYPE=\"Hidden\" NAME=\"title\" VALUE=\"$encodedTitle\" />";
			$output.='<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Finish survey" /></p></form>';
		}

$output.='</table></form>';
}// end of active status


//-------------------------------------------------------------
//Status 'Ended'
//-------------------------------------------------------------
else if($pageStatus=='ended')
{
 //---get the parameters ---
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
		
$webOnly='no';
if(isset($argv["webonly"]))
  $webOnly=$argv["webonly"];
$output.="<INPUT TYPE=\"Hidden\" NAME=\"webonly\" VALUE=\"$webOnly\" />";

$phone=$wgUser->getMobilePhone();
$output.="<INPUT TYPE=\"Hidden\" NAME=\"phone\" VALUE=\"$phone\" />";

$displayTop=3;
if(isset($argv["displaytop"]))
  $displayTop=$argv["displaytop"];
$output.="<INPUT TYPE=\"Hidden\" NAME=\"displaytop\" VALUE=\"$displayTop\" />";

//---get the surveys data---
$survey = array();
$surveyID = array();
//---get the surveys---
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
    $questionWiki=$survey[$questionIndex];
    $questionWiki=urldecode($questionWiki);
    $parsedQuestion=$wgParser->parse($questionWiki,$wgTitle, $wgOut->ParserOptions(), false ,false);
    $question=$parsedQuestion->getText();
    $questionIndex+=1;
    $receiver = array();
    $savedChoice = array();

    //---get the choices---
    $chQuery = "SELECT * FROM surveychoice WHERE surveyID = $sid ORDER BY choiceID";
    $chqueryexe = odbc_do($connectionstring, $chQuery);
    while(odbc_fetch_row($chqueryexe))
    {
     $receiver[] = odbc_result($chqueryexe, 'receiver');
     $savedChoice[] = odbc_result($chqueryexe, 'choice');
     }

       	$output.="<tr><td colspan=2>$questionIndex. $question</td></tr>";
			
      //---add choices---
      $choiceIndex=0;
      foreach ($savedChoice as $choiceWiki)
      {
	$choiceWiki=urldecode($choiceWiki);
	//$choiceWiki =substr($choiceWiki,1);
	$choiceWiki=trim($choiceWiki);
	$parsedChoice=$wgParser->parse($choiceWiki,$wgTitle, $wgOut->ParserOptions(), false ,false);
	$choice=$parsedChoice->getText();
	if($choice!="")
          {
	    $ci=chr(65+$choiceIndex);
	    $choiceIndex++;

	    if($wgRequest->getVal('useskin')!='mobileskin')
	    {
	      $output.="<tr><td><ul><li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$choiceIndex.jpg)\"><label>$ci. $choice</label></li></ul></td></tr>";
	      }
	    else//use mobile skin
	    {
	      $output.="<tr><td colspan=2><ul><li STYLE=\"list-style-image: url(./utkgraph/ChoiceColor/Choice$choiceIndex.jpg)\"><label>$ci. $choice</label><br /></tr>";
	      	}
	     }
	 }

   }//end foreach surveyID as $sid


//---get the top list Exposition---
//--------------------------------------

//---get Exposition data from database---

$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
//SQL query
  $pQuery = "SELECT presentationID, presentation, marks FROM view_presentation_page_mark where pageID=$pageID and marks is not NULL ORDER BY marks DESC";
  $numPQuery = "SELECT count(*) as numpres FROM view_presentation_page_mark where pageID=$pageID and marks is not NULL";
  $topQuery="Select displayTop from page where pageID=$pageID";

//execute query
  $pqueryexe = odbc_do($connectionstring, $pQuery);
  $numpqueryexe = odbc_do($connectionstring, $numPQuery);
  $topqueryexe = odbc_do($connectionstring, $topQuery);

  while(odbc_fetch_row($numpqueryexe))
   {
     $numpres= odbc_result($numpqueryexe,'numpres');
    }
  while(odbc_fetch_row($topqueryexe))
   {
     $Top= odbc_result($topqueryexe,'displayTop');
    }

$output.="<tr><td><fieldset>";
//$output.="<legend><H3>Top $Top Exposition</H3></legend><br>";

if ($numpres==0)
 {
   $output.="<legend><H3>Top Exposition</H3></legend><br>";
   $runP='No Exposition with votes';
   $output.= "<label><strong> $runP </strong></label>";
   $output.="</fieldset></tr></td>";
  }
else
 {
  $runP=array();
  $runM=array();
  $i=1;
  while(odbc_fetch_row($pqueryexe))
    {
       $runExposition = odbc_result($pqueryexe, 'presentation');
       $runMark = odbc_result($pqueryexe, 'marks');
       $runExposition = urldecode ($runExposition);
       $runP[$i] = $runExposition;
       $runM[$i] = $runMark;
       $i++;
      }

$a=$i-1;
  if ($i>$Top)
   {
     $output.="<legend><H3>Top $Top Exposition</H3></legend>";
     $j=1;
     while ($j<=$Top)
     {
       $output.= "<label><ul><li> $runP[$j] </ul></label>";
       $j++;
      }
    }
  else
    {
     $output.="<legend><H3>Top $a Exposition</H3></legend>";
     $j=1;
     while ($j<=$a)
     {
       $output.= "<label><ul><li> $runP[$j] </ul></label>";
       $j++;
      }
    }
  $output.="</fieldset></tr></td>";
 }


$output.='</table></form>';

} //end of ended status

//disconnect from database
   odbc_close($connectionstring);
return $output;
}
?>