<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpVoting";

require_once("$IP/SurveySettings.php");

function wfExtensionSpVoting() {
        global $IP, $wgMessageCache;
        require_once( "$IP/includes/SpecialPage.php" );

// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
// 'allpages' => 'All Pages';
// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('voting' => 'Voting'));

class SpVotingPage extends SpecialPage {
	function SpVotingPage() {
		SpecialPage::SpecialPage( 'Voting' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		global $gDBUserName;
		global $gDBUserPassword;
		global $gDataSourceName;
		$wgOut->setPageTitle("Voting Service");
		$userName=$wgUser->getName();

		if(!$wgUser->isLoggedIn())
		{
			$wgOut->addHTML('<pre>   Notice: You have to login to check your surveys.</pre>');
			global $wgRequest;
			require_once( "SpecialUserlogin.php" );
			$form = new LoginForm( $wgRequest );
			$form->execute();
			return;
		}
		
		if(!$wgUser->isLoggedIn())
			$userName="NULL";

		$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);

		//SQL query
		$Query = "SELECT * FROM page WHERE author = '$userName' ORDER BY CreateTime DESC";

		//execute query
		$queryexe = odbc_do($connectionstring, $Query);

		//query database
		$resultIsNull=TRUE;
		$pageIDs=array();
		$encodedTitles=array();
		$titles=array();
		$pageStatus=array();
		$surveyType=array();
		$creationTimeStamps=array();
		$now=date("Y-m-d H:i:s");
		$initDate= date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
		while(odbc_fetch_row($queryexe))
		{
			$resultIsNull=FALSE;
			//collect results
			$pageIDs[] = odbc_result($queryexe, 'pageID');
			$encodedTitle = odbc_result($queryexe, 'title');
			$encodedTitles[]=$encodedTitle;
			$titles[]=urldecode($encodedTitle);
			$surveyType[] = odbc_result($queryexe, 'surveyType');
			$creationTime = odbc_result($queryexe, 'createTime');
			$creationTimeStamps[] = strtotime($creationTime);
			$startTime = odbc_result($queryexe, 'startTime');
			$endTime = odbc_result($queryexe, 'endTime');

			if ($startTime - $initDate==0)
				$pageStatus[] = 'ready';
			else if ($endTime>$now)
				$pageStatus[] = 'active';
			else if ($endTime< $now)
				$pageStatus[] = 'ended';
		}
		$numSurveys=count($pageIDs);
		//group a survey by another survey
		$wgOut->addHTML('<fieldset><legend>Relationship of two surveys:</legend><span style="color:#999999">This tool allows you to group the result of one survey by another survey. You can specify two surveys in the textfield below and create a stacked bar chart. In the chart, each user group in one survey is displayed as a fraction (or percentage) of another survey. </span>
<form ACTION="./database/groupSurveys.php?" METHOD="GET"><p>Group <select name="survey2">');
  		for ( $i=0;$i<$numSurveys;$i++)
		{
			$encodedTitle=$encodedTitles[$i];
			$title=$titles[$i];
			//format and display results
			if($pageStatus[$i] == 'ended')
				$wgOut->addHTML("<option value=\"$encodedTitle\">$title</option>");
		}

  		$wgOut->addHTML('</select> by <select name="survey1">');
		for ( $i=0;$i<$numSurveys;$i++)
		{
			$encodedTitle=$encodedTitles[$i];
			$title=$titles[$i];
			//format and display results
			if($pageStatus[$i] == 'ended')
				$wgOut->addHTML("<option value=\"$encodedTitle\">$title</option>");
		}
		$wgOut->addHTML('</select><input name="submit" type="submit" value="Group surveys" /></p></form></fieldset>');

		//combine quiz results
		$wgOut->addHTML('<fieldset><legend>Combine quiz results:</legend><span style="color:#999999">This tool allows you to combine the result of several quizs. </span>
<form ACTION="index.php" METHOD="GET"><input type="hidden" name="title" value="Special:CombineQuizs" /><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#cbdced ><TR><th>Title</th><th>Status</th><th>Participants</th></TR>');
		$resultIsNull=true;
		$numQuizs=0;
  		for ( $i=0;$i<$numSurveys;$i++)
		{
			$encodedTitle=$encodedTitles[$i];
			$title=$titles[$i];
			$pageID=$pageIDs[$i];
			$pageState=$pageStatus[$i];
			$type=$surveyType[$i];
			if($type==2)
			{
				$numParticipants=0;
				if($pageStatus!='ready')
				{
					$Query3 = "SELECT * FROM view_quiz_result WHERE pageid = $pageID";
					$queryexe3 = odbc_do($connectionstring, $Query3);
						
					$numParticipants=odbc_num_rows($queryexe3);
				}
				
				$wgOut->addHTML("<tr><td><input type=\"checkbox\" value=\"$pageID\" name=\"selectedQuiz$numQuizs\"><a href=\"index.php?title=$title\">  $title</a></td><td align=\"Center\">$pageState</td><td align=\"Center\">$numParticipants</td></tr>");
				$numQuizs++;
				$resultIsNull=false;
			}
		}
		if($resultIsNull) $wgOut->addHTML("<tr><td colspan=7 align=center>No quiz is available.</td></tr>");
		$wgOut->addHTML("</table><input type=\"hidden\" name=\"numQuizs\" value=\"$numQuizs\" />");

		$wgOut->addHTML('<p><input name="submit" type="submit" value="Combine Results of Selected Quizs" /></p></form></fieldset>');

		$wgOut->addHTML("<h2>$userName's surveys</h2>");
		$wgOut->addHTML("<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#cbdced><TR><th>Title or Survey Question</th><th>Time of Creation</th></TR>");

		for ( $i=0;$i<$numSurveys;$i++)
		{
			$encodedTitle=$encodedTitles[$i];
			$title=$titles[$i];
			$creationTimeStamp=$creationTimeStamps[$i];
			//format and display results
			$wgOut->addHTML("<tr><td><a href=\"./index.php?title=$encodedTitle\">$title</a></td><td><script>var d=new Date(); d.setTime($creationTimeStamp*1000);document.write(d.toLocaleString());</script></td></tr>");
		}

		if($resultIsNull) $wgOut->addHTML("<tr><td>No survey was created.</td><td>&nbsp;</td></tr>");
		$wgOut->addHTML('</table>');

		//disconnect from database
		odbc_close($connectionstring);

	}//end function execute
}//end class SpVotingPage

SpecialPage::addPage( new SpVotingPage );
}
?>