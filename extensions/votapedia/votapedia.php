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
 * Use different database for unit testing
 */
if(defined('VOTAPEDIA_TEST')) //used for unit testing
	$gvDataSourceName = "unittest";

/**
 * Remove prefixes and suffixes in Category listing
 */
$gvCatRemovePrefix = array('Surveys in ', 'Quizes in ');
$gvCatRemoveSuffix = array(' Surveys', ' Survey', ' Quiz', 'Quizes');

/**
 * Votapedia script path, and extensions
 */
$gvPath = "$IP/extensions/votapedia"; //path to votapedia extension
require_once( "$gvPath/survey/connection.php" );
require_once( "$gvPath/survey/error.php" );
require_once( "$gvPath/special/CreateSurvey.php" );
require_once( "$gvPath/tag/SurveyChoices.php" );

require_once( "$gvPath/UserHooks.php" );

#require_once( "$gvPath/special/CreateSurvey.php" );
#require_once( "$gvPath/ChoiceTagExtension.php" );
#require_once( "$gvPath/SpecialEmirTest.php" );

?>