<?php
if (!defined('MEDIAWIKI') && !defined('VOTAPEDIA_DAEMON')) die();

// Votapedia script path, and extensions.
$vgPath = "$IP/extensions/votapedia";
$vgScript = "$wgScriptPath/extensions/votapedia";

// Votapedia database connection
$vgDBserver         = $wgDBserver;
$vgDBName           = $wgDBname;
$vgDBUserName       = $wgDBuser;
$vgDBUserPassword   = $wgDBpassword;
$vgDBPrefix         = "v_";
$vgDBType           = 'mysql';

// Set Timezone -- check the manual http://php.net/manual/en/timezones.php
date_default_timezone_set('Asia/Kuala_Lumpur');

// Configure phone numbers of PBX
$vgNumberCallerID = '82315772';
$vgNumberUserPass = '81161899';
$vgNumberPBX = '8116';
$vgCountry = 'Malaysia';

$vgSmsChoiceLen = 2; // How many last digits of phone number should be used for SMS choice
// Example:   phone = +60102984598   sms = 98    vgSmsChoiceLen = 2

// Remove prefixes and suffixes in "Survey Category" listing
$vgCatRemovePrefix = array('Category:Surveys in ', 'Category:Quizes in ','Category:');
$vgCatRemoveSuffix = array(' Surveys', ' Survey', ' Quiz', 'Quizes');

// Allowed HTML/Mediawiki tags in survey choices.
$vgAllowedTags = '<math><code><b><u><i>';

//Allow anonymous users to create surveys
$vgAnonSurveyCreation = true;

/**
 * @return array containing all phone numbers that can be used for voting
 */
function vfGetAllNumbers()
{
    $out = array();
    for($i=0;$i<=99;$i++)
    {
        $out[] = '+601029113' . sprintf("%02d",$i);
    }
    return $out;
}

$vgUseDaemon = false; // specify whether or not you are using daemon
