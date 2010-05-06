<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpRecentCalls";
require_once( "$IP/includes/SpecialPage.php" );
require_once("$IP/SurveySettings.php");

function wfExtensionSpRecentCalls() {
    global $wgMessageCache;

	SpecialPage::addPage( new SpRecentCallsPage );
	$wgMessageCache->addMessages(array('recentcalls' => 'Recent Calls'));
}

class SpRecentCallsPage extends SpecialPage {
	function SpRecentCallsPage() {
		SpecialPage::SpecialPage( 'RecentCalls' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $gDataSourceName;
		global $gDBUserName;
		global $gDBUserPassword;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Recent Calls");
		$connectionstring = odbc_connect($gDataSourceName,  $gDBUserName, $gDBUserPassword);
		
		//SQL query
		$recentday= date("Y-m-d H:i:s",mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));
		$Query = "SELECT * FROM view_recent_call WHERE voteDate >= '$recentday' ORDER BY voteDate DESC LIMIT 5";

		//execute query
		$queryexe = odbc_do($connectionstring, $Query);
		//the background should be #f5faff
		$wgOut->addHTML("<TABLE CELLSPACING=0 CELLPADDING=5 style=\"margin:5px 5px 5px 5px;background-color:#fcfcfc\"><TR><th style=\"border:1px solid #ccc\">Caller</th><th style=\"border-top:1px solid #ccc;border-bottom:1px solid #ccc\">Survey</th><th style=\"border:1px solid #ccc\">Time</th></TR>");
		//query database
		while(odbc_fetch_row($queryexe))
		{
			//collect results
			$caller = odbc_result($queryexe, 'voterID');
			$title = odbc_result($queryexe, 'title');
			$time = odbc_result($queryexe, 'voteDate');
			$displayCaller=substr($caller,0,strlen($caller)-2).'**';
			$displayTitle=urldecode($title);
			$timeStamp=strtotime($time);
			$displayTime=date("H:i:s", $timeStamp);
			//format and display results
			$wgOut->addHTML("<tr><td style=\"border-left:1px solid #ccc;border-right:1px solid #ccc;border-bottom:1px solid #ccc\">$displayCaller</td><td style=\"border-bottom:1px solid #ccc\"><a href=\"index.php?title=$title\">$displayTitle</a></td><td style=\"border-left:1px solid #ccc;border-right:1px solid #ccc;border-bottom:1px solid #ccc\">$displayTime</td></tr>");//<script>var d=new Date(); d.setTime($timeStamp*1000);document.write(d.toLocaleString());</script>
		}
		$wgOut->addHTML('</table>');

		//disconnect from database
		odbc_close($connectionstring);
	}//end function execute
}//end class SpRecentCallsPage

?>