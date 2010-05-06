<?php
/**
 * Types of surveys.
 */
define('vSIMPLE_SURVEY',   1);
define('vQUIZ',            2);
define('vRANK_EXPOSITIONS',3);
define('vQUESTIONNAIRE',   4);
define('vTEXT_RESPONSE',   5);
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
	global $gvDBType, $gvDBserver, $gvDBUserName, $gvDBUserPassword, $gvDBName;

	$cn = &ADONewConnection($gvDBType);
	if (!$cn->Connect($gvDBserver, $gvDBUserName, $gvDBUserPassword, $gvDBName))
		throw new SurveyException("Could not connect to database", 400);
	return $cn;
}
/**
 * Global variable - ADOdb
 * @var $gvDB global variable ADOdb connection
 */
global $gvDB;
$gvDB = vfConnectDatabase();
/**
 * Rotates color images for a choice.
 * 
 * @return a path to image
 */
function vfGetColorImage()
{
	static $c = 0;
	$c = ($c + 1) % 50;
	global $gvScript;
	return "$gvScript/images/colors/Choice$c.jpg";
}
/**
 * Purge the cache of a page with a given title
 * 
 * @param $title string title of wiki page
 */
function vfPurgePage($title)
{
	$params = new FauxRequest(array('action' => 'purge','titles' => $title));
	$api = new ApiMain($params, true);
	$api->execute();
	$data = & $api->getResultData();
	if(!isset($data['purge'][0]['purged']))
		throw new Exception('Page purging has failed');
}
?>