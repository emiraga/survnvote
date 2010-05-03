<?php
if (!defined('MEDIAWIKI')) die();

$gvDBserver              = "localhost";
$gvWikiDBname            = "wikidb";
$gvDataSourceName        = "unittest";
$gvDBUserName            = "root";
$gvDBUserPassword        = "";

//Set Timezone -- check the manual http://php.net/manual/en/timezones.php
date_default_timezone_set('Asia/Kuala_Lumpur');

$gvPath = "$IP/extensions/votapedia";

$gvNumberCallerID = '82315772';
$gvNumberUserPass = '81161899';
$gvNumberPBX = '8116';

require_once( "$gvPath/SpecialCreateSurvey.php" );
require_once( "$gvPath/ChoiceTagExtension.php" );
require_once( "$gvPath/SpecialEmirTest.php" );
require_once( "$gvPath/ArticleHooks.php" );

?>