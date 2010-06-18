<?php
if(!defined('MEDIAWIKI')) die();
/**
 * @package VotapediaCommon
 */

/** Types of surveys. */
define('vSIMPLE_SURVEY',   1);
define('vQUIZ',            2);
define('vRANK_EXPOSITIONS',3);
define('vQUESTIONNAIRE',   4);
define('vTEXT_RESPONSE',   5);

/** Privacy levels. */
define('vPRIVACY_LOW',    1);
define('vPRIVACY_MEDIUM', 2);
define('vPRIVACY_HIGH',   3);

/** User phone status */
define('vPHONE_NEW', 0);
define('vPHONE_SENT_CODE', 1);
define('vPHONE_DELETED', 2);
define('vPHONE_VERIFIED', 10);
define('vPHONE_UNKNOWN', 11); //phone owner did not confirm (verify) his phone

/**
 * Return a current datetime formated in particular way.
 * 
 * @return String
 */
function vfDate($date = NULL)
{
    if($date)
        return date("Y-m-d H:i:s", $date);
    else
        return date("Y-m-d H:i:s");
}
/**
 * Convert page title into a friendly form, shorter and trimmed
 *
 * @param String $mytitle
 * @return String processed title
 */
function vfGetPageTitle($mytitle)
{
    $mytitle = trim(stripslashes($mytitle));
    if(strlen($mytitle)>50)
    {
        $mytitle=substr($mytitle,0,50).'...';
    }
    return $mytitle;
}
/**
 * Return a message in error box, will show as red in HTML
 *
 * @param String $message
 * @return String HTML code for error box.
 */
function vfErrorBox($message)
{
    return '<div class="errorbox" style="margin-bottom: 0.5em;"><strong>'.$message.'</strong></div><div class="visualClear"></div>';
}
/**
 * Return a message in error box, will show as red in HTML
 *
 * @param String $message
 * @return String HTML code
 */
function vfSuccessBox($message)
{
    return '<div class="successbox" style="margin-bottom: 0em;"><strong>'.$message.'</strong></div><div class="visualClear"></div>';
}
/**
 * Custom Exception class for surveys
 * @package VotapediaCommon
 */
class SurveyException extends Exception
{
    //
}
/**
 * Colors a survey choice.
 */
global $vgColors;
$vgColors = array('808080', 'FF6766', '669934', '669ACC', 'FFCC66', '986699', '008001',
    'FF6833', '882295', '006599', 'CD9933', '006766', '99CCCD', 'CC00CC', 'BBBB9B',
    'CD3301', '676767', '807FFE', '804000', 'FE80FE', '00FF41', 'FFFF00', 'FE0000',
    '808042', '247D9F', 'FE8081', '807FFE', 'FF8041', '427F80', 'C0C0C0', '7F00FF',
    'FF00FE', '800000', '7FFFFE', 'E3A39A', '804000', 'FFFF00', '800000', '007FFF',
    '7F00FF', 'FE0000', '81FF81', '47064A', 'B8D2D3', 'DBC1B0', '008001', 'FE8DA1',
    '47064A', '804000', 'FF0080');
/**
 * Rotates color images for a choice.
 *
 * @param Integer $index current position in colors, vfGetColor updates it's value
 *
 */
function vfGetColor(&$index)
{
    global $vgColors;
    $res = $vgColors[$index];
    $index = ($index + 1) % count($vgColors);
    return $res;
}
/**
 * Get a singleton of MediaWiki adapter
 */
function &vfAdapter()
{
    if(! isset($GLOBALS['vgAdapter']))
        $GLOBALS['vgAdapter'] = new MwAdapter();
    return $GLOBALS['vgAdapter'];
}
/**
 * Get a singleton of MediaWiki adapter
 */
function &vfUser()
{
    if(! isset($GLOBALS['vgUser']))
        $GLOBALS['vgUser'] = new MwUser();
    return $GLOBALS['vgUser'];
}
/**
 * Convert wiki text to regular text.
 * 
 *  1. Strip tags
 *  2. Replace '''bold text''' -> bold text
 *  3. Replace ''italic'' -> Italic
 *  4. Remove duplicate whitespace
 * 
 * @param String $wiki wiki text
 * @return String regular text
 */
function vfWikiToText($wiki)
{
    $text = strip_tags($wiki);
    $text = str_replace("'''",' ',$text);
    $text = str_replace("''",' ',$text);
    $text = preg_replace('/\s+/', ' ', $text); //@todo use mb_ereg_replace
    $invalidChars  = array('<','>','|','/',':');
    $text = trim(str_replace($invalidChars, " ", $text));
    return $text;
}
/**
 * @param String $phone phone number
 * @return String HTML code with modified phone
 */
function vfColorizePhone($phone, $colorsms=false, $obscure = false)
{
    global $vgSmsChoiceLen, $vgEnableSMS, $vgCountryCode;

    if($obscure)
        $phone = substr($phone, 0, -3) . "<font color=gray>XXX</font>";

    $prefix = '+'.$vgCountryCode;
    if(substr($phone,0,strlen($prefix)) == $prefix)
    {
        // in Malaysia we are lucky to have a prefix '60' which ends with zero
        $prefix = preg_replace('/0$/', '', $prefix);

        $phone = '<font color=gray>'.substr($phone,0,strlen($prefix)).'</font>&thinsp;'
                .substr($phone,strlen($prefix));
    }
    if(! $vgEnableSMS || ! $colorsms)
        return $phone;
    return substr($phone, 0, -$vgSmsChoiceLen)
            . '<font color=#E00000>'.substr($phone,-$vgSmsChoiceLen,$vgSmsChoiceLen).'</font>';
}
/**
 * This is used to connect database.
 */
include_once("adodb/adodb.inc.php");
include_once("adodb/adodb-exceptions.inc.php");
/**
 * Connect database without parameters
 * @return ADOConnection
 */
function vfConnectDatabase()
{
    global $vgDBType, $vgDBserver, $vgDBUserName, $vgDBUserPassword, $vgDBName;

    $cn = ADONewConnection($vgDBType);
    if (!$cn->Connect($vgDBserver, $vgDBUserName, $vgDBUserPassword, $vgDBName))
        throw new SurveyException("Could not connect to database", 400);
    return $cn;
}
/**
 * @var $vgDB global variable ADOdb connection
 */
global $vgDB;
$vgDB = vfConnectDatabase();

if(!isset($_GET['action']) || $_GET['action'] != 'ajax')
{
    $vgDB->debug = true;
}

$vgDB->LogSQL(true);
