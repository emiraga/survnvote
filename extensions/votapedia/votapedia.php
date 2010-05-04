<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Votapedia database connection
 */
$gvDBserver              = "localhost";
$gvDataSourceName        = "voting";
$gvDBUserName            = "root";
$gvDBUserPassword        = "";
#$gvWikiDBname            = "wikidb";

/**
 * Set Timezone -- check the manual http://php.net/manual/en/timezones.php
 */
date_default_timezone_set('Asia/Kuala_Lumpur');

/**
 * Configure phone numbers of PBX
 */
$gvNumberCallerID = '82315772';
$gvNumberUserPass = '81161899';
$gvNumberPBX = '8116';
$gvCountry = 'Malaysia';

/**
 * Remove prefixes and suffixes in Category listing
 */
$gvCatRemovePrefix = array('Surveys in ', 'Quizes in ');
$gvCatRemoveSuffix = array(' Surveys', ' Survey', ' Quiz', 'Quizes');


/******************************************************************/
/*** Do not edit items below unless you know what you are doing ***/
/******************************************************************/

/**
 * Use different database for unit testing. Do not edit.
 */
if(defined('VOTAPEDIA_TEST')) //used for unit testing
	$gvDataSourceName = "unittest";

/**
 * Votapedia script path, and extensions. Do not edit.
 */
$gvPath = "$IP/extensions/votapedia"; //path to votapedia extension
$gvScript = "$wgScriptPath/extensions/votapedia";

require_once( "$gvPath/Common.php" );
require_once( "$gvPath/special/CreateSurvey.php" );
require_once( "$gvPath/tag/SurveyChoices.php" );

require_once( "$gvPath/UserHooks.php" );

#require_once( "$gvPath/special/CreateSurvey.php" );
#require_once( "$gvPath/ChoiceTagExtension.php" );
#require_once( "$gvPath/SpecialEmirTest.php" );

/**
 * Types of surveys, do not edit.
 */
define('vSIMPLE_SURVEY',1);
define('vQUIZ',2);
define('vRANK_EXPOSITIONS',3);
define('vQUESTIONNAIRE', 4);
define('vTEXT_RESPONSE', 5);

?>