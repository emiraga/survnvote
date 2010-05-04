<?php
/**
 * Convert page title into a friendly form, shorter and trimmed
 * 
 * @param $mytitle
 */
function vfGetPageTitle($mytitle)
{
	$mytitle = trim(stripslashes($mytitle));
	if(strlen($mytitle)>50)
	{
		$mytitle=substr($mytitle,0,50);
		$mytitle.='...';
	}
	return $mytitle;
}
/**
 * Return a message in error box, will show as red in HTML
 * 
 * @param $message
 */
function vfErrorBox($message)
{
	return '<div class="errorbox" style="margin-bottom: 0.5em;"><strong>'.$message.'</strong></div><div class="visualClear"></div>';
}
/**
 * Return a message in error box, will show as red in HTML
 * 
 * @param $message
 */
function vfSuccessBox($message)
{
	return '<div class="successbox" style="margin-bottom: 0em;"><strong>'.$message.'</strong></div><div class="visualClear"></div>';
}
/**
 * Custom Exception class for surveys
 * 
 * @author Emir Habul
 *
 */
class SurveyException extends Exception
{
	//
}
/**
 * This is used to connect database.
 */
include_once("adodb/adodb.inc.php");
include_once("adodb/adodb-exceptions.inc.php");
/**
 * Connect database without parameters
 * @return $cn ADOConnection
 */
function vfConnectDatabase()
{
	global $gvDBserver, $gvDBUserName, $gvDBUserPassword, $gvDataSourceName;

	$cn = &ADONewConnection('mysqli');
	if (!$cn->Connect($gvDBserver, $gvDBUserName, $gvDBUserPassword, $gvDataSourceName))
		throw new SurveyException("Could not connect database", 400);
	return $cn;
}
/**
 * Global variable - ADOdb
 * @var $gDB global variable ADOdb connection
 */
$gDB = vfConnectDatabase();
/**
 * Rotates color images for a choice.
 * @return a path to image
 */
function vfGetColorImage()
{
	static $c = 0;
	$c = ($c + 1) % 50;
	global $gvScript;
	return "$gvScript/images/colors/Choice$c.jpg";
}
?>