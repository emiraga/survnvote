<?php
if (!defined('MEDIAWIKI')) die();
//$gvPath

$gvDBserver              = "localhost";
$gvWikiDBname            = "wikidb";
$gvDataSourceName        = "voting";
$gvDBUserName            = "root";
$gvDBUserPassword        = "";

//Set Timezone -- check the manual http://php.net/manual/en/timezones.php
date_default_timezone_set('Asia/Kuala_Lumpur');

$gvNumberCallerID = '82315772';
$gvNumberUserPass = '81161899';
$gvNumberPBX = '8116';

########################################################

if(defined('VOTAPEDIA_TEST')) //used for unit testing
{
	$gvDataSourceName = "unittest";
}

$gvPath = "$IP/extensions/votapedia"; //path to votapedia extension
require_once( "$gvPath/survey/connection.php" );
require_once( "$gvPath/survey/error.php" );
require_once( "$gvPath/SpecialCreateSurvey.php" );
require_once( "$gvPath/ChoiceTagExtension.php" );
require_once( "$gvPath/SpecialEmirTest.php" );
require_once( "$gvPath/ArticleHooks.php" );

?>