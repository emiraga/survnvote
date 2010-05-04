<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpCombineQuizs";

require_once("$IP/SurveySettings.php");

function wfExtensionSpCombineQuizs() {
        global $IP, $wgMessageCache;
        require_once( "$IP/includes/SpecialPage.php" );

// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
// 'allpages' => 'All Pages';
// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('combinequizs' => 'CombineQuizs'));

class SpCombineQuizsPage extends SpecialPage {
	function SpCombineQuizsPage() {
		SpecialPage::SpecialPage( 'CombineQuizs' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		global $gDataSourceName;
		global $gDBUserName;
		global $gDBUserPassword;
		$wgOut->setPageTitle("Combine Quiz Result");
		$wgOut->setArticleFlag(false);
		$userName=$wgUser->getName();
		

		if(!$wgUser->isLoggedIn())
		{
			$wgOut->addHTML('<pre>   Notice: You have to login to combine your quizs.</pre>');
			global $wgRequest;
			require_once( "SpecialUserlogin.php" );
			$form = new LoginForm( $wgRequest );
			$form->execute();
			return;
		}
		
		if(!$wgUser->isLoggedIn())
			$userName="NULL";
		
		$selectedQuizs = array();
		if(isset($_GET["numQuizs"]))
		{
			$numQuizs=$_GET["numQuizs"];
			for($i=0;$i<$numQuizs;$i++)
			{
				if(isset($_GET["selectedQuiz$i"]))
				{
					$selectedQuizs[] = $_GET["selectedQuiz$i"];
				}
			}
		}
				
		$numSelectedQuizs=count($selectedQuizs);
		if($numSelectedQuizs<2)
			$wgOut->addHTML('<p style="font-size: large">Please select two or more quizs to combine their results.</p>');
		
		$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);
		
		$Query = "SELECT * FROM page WHERE ";
		$Where='';
		$loop=0;
		$pageIDs='';//the md5 of all the pageIDs will become the filename for histogram
		foreach($selectedQuizs as $quiz)
		{
			if($loop!=0)
				$Where.="OR ";
			$Where .="pageID = $quiz ";
			$pageIDs.="|$quiz|";
			$loop++;
		}
		$Query.=$Where;
		
		//execute query
		//echo $Query;
		$queryexe = odbc_do($connectionstring, $Query);

		//query database
		$wgOut->addHTML('<p style="font-size: large"><strong>The result is a combination of the following surveys:</strong></p>');
		$i=1;
		$wgOut->addHTML('<p style="font-size: large">');
		$endTimes='';
		while(odbc_fetch_row($queryexe))
		{
			$encodedTitle = odbc_result($queryexe, 'title');
			$title = urldecode($encodedTitle);
			$endTimes.= odbc_result($queryexe, 'endTime');
			$wgOut->addHTML("&nbsp;&nbsp;$i.<a href=\"index.php?title=$encodedTitle\">$title</a><br />");
			$i++;
		}
		$wgOut->addHTML('</p>');
		
		//calculate total points
		$Query = "SELECT * FROM view_quiz WHERE $Where";
		$queryexe = odbc_do($connectionstring, $Query);
		$totalPoints=0;
		while(odbc_fetch_row($queryexe))
		{
			$totalPoints += odbc_result($queryexe, 'points');
		}
		
		//$wgOut->addHTML('<p>NumQuizs='.$numSelectedQuizs.'</p>');
		$action='';
		if(isset($_GET['submit']))
			$action = $_GET['submit'];
		$displayRealName=false;
		if(isset($_GET['realname']))
			if($_GET['realname']=='yes')
				$displayRealName=true;
		if($action=='Combine Results of Selected Quizs')
		{
			$wgOut->addHTML( '<table><tr><td><form action="./database/sendQuizResultSMS.php?" method="post"><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#cbdced style="font-size: large"><TR><th>SMS</th><th>User Name</th>');
			$wgOut->addHTML( "<INPUT TYPE=\"Hidden\" NAME=\"page\" VALUE=\"$pageIDs\" />");
			$wgOut->addHTML( "<INPUT TYPE=\"Hidden\" NAME=\"totalpoint\" VALUE=\"$totalPoints\" />");
			$wgOut->addHTML( "<INPUT TYPE=\"Hidden\" NAME=\"sender\" VALUE=\"$userName\" />");
			if($displayRealName)
				$wgOut->addHTML( '<th>Real Name</th>');
			$wgOut->addHTML( '<th>Total Mark</th></TR>');
			$record=array();
			$realNames=array();
			$phones=array();
			foreach ($selectedQuizs as $quiz)
			{
				//SQL query
				$Query = "SELECT * FROM view_quiz_result WHERE pageID = '$quiz'";
		
				//execute query
				$queryexe = odbc_do($connectionstring, $Query);
		
				//query database
				while(odbc_fetch_row($queryexe))
				{
					$user = odbc_result($queryexe, 'voterid');
					$mark = odbc_result($queryexe, 'marks');
					$phone = odbc_result($queryexe, 'phone');
					if($displayRealName)
						$realName = odbc_result($queryexe, 'realname'); 
					if($user==NULL)
						$user = $phone;
					if( !isset($record["$user"]) )
						$record["$user"]=0;
					$record["$user"]+=$mark;
					$phones["$user"]=$phone;
					if($displayRealName)
					{
						if( !isset($realNames["$user"]) )
							$realNames["$user"]='';
						$realNames["$user"]=$realName;
					}
				}
				
			}//end foreach
			arsort($record);
			foreach($record as $u=>$m)
			{
				$phone=$phones["$u"];
				$Query2 = "SELECT * FROM quizresultsms WHERE pageID = '$pageIDs' AND mobile = '$phone'";
				//echo $Query2;
				$queryexe2 = odbc_do($connectionstring, $Query2);
				if(odbc_fetch_row($queryexe2))
				{
					$wgOut->addHTML("<td><span style=\"color:#128a12\">SMS Sent</span></td><TD>$u</TD>");
				}
				else
				{
					$wgOut->addHTML("<td><INPUT TYPE=\"checkbox\" NAME=\"sendsms[]\" VALUE=\"$u\" /></td><TD>$u</TD>");
				}
				if($displayRealName)
				{
					$r=$realNames["$u"];
					$wgOut->addHTML("<TD>$r</TD>");
				}
				$wgOut->addHTML("<TD>$m</TD></TR>");
			}
			$wgOut->addHTML('</TABLE>');
			
			//disconnect from database
			odbc_close($connectionstring);
			$wgOut->addHTML('<p style="margin:10px 10px 10px 10px"><input type="submit" name="Submit" value="Send result by SMS" /></form>');
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
			$wgOut->addHTML("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$url\">$text</a></p></td>");
			
			//generate histogram
			$markCount = array();
			foreach ($record as $user=>$marks)
			{
				if(isset($markCount["$marks"]))
					$markCount["$marks"]+=1;
				else
					$markCount["$marks"]=1;
			}
			ksort($markCount);//sort by the mark
			require_once("./utkgraph/Quizhistogram.php");
			$url=generateQuizHistogram($markCount,$pageIDs,$endTimes);
			$wgOut->addHTML("<td><p>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"$url\" /></p></td></table>");
		}
	}//end function execute
}//end class SpCombineQuizsPage

SpecialPage::addPage( new SpCombineQuizsPage );
}
?>