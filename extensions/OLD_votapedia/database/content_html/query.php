<?php
//This file is used to create query result for the database schema page.
$sql = $_GET["SQL"];
$view = $_GET["VIEW"];
require_once("../SurveySettings.php");
global $gDBUserName;
global $gDBUserPassword;
$connectionstring = odbc_connect("MobileServer",  $gDBUserName, $gDBUserPassword);
//SQL query
$Query = stripslashes($sql);

if($view=='table'){
echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;" />
<title>Interacting With The Database</title>
</head>
<body>
<h2>Query Results</h2>
');
echo("<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2 bordercolor=#aaaaaa style=\"margin:10px 10px 10px 40px\"><TR><th>Caller</th><th>Receiver</th><th>Time of Call</th></TR>");

//execute the query
$queryexe = odbc_do($connectionstring, $Query);
//fetch the results
while(odbc_fetch_row($queryexe))
{
	//collect results
	$caller = odbc_result($queryexe, 'caller');
	$receiver = odbc_result($queryexe, 'receiver');
	$time = odbc_result($queryexe, 'calldate');

	//format and display results
	echo("<tr><td>$caller</td><td>$receiver</td><td>$time</td></tr>");
}
echo('</table>');

echo ('</body></html>');
}else if($view=='xml'){
	// We'll be outputting a xml
	header('Content-type: text/xml');

	echo('<?xml version="1.0" encoding="utf-8"?>');

	echo('<root>');
	//execute the query
	$queryexe = odbc_do($connectionstring, $Query);
	//fetch the results
	while(odbc_fetch_row($queryexe))
	{
		//collect results
		$caller = odbc_result($queryexe, 1);
		$receiver = odbc_result($queryexe, 2);
		$time = odbc_result($queryexe, 3);

		//format and display results
		echo("<row Caller=\"$caller\" Receiver=\"$receiver\" Time_of_Call=\"$time\" />");
	}
	echo("</root>");
}
//disconnect from database
odbc_close($connectionstring);
?>