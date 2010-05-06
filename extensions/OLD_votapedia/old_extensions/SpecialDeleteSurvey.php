<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpDeleteSurvey";

require_once("./SurveySettings.php");

function wfExtensionSpDeleteSurvey() {
        global $IP, $wgMessageCache;
        require_once( "$IP/includes/SpecialPage.php" );

// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
// 'allpages' => 'All Pages';
// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('DeleteSurvey' => 'DeleteSurvey'));

class SpDeleteSurveyPage extends SpecialPage {
	function SpDeleteSurveyPage() {
		SpecialPage::SpecialPage( 'DeleteSurvey' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $wgUser;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Delete Surveys");
		$userName=$wgUser->getName();

		# Check permissions
		if( ( !$wgUser->isAllowed( 'delete' ) ) ) {
			$wgOut->sysopRequired();
			return;
		}
		global $gDataSourceName;
		global $gDBUserName;
		global $gDBUserPassword;
		$connectionstring = odbc_connect($gDataSourceName, $gDBUserName, $gDBUserPassword);

		//SQL query
		$Query = "SELECT * FROM page ORDER BY CreateTime DESC";

		//execute query
		$queryexe = odbc_do($connectionstring, $Query);

		$wgOut->addHTML("<h3>All surveys</h3><FORM ACTION=\"/database/deleteSurvey.php?\" METHOD=\"post\"><p><INPUT TYPE=\"Submit\" VALUE=\"Delete Selected Surveys\" NAME=\"submit\"/></p><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#cbdced ><TR><th>Title</th><th>Author</th><th>Status</th><th>Number of Votes</th><th>Creation Time</th><th>Start Time</th><th>End Time</th></TR>");
		//query database
		$resultIsNull=TRUE;
		$initDate= date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
		$now=date("Y-m-d H:i:s");
		while(odbc_fetch_row($queryexe))
		{
			$resultIsNull=FALSE;
			//collect results
			$pageStatus='ended';

			$pageID = odbc_result($queryexe, 'pageID');
			$encodedTitle = odbc_result($queryexe, 'title');
			$title=urldecode($encodedTitle);
			$author = odbc_result($queryexe, 'author');
			$createTime = odbc_result($queryexe, 'createTime');
			$startTime = odbc_result($queryexe, 'startTime');
			$endTime = odbc_result($queryexe, 'endTime');

			if ($startTime - $initDate==0)
				$pageStatus = 'ready';
			else if ($endTime>$now)
				$pageStatus = 'active';
			else if ($endTime< $now)
				$pageStatus = 'ended';

			$numVotes=0;
			if($pageStatus!='ready')
			{
				$Query2 = "SELECT * FROM survey WHERE pageID = $pageID";
				$queryexe2 = odbc_do($connectionstring, $Query2);
				
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
			}
			$createTimeStamp=strtotime($createTime);
			$startTimeStamp=strtotime($startTime);
			$endTimeStamp=strtotime($endTime);
			//format and display results
			$wgOut->addHTML("<tr><td><input type=\"checkbox\" value=\"$encodedTitle\" name=\"selectedSurveys[]\"><a href=\"index.php?title=$title\">  $title</a></td><td>$author</td><td align=\"Center\">$pageStatus</td><td align=\"Center\">$numVotes</td><td><script>var d=new Date(); d.setTime($createTimeStamp*1000);document.write(d.toLocaleString());</script></td><td><script>var d=new Date(); d.setTime($startTimeStamp*1000);document.write(d.toLocaleString());</script></td><td><script>var d=new Date(); d.setTime($endTimeStamp*1000);document.write(d.toLocaleString());</script></td></tr>");
		}
		if($resultIsNull) $wgOut->addHTML("<tr><td colspan=7 align=center>No survey is available.</td></tr>");
		$wgOut->addHTML('</table><p><INPUT TYPE="Submit" VALUE="Delete Selected Surveys" NAME="submit"/></p></form>');

		//disconnect from database
		odbc_close($connectionstring);
	}//end function execute
}//end class SpDeleteSurveyPage

SpecialPage::addPage( new SpDeleteSurveyPage );
}
?>