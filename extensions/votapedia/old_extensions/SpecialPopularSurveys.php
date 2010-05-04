<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpPopularSurveys";
require_once( "$IP/includes/SpecialPage.php" );
require_once("$IP/SurveySettings.php");

function wfExtensionSpPopularSurveys() {
    global $wgMessageCache;

	SpecialPage::addPage( new SpPopularSurveysPage );
	$wgMessageCache->addMessages(array('popularsurveys' => 'Popular Surveys'));
}

class SpPopularSurveysPage extends SpecialPage {
	function SpPopularSurveysPage() {
		SpecialPage::SpecialPage( 'PopularSurveys' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $gDataSourceName;
		global $gDBUserName;
		global $gDBUserPassword;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Popular Surveys");
		$connectionstring = odbc_connect($gDataSourceName,  $gDBUserName, $gDBUserPassword);
		
		//---SQL query---
		$Query = "SELECT title, sum( vote ) AS votes  FROM foruser.view_user2 GROUP BY title ORDER BY votes DESC LIMIT 5";

		//---execute query---
		$queryexe = odbc_do($connectionstring, $Query);
		//---the background---
		$wgOut->addHTML("<TABLE CELLSPACING=0 CELLPADDING=5 style=\"margin:5px 5px 5px 5px;background-color:#faf5ff\"><TR><th style=\"border:1px solid #ccc\">Title</th><th style=\"border-right:1px solid #ccc;border-top:1px solid #ccc;border-bottom:1px solid #ccc\">Total Votes</th></TR>");
		//query database
		while(odbc_fetch_row($queryexe))
		{
			//collect results
			$title = odbc_result($queryexe, 'title');
			$votes = odbc_result($queryexe, 'votes');
   
                        if (strlen($title)>150)
                         {
                            $displayTitle=mb_substr($title,0,142).'...';
                            $displayTitle=urldecode($displayTitle);
                           }
                        else
                            $displayTitle=urldecode($title);

			//format and display results

			$wgOut->addHTML("<tr><td style=\"border-left:1px solid #ccc;border-right:1px solid #ccc;border-bottom:1px solid #ccc\"><a href=\"index.php?title=$title\">$displayTitle</a></td><td style=\"border-left:0px solid #ccc;border-right:1px solid #ccc;border-bottom:1px solid #ccc\" align=\"Center\">$votes</td></tr>");
		}
		$wgOut->addHTML('</table>');

		//disconnect from database
		odbc_close($connectionstring);
	}//end function execute
}//end class SpPopularSurveysPage

?>