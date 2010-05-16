<?php
if (!defined('MEDIAWIKI')) die();

/* Votapedia database connection */
$vgDBserver         = $wgDBserver;
$vgDBName           = $wgDBname;
$vgDBUserName       = $wgDBuser;
$vgDBUserPassword   = $wgDBpassword;
$vgDBPrefix         = "v_";
$vgDBType           = 'mysql';

/* Set Timezone -- check the manual http://php.net/manual/en/timezones.php */
date_default_timezone_set('Asia/Kuala_Lumpur');

/*
 * Configure phone numbers of PBX
 */
$gvNumberCallerID = '82315772';
$gvNumberUserPass = '81161899';
$gvNumberPBX = '8116';
$gvCountry = 'Malaysia';

$vgSmsChoiceLen = 2; /* How many last digits of phone number should be used for SMS choice*/
                     /* Example:   phone = +60102984598   sms = 98    vgSmsChoiceLen = 2*/

/* Remove prefixes and suffixes in "Survey Category" listing */
$gvCatRemovePrefix = array('Category:Surveys in ', 'Category:Quizes in ','Category:');
$gvCatRemoveSuffix = array(' Surveys', ' Survey', ' Quiz', 'Quizes');

/* Allowed HTML/Mediawiki tags in survey choices. */
$gvAllowedTags = '<math><code><source><pre><b><u><i>';

/* Votapedia script path, and extensions. */
$gvPath = "$IP/extensions/votapedia";
$gvScript = "$wgScriptPath/extensions/votapedia";

/* Template which is used to insert an existing survey into the page */
$gvSurveyTemplate = 'Survey';

/**
 * Returns an array containing all phone numbers that can be used for voting
 */
function vfGetAllNumbers()
{
	$out = array();
	for($i=0;$i<=99;$i++)
		$out[] = '+601029113' . sprintf("%02d",$i);
	return $out;
}

/******************************************************************/
/*** Do not edit items below unless you know what you are doing ***/
/******************************************************************/

#debug script
//require_once( "$gvPath/SpecialEmirTest.php" ); //@todo remove this
require_once( "$gvPath/UserHooks.php" );

//International Texts and Aliases
$wgExtensionMessagesFiles['Votapedia'] = "$gvPath/votapedia.i18n.php";
$wgExtensionAliasesFiles['Votapedia'] = "$gvPath/votapedia.alias.php";

//MediaWiki Adapter
$wgAutoloadClasses['MwAdapter'] = "$gvPath/MwAdapter.php";
$wgAutoloadClasses['MwParser'] = "$gvPath/MwAdapter.php";

//Special page CreateSurvey
$wgAutoloadClasses['spCreateSurvey'] = "$gvPath/special/CreateSurvey.php";
$wgSpecialPages['CreateSurvey'] = 'spCreateSurvey';

$wgAutoloadClasses['spCreateQuestionnaire'] = "$gvPath/special/CreateQuestionnaire.php";
$wgSpecialPages['CreateQuestionnaire'] = 'spCreateQuestionnaire';

//Special page ViewSurvey
$wgAutoloadClasses['ViewSurvey'] = "$gvPath/special/ViewSurvey.php";
$wgSpecialPages['ViewSurvey'] = 'ViewSurvey';

//Special page ProcessSurvey
$wgAutoloadClasses['ProcessSurvey'] = "$gvPath/special/ProcessSurvey.php";
$wgSpecialPages['ProcessSurvey'] = 'ProcessSurvey';

//Tag <Survey />
//Survey view options
$wgAutoloadClasses['SurveyView'] = "$gvPath/SurveyView.php";
$wgAutoloadClasses['SurveyViewNocache'] = "$gvPath/SurveyView.php";
$wgAjaxExportList[] = 'SurveyView::getButtons';
$wgAjaxExportList[] = 'SurveyView::getChoices';
$wgAjaxExportList[] = 'SurveyView::getChoice';

$wgHooks['ParserFirstCallInit'][] = 'vfParserFirstCallInit';
function vfParserFirstCallInit( &$parser ){
	$parser->setHook( 'SurveyChoice', 'SurveyView::executeTag' );
	$parser->setFunctionHook( 'Survey', 'SurveyView::executeMagic' );
	return true;
}

//Magic words
$wgHooks['LanguageGetMagic'][]       = 'vfLanguageGetMagic';
function vfLanguageGetMagic(&$magicWords, $langCode)
{
	$magicWords['Survey'] = array( 0, 'Survey' );
	return true;
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