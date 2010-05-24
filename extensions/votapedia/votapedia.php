<?php
if (!defined('MEDIAWIKI')) die();

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

/* Remove prefixes and suffixes in "Survey Category" listing */
$vgCatRemovePrefix = array('Category:Surveys in ', 'Category:Quizes in ','Category:');
$vgCatRemoveSuffix = array(' Surveys', ' Survey', ' Quiz', 'Quizes');

// Allowed HTML/Mediawiki tags in survey choices.
$vgAllowedTags = '<math><code><b><u><i>';

//Allow anonymous users to create surveys
$vgAnonSurveyCreation = true;

// Votapedia script path, and extensions.
$vgPath = "$IP/extensions/votapedia";
$vgScript = "$wgScriptPath/extensions/votapedia";

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

$vgUseDaemon = false; // specify whether or not you are using daemon, please refer to documentation.

/******************************************************************/
/*** Do not edit items below unless you know what you are doing ***/
/******************************************************************/

require_once( "$vgPath/UserHooks.php" );

if(! $vgUseDaemon)
{
    // If we are not using daemon, maintenance must be manually called
    //@todo
}

//International Texts and Aliases
$wgExtensionMessagesFiles['Votapedia'] = "$vgPath/votapedia.i18n.php";
$wgExtensionAliasesFiles['Votapedia'] = "$vgPath/votapedia.alias.php";

//MediaWiki Adapter
$wgAutoloadClasses['MwAdapter'] = "$vgPath/MwAdapter.php";
$wgAutoloadClasses['MwParser'] = "$vgPath/MwAdapter.php";
$wgAutoloadClasses['MwUser'] = "$vgPath/MwAdapter.php";

//Special page CreateSurvey
$wgAutoloadClasses['spCreateSurvey'] = "$vgPath/special/CreateSurvey.php";
$wgSpecialPages['CreateSurvey'] = 'spCreateSurvey';

$wgAutoloadClasses['spCreateQuestionnaire'] = "$vgPath/special/CreateQuestionnaire.php";
$wgSpecialPages['CreateQuestionnaire'] = 'spCreateQuestionnaire';

//Special page ViewSurvey
$wgAutoloadClasses['ViewSurvey'] = "$vgPath/special/ViewSurvey.php";
$wgAutoloadClasses['SurveyButtons'] = "$vgPath/survey/SurveyButtons.php";
$wgAutoloadClasses['SurveyBody'] = "$vgPath/survey/SurveyBody.php";
$wgSpecialPages['ViewSurvey'] = 'ViewSurvey';

//Special page ProcessSurvey
$wgAutoloadClasses['ProcessSurvey'] = "$vgPath/special/ProcessSurvey.php";
$wgSpecialPages['ProcessSurvey'] = 'ProcessSurvey';

//Tag <Survey />
//Survey view options
$wgAutoloadClasses['SurveyView'] = "$vgPath/survey/SurveyView.php";

$wgAjaxExportList[] = 'SurveyView::getChoices';
$wgAjaxExportList[] = 'SurveyView::getChoice';

$wgAjaxExportList[] = 'SurveyBody::ajaxChoice';
$wgAjaxExportList[] = 'SurveyBody::getChoices';

$wgHooks['ParserFirstCallInit'][] = 'vfParserFirstCallInit';
function vfParserFirstCallInit( &$parser )
{
    $parser->setHook( 'SurveyChoice', 'SurveyView::executeTag' );
    $parser->setFunctionHook( 'Survey', 'SurveyView::executeMagic' );
    return true;
}

//Magic words (tags)
define('vtagSIMPLE_SURVEY',    'Survey');
define('vtagQUIZ',             'Quiz');
define('vtagRANK_EXPOSITIONS', 'Rankexpo');
define('vtagQUESTIONNAIRE',    'Questionnaire');
define('vtagTEXT_RESPONSE',    'TextResponse');

$wgHooks['LanguageGetMagic'][]       = 'vfLanguageGetMagic';
function vfLanguageGetMagic(&$magicWords, $langCode)
{
    $magicWords['Survey'] = array(0,
            vtagSIMPLE_SURVEY, vtagQUIZ, vtagRANK_EXPOSITIONS,
            vtagQUESTIONNAIRE, vtagTEXT_RESPONSE);
    return true;
}

//Credits
$wgExtensionCredits['other'][] = array(
        'name' => 'Votapedia.net',
        'author' => 'Emir Habul',
        'url' => 'http://www.votapedia.net/',
        'description' => 'Votapedia - Audience Response System',
        'descriptionmsg' => 'votapedia-desc',
        'version' => '1.0.0',
);

?>