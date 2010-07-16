<?php
if(!defined('MEDIAWIKI')) die();
/**
 * @package SurvnvoteCommon
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
define('vPHONE_UNKNOWN', 11);

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
    return '<div class="errorbox" style="margin-bottom: 0.5em;"><strong>'
    .$message.'</strong></div><div class="visualClear"></div>';
}
/**
 * Return a message in error box, will show as red in HTML
 *
 * @param String $message
 * @return String HTML code
 */
function vfSuccessBox($message)
{
    return '<div class="successbox" style="margin-bottom: 0em;"><strong>'
    .$message.'</strong></div><div class="visualClear"></div>';
}
/**
 * Custom Exception class for surveys
 * @package SurvnvoteCommon
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
    //Remove bold markup
    $text = str_replace("'''",' ',$text);
    //Remove italic markup
    $text = str_replace("''",' ',$text);
    //http://en.wikipedia.org/wiki/Wikipedia%3ANaming_conventions_%28technical_restrictions%29
    $invalidChars  = array('<','>','|','/',':', '#', '[',']', '|', '{' ,'}','.','&');
    $text = trim(str_replace($invalidChars, " ", $text));
    $text = preg_replace('/\s+/', ' ', $text); //@todo use mb_ereg_replace
    return $text;
}
/**
 * @param String $phone phone number
 * @return String HTML code with modified phone
 */
function vfColorizePhone($phone, $colorsms=false, $obscure = false)
{
    global $vgSmsChoiceLen, $vgEnableSMS, $vgCountryCode;

    if($obscure && strlen($phone) > 6)
        $phone = substr($phone, 0, -3) . "<font color=gray>XXX</font>";

    $prefix = '+'.$vgCountryCode;
    if(substr($phone,0,strlen($prefix)) == $prefix)
    {
        // in Malaysia we are lucky to have a prefix '60' which ends with zero
        $prefix = preg_replace('/0$/', '', $prefix);

        $phone = '<font color=gray>'.substr($phone,0,strlen($prefix)).'</font> '
                .substr($phone,strlen($prefix));
    }
    if(! $vgEnableSMS || ! $colorsms)
        return $phone;
    return substr($phone, 0, -$vgSmsChoiceLen)
            . '<font color=#E00000>'.substr($phone,-$vgSmsChoiceLen,$vgSmsChoiceLen).'</font>';
}
/**
 *
 * @param String $email
 * @param Boolean $obscure
 * @return String
 */
function vfColorizeEmail($email, $obscure = false)
{
    $part = preg_split('/@/', $email);
    if(count($part) < 2)
        return $email;
    if($obscure)
    {
        $one = substr($part[0], 0, 3).'<font color=gray>...</font>';
        $two = substr($part[1], 0, 3).'<font color=gray>...</font>';
    }
    return $one.'@'.$two;
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
    $cn->SetFetchMode(ADODB_FETCH_ASSOC);
    return $cn;
}
/**
 * Compare date with current datetime and give description.
 * 
 * @param String $date
 * @param String format 'n' - normal, 'a' - abreviated, 'l' - long
 * @return String
 */
function vfPrettyDate($date, $format = 'a')
{
    $diff = time() - strtotime($date);
    $dayDiff = floor($diff / 86400);

    if(is_nan($dayDiff) || $dayDiff < 0)
        return '';
    
    if($dayDiff == 0)
    {
        if($diff < 60)
        {
            $res = 'Just now';
        } elseif($diff < 120)
        {
            $res = '1 minute ago';
        } elseif($diff < 3600)
        {
            $res = floor($diff/60) . ' minutes ago';
        } elseif($diff < 7200)
        {
            $res = '1 hour ago';
        } elseif($diff < 86400)
        {
            $res = floor($diff/3600) . ' hours ago';
        }
    } elseif($dayDiff == 1)
    {
        $res = 'Yesterday';
    } elseif($dayDiff < 7)
    {
        $res = $dayDiff . ' days ago';
    } elseif($dayDiff == 7)
    {
        $res = '1 week ago';
    } elseif($dayDiff < (7*6))
    {
        $res = ceil($dayDiff/7) . ' weeks ago';
    } elseif($dayDiff < 365)
    {
        $res = ceil($dayDiff/(365/12)) . ' months ago';
    } else
    {
        $years = round($dayDiff/365);
        $res = $years . ' year' . ($years != 1 ? 's' : '') . ' ago';
    }
    if($format == 'a')
    {
        return $res.' <abbr title="'.htmlspecialchars($date).'">*</abbr>';
    }
    elseif($format == 'l')
    {
        return htmlspecialchars($date) . ' ('.$res.')';
    }
    else
    {
        return $res;
    }
}

global $vgDB, $vgDebug;
if(! $vgDebug)
{
    $vgDB = vfConnectDatabase();
}
else
{
    // In case we are debugging/profiling use the Facade design patern
    require_once("$vgPath/misc/DebugDatabase.php");
    $vgDB = vfConnectDebugDatabase();
}

global $vgDebugIPs;
if($vgDebug && ( !isset($_SERVER['REMOTE_ADDR'])
            || in_array($_SERVER['REMOTE_ADDR'], $vgDebugIPs) ))
{
    if(!isset($_GET['action']) || $_GET['action'] != 'ajax')
    {
        $vgDB->enableOutput(true);
    }
}


require_once "Net/GeoIP.php";

$geoip = Net_GeoIP::getInstance("c:/bin/GeoIP.dat");


//try {
    //echo $geoip->lookupCountryCode('210.48.222.71').'<br>';
//}
