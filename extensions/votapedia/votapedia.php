<?php
if (!defined('MEDIAWIKI')) die();

/* Votapedia database connection */
$gvDBserver         = $wgDBserver;
$gvDBName           = $wgDBname;
$gvDBUserName       = $wgDBuser;
$gvDBUserPassword   = $wgDBpassword;
$gvDBPrefix         = "v_";
$gvDBType           = 'mysql';

/* Set Timezone -- check the manual http://php.net/manual/en/timezones.php */
date_default_timezone_set('Asia/Kuala_Lumpur');

/*
 * Configure phone numbers of PBX
 */
$gvNumberCallerID = '82315772';
$gvNumberUserPass = '81161899';
$gvNumberPBX = '8116';
$gvCountry = 'Malaysia';

/* Remove prefixes and suffixes in "Survey Category" listing */
$gvCatRemovePrefix = array('Surveys in ', 'Quizes in ');
$gvCatRemoveSuffix = array(' Surveys', ' Survey', ' Quiz', 'Quizes');

/* Allowed HTML/Mediawiki tags in survey choices. */
$gvAllowedTags = '<math><code><source><pre><b><u><i>';

/* Votapedia script path, and extensions. */
$gvPath = "$IP/extensions/votapedia";
$gvScript = "$wgScriptPath/extensions/votapedia";

/* Template which is used to insert an existing survey into the page */
$gvSurveyTemplate = 'Survey';

/******************************************************************/
/*** Do not edit items below unless you know what you are doing ***/
/******************************************************************/

#debug script
require_once( "$gvPath/SpecialEmirTest.php" ); //@todo remove this
require_once( "$gvPath/UserHooks.php" );

//International Texts and Aliases
$wgExtensionMessagesFiles['Votapedia'] = "$gvPath/votapedia.i18n.php";
$wgExtensionAliasesFiles['Votapedia'] = "$gvPath/votapedia.alias.php";

//Special page CreateSurvey
$wgAutoloadClasses['CreateSurvey'] = "$gvPath/special/CreateSurvey.php";
$wgSpecialPages['CreateSurvey'] = 'CreateSurvey';

//Special page ProcessSurvey
$wgAutoloadClasses['ProcessSurvey'] = "$gvPath/special/ProcessSurvey.php";
$wgSpecialPages['ProcessSurvey'] = 'ProcessSurvey';

//Tag <Survey />
$wgAutoloadClasses['tagSurveyChoices'] = "$gvPath/tag/SurveyChoices.php";
$wgHooks['ParserFirstCallInit'][] = 'vfSurveyChoicesInit';
function vfSurveyChoicesInit( &$parser ){
	$parser->setHook( 'SurveyChoice', 'tagSurveyChoices::execute' ); return true;
}

//Credits
$wgExtensionCredits['other'][] = array(
	'name' => 'Votapedia',
	'author' => 'Emir Habul',
	'url' => 'http://votapedia.webhop.org/',
	'description' => 'Votapedia - Audience Response System',
	'descriptionmsg' => 'votapedia-desc',
	'version' => '1.0.0',
);

?>