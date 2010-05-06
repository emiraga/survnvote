<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpActiveSurveys";

require_once("$IP/SurveySettings.php");

function wfExtensionSpActiveSurveys() {
	global $IP, $wgMessageCache;
	require_once( "$IP/includes/SpecialPage.php" );

	$wgMessageCache->addMessages(array('ActiveSurveys' => 'ActiveSurveys'));

class SpActiveSurveysPage extends SpecialPage {
	function SpActiveSurveysPage() {
		SpecialPage::SpecialPage( 'ActiveSurveys' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		global $gDataSourceName;
		global $gDBUserName;
		global $gDBUserPassword;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Active Surveys");
		$userName=$wgUser->getName();

		$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);

		//SQL query
		$Query = "SELECT * FROM page WHERE author<>'Test' and StartTime < now() and endTime > now() ORDER BY CreateTime DESC";

		//execute query
		$queryexe = odbc_do($connectionstring, $Query);

		$wgOut->addHTML("<TABLE CELLSPACING=0 CELLPADDING=5 style=\"margin:5px 5px 5px 20px;background-color:#fcfcfc\"><TR><th style=\"border:1px solid #ccc\">Title</th><th style=\"border-top:1px solid #ccc;border-bottom:1px solid #ccc;border-right:1px solid #ccc\">Author</th><th style=\"border-top:1px solid #ccc;border-bottom:1px solid #ccc\">Number of Votes</th><th style=\"border-top:1px solid #ccc;border-left:1px solid #ccc;border-bottom:1px solid #ccc\">Type</th><th style=\"border:1px solid #ccc\">End Time</th></TR>");
		//query database
		$resultIsNull=TRUE;
		while(odbc_fetch_row($queryexe))
		{
			$resultIsNull=FALSE;
			//collect results
			$pageID = odbc_result($queryexe, 'pageID');
			$title = odbc_result($queryexe, 'title');
			$decodedTitle=urldecode($title);
			$author = odbc_result($queryexe, 'author');
			$endTime = odbc_result($queryexe, 'endTime');
			$type = odbc_result($queryexe, 'surveyType');
			
			$typeStr='';
			switch($type)
			{
			case 1:
				$typeStr='<a href="index.php?title=Category:Simple Surveys">Simple Survey</a>';
				break;
			case 2:
				$typeStr='<a href="index.php?title=Category:Quizs">Quiz</a>';
				break;
			case 3:
				$typeStr='<a href="index.php?title=Category:Rank Expositions">Rank Expositions</a>';
				break;
			case 4:
				$typeStr='<a href="index.php?title=Category:Questionnaires">Questionnaire</a>';
				break;
			case 5:
				$typeStr='<a href="index.php?title=Category:Text Responses">Text Response</a>';
				break;
			}

			$Query2 = "SELECT * FROM survey WHERE pageID = $pageID";
			$queryexe2 = odbc_do($connectionstring, $Query2);
			$numVotes=0;
			while(odbc_fetch_row($queryexe2))
			{
				$surveyID = odbc_result($queryexe2, 'surveyID');
				$Query3 = "SELECT * FROM surveyChoice WHERE surveyID = $surveyID";
				$queryexe3 = odbc_do($connectionstring, $Query3);

				while(odbc_fetch_row($queryexe3))
				{
					$v = odbc_result($queryexe3, 'vote');
					$numVotes+=$v;
				}
			}

			$endTimeStamp=strtotime($endTime);
			//format and display results
			$wgOut->addHTML("<tr><td style=\"border-left:1px solid #ccc;border-right:1px solid #ccc;border-bottom:1px solid #ccc\"><a href=\"index.php?title=$title\">$decodedTitle</a></td><td style=\"border-bottom:1px solid #ccc;border-right:1px solid #ccc\"><a href=\"index.php?title=Category:Surveys_by_$author\">$author</a></td><td align=\"Center\" style=\"border-bottom:1px solid #ccc\">$numVotes</td><td align=\"Center\" style=\"border-bottom:1px solid #ccc;border-left:1px solid #ccc\">$typeStr</td><td style=\"border-left:1px solid #ccc;border-right:1px solid #ccc;border-bottom:1px solid #ccc\"><script>var d=new Date(); d.setTime($endTimeStamp*1000);document.write(d.toLocaleString());</script></td></tr>");
		}
		if($resultIsNull) $wgOut->addHTML("<tr><td style=\"border-left:1px solid #ccc;border-right:1px solid #ccc;border-bottom:1px solid #ccc\" colspan=4 align=center>No survey is currently active.</td></tr>");
		$wgOut->addHTML('</table>');

		//disconnect from database
		odbc_close($connectionstring);

	}//end function execute
}//end class SpActiveSurveysPage

SpecialPage::addPage( new SpActiveSurveysPage );
}
?>