<?php
if (!defined('MEDIAWIKI')) die('Cannot access this file.');

require_once("$vgPath/votapedia.settings.php");
require_once("$vgPath/UserHooks.php" );

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