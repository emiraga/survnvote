<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Rename this file to config.php after you are done editing it.
 *
 * @package VotapediaCommon
 */

// Votapedia script path, and extensions.
$vgPath = "$IP/extensions/votapedia";
$vgScript = "$wgScriptPath/extensions/votapedia";

// Votapedia database connection, change this to different values //
// if you don't want to use same database/user as Mediawiki
$vgDBserver         = $wgDBserver;
$vgDBName           = $wgDBname;
$vgDBUserName       = $wgDBuser;
$vgDBUserPassword   = $wgDBpassword;
$vgDBType           = $wgDBtype;
$vgDBPrefix         = "v_";

// Configure phone numbers of PBX
#$vgNumberCallerID = '82315772';
#$vgNumberUserPass = '81161899';
#$vgNumberPBX = '8116';
$vgCountry = 'Malaysia';
$vgCountryCode = '60';

$vgEnableSMS = true;

if($vgEnableSMS)
{
    /* Prefix for table names that hold sms messages.
     * Configure the name of GAMMU-SMSD database followed by a dot
     * or a prefix for tables that hold sms messages   */
    $vgSmsPrefix       = 'smsd.';
    #$vgSmsPrefix       = 'v_sms_';

    // How many last digits of phone number should be used for SMS choice
    $vgSmsChoiceLen = 2;  // Example:  vgSmsChoiceLen = 2  phone = +60102984598   sms = 98

     // This is the number that receives SMS messages from voters.
    $vgSmsNumber = '+60132156758';
}

// Length of confirm field
$vgConfirmCodeLen = 6; 

// Remove prefixes and suffixes in "Survey Category" listing
$vgCatRemovePrefix = array('Category:Surveys in ', 'Category:Quizes in ','Category:');
$vgCatRemoveSuffix = array(' Surveys', ' Survey', ' Quiz', 'Quizes');

// Allowed HTML/Mediawiki tags in survey choices.
$vgAllowedTags = '<math><code><b><u><i>';

// Allow anonymous users to create surveys
$vgAnonSurveyCreation = true;

// Interval after which images will be refreshed, set 0 to disable
$vgImageRefresh = 10; //@todo increase this value

/**
 * @return Array containing all phone numbers that can be dialed for voting
 */
function vfGetAllNumbers()
{
    $out = array();
    for($i=0;$i<=99;$i++)
    {
        $out[] = '+603619699' . sprintf("%02d",$i);
    }
    return $out;
}
/**
 * @param String $number input number from user
 * @return String processed number
 */
function vfProcessNumber($number)
{
    global $vgCountryCode;
    $number = preg_replace('/[^0-9]/','',$number);//remove other characters
    $number = preg_replace('/^0*/', '', $number);//remove leading zeroes
    if(strlen($number) <= 9)
        $number = $vgCountryCode . $number;//add country code
    return "+".$number;
}

