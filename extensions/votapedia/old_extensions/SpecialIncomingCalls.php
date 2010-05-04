<?php
old_stuff();
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpIncomingCalls";
require_once( "$IP/includes/SpecialPage.php" );
require_once("$IP/SurveySettings.php");

function wfExtensionSpIncomingCalls() {
    global $wgMessageCache;

	SpecialPage::addPage( new SpIncomingCallsPage );
	$wgMessageCache->addMessages(array('incomingcalls' => 'Incoming Calls'));
}

//Generate a SQL query to get the number of incoming calls in the last $n days
function makeChartSQL($n){
	$sql="";
	$i=0;
	while ($i<$n+1) {
		$nDaysAgo=date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$n+$i, date("Y")));
		$nMinusOneDaysAgo=date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$n+$i+1, date("Y")));
		$selectStart = "SELECT '$nDaysAgo' as Date,Count(*) AS AMOUNT FROM loggedcall  WHERE calldate >='$nDaysAgo' and calldate <'$nMinusOneDaysAgo'";
		if($i>0) $sql=$sql." UNION ALL ";

		$sql= $sql. $selectStart;

		$i++;
	}
	return $sql;
}

class SpIncomingCallsPage extends SpecialPage {
	function SpIncomingCallsPage() {
		SpecialPage::SpecialPage( 'IncomingCalls' );
		$this->includable( true );
	}

	function execute( $par = null ) {
		global $wgOut;
		global $gDataSourceName;
		global $gDBUserName;
		global $gDBUserPassword;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Incoming Calls");
		$wgOut->addHTML('<h3>Instructions</h3>
		<ol><li>If service is available, with a mobile phone, call 0262167123 from anywhere in Australia.</li>
		<li>Let the phone ring once or twice then hang up.</li>
		<li>Within a few minutes, you should receive a text message saying "Thankyou for calling"</li></ol>
		<h3>Last 20 calls</h3>
		');
		$connectionstring = odbc_connect($gDataSourceName,  $gDBUserName, $gDBUserPassword);
		
		//SQL query
		$recentday= date("Y-m-d H:i:s",mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));
		$Query = "SELECT * FROM loggedcall WHERE calldate >= '$recentday' ORDER BY calldate DESC LIMIT 20";

		//execute query
		$queryexe = odbc_do($connectionstring, $Query);

		$wgOut->addHTML("<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#aaaaaa style=\"margin:10px 10px 10px 40px\"><TR><th>Caller</th><th>Receiver</th><th>Time of Call</th></TR>");
		//query database
		while(odbc_fetch_row($queryexe))
		{
			//collect results
			$caller = odbc_result($queryexe, 'caller');
			$receiver = odbc_result($queryexe, 'receiver');
			$time = odbc_result($queryexe, 'calldate');

			//format and display results
			$wgOut->addHTML("<tr><td>$caller</td><td>$receiver</td><td>$time</td></tr>");
		}
		$wgOut->addHTML('</table>');

		//disconnect from database
		odbc_close($connectionstring);

		$recentday= date("Y-m-d H:i:s",mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));
		//the query constructor
		$wgOut->addHTML('
  <H3>View Logged Calls</H3>
  <TABLE BGCOLOR="#3399CC">
  <TR><TD>
  <TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#9dceff style="margin:10px 10px 10px 40px">
    <FORM ACTION="'.$site_location.'/database/query.php?" METHOD="get">
    	<TR>
      		<TH><FONT FACE="Verdana, Arial, Helvetica">Structured Query Language Request (SQL)</FONT></TH>
      		<TH><FONT FACE="Verdana, Arial, Helvetica">View Options</FONT></TH>
    	</TR>
		<TR>
    	  	<TD><TEXTAREA NAME="SQL" COLS="10" ROWS="3">SELECT * FROM loggedCall WHERE calldate >= '."'$recentday'".' ORDER BY calldate DESC</TEXTAREA><p><INPUT TYPE="Submit" VALUE="Submit SQL" /></p>
    	  	</TD>
    	  	<TD><INPUT TYPE="radio" NAME="VIEW" VALUE="table" CHECKED="1"/>Table<br/><INPUT TYPE="radio" NAME="VIEW" VALUE="xml" />XML
			</TD>
		</TR>
		<INPUT TYPE="Hidden" NAME="ROOT" VALUE="root" />
	</FORM>
  </TABLE>
  </TD></TR>
  </TABLE>');

  		$chartSQL=makeChartSQL(7);
  		$chartURL="$site_location/database/owc_chart.asp?SQL=".urlencode($chartSQL);
  		$wgOut->addHTML('<script language="JavaScript">');
  		//$wgOut->addHTML('<!--');
  		$wgOut->addHTML("function updateChart(){var url='$site_location/database/owc_chart.asp?SQL='+URLencode(document.getElementById(\"chartTextArea\").value);document.getElementById(\"callChart\").src=url;}function URLencode(str) {var result = \"\";for (i = 0; i < str.length; i++) {if (str.charAt(i) == \" \") result +=\"+\";else result += str.charAt(i);}return escape(result);}");
  		//$wgOut->addHTML('-->');
  		$wgOut->addHTML('</script>');
$wgOut->addHTML("<H3>Amount of incoming calls</H3><img id=\"callChart\" style=\"margin:10px 10px 10px 40px\" src=$chartURL /><p style=\"margin:10px 10px 10px 40px\"><textarea id=\"chartTextArea\" cols=10 rows=5>$chartSQL</textarea><p style=\"margin:10px 10px 10px 40px\"><input type=button name=btnUpdateChart value=\"Update Chart\" onclick='updateChart()'></p></p>
");
  		$wgOut->addWikiText("    Notice: Don't plot too much data.");
	}//end function execute
}//end class SpIncomingCallsPage

?>