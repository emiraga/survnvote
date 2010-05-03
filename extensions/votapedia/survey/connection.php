<?php
if (!defined('MEDIAWIKI')) die();
/**
 * This page is used to connect database.
 *
 * @package DAO of Survey
 */

include_once("adodb/adodb.inc.php");
include_once("adodb/adodb-exceptions.inc.php");
/**
 * Connect database without parameters
 * @return $cn ADOConnection
 */
function connectDatabase()
{
	global $gvDBserver, $gvDBUserName, $gvDBUserPassword, $gvDataSourceName;

	$cn = &ADONewConnection('mysqli');
	if (!$cn->Connect($gvDBserver, $gvDBUserName, $gvDBUserPassword, $gvDataSourceName))
		throw new SurveyException("Could not connect database",400);
	return $cn;
}

$gDB = connectDatabase();
#$gDB->debug=true;

?>