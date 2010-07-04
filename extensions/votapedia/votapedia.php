<?php
if (!defined('MEDIAWIKI')) die('Cannot access this file.');

/**
 * @package VotapediaCommon
 */

/** Configure votapedia */
require_once(dirname(__FILE__).'/config.php');

//International Texts and Aliases
$wgExtensionMessagesFiles['Votapedia'] = "$vgPath/votapedia.i18n.php";
$wgExtensionAliasesFiles['Votapedia'] = "$vgPath/votapedia.alias.php";

//MediaWiki Adapter
$wgAutoloadClasses['MwAdapter'] = "$vgPath/misc/MwAdapter.php";
$wgAutoloadClasses['MwParser'] = "$vgPath/misc/MwAdapter.php";
$wgAutoloadClasses['MwUser'] = "$vgPath/misc/MwAdapter.php";

//Special page CreateSurvey
$wgAutoloadClasses['spCreateSurvey'] = "$vgPath/special/CreateSurvey.php";
$wgSpecialPages['CreateSurvey'] = 'spCreateSurvey';

//Special page CreateQuestionnaire
$wgAutoloadClasses['spCreateQuestionnaire'] = "$vgPath/special/CreateQuestionnaire.php";
$wgSpecialPages['CreateQuestionnaire'] = 'spCreateQuestionnaire';

//Special page CreateQuiz
$wgAutoloadClasses['spCreateQuiz'] = "$vgPath/special/CreateQuiz.php";
$wgSpecialPages['CreateQuiz'] = 'spCreateQuiz';

//Special page ViewSurvey
$wgAutoloadClasses['ViewSurvey'] = "$vgPath/special/ViewSurvey.php";
$wgAutoloadClasses['RealSurveyBody'] = "$vgPath/survey/SurveyBody.php";
$wgSpecialPages['ViewSurvey'] = 'ViewSurvey';

//Special page CorrelateSurvey
$wgAutoloadClasses['CorrelateSurvey'] = "$vgPath/special/StatsSurvey.php";
$wgSpecialPages['CorrelateSurvey'] = 'CorrelateSurvey';

//Special page CrossTabSurvey
$wgAutoloadClasses['CrossTabSurvey'] = "$vgPath/special/StatsSurvey.php";
$wgSpecialPages['CrossTabSurvey'] = 'CrossTabSurvey';

//Special page ExportSurvey
$wgAutoloadClasses['ExportSurvey'] = "$vgPath/special/ExportSurvey.php";
$wgSpecialPages['ExportSurvey'] = 'ExportSurvey';

//Special page ProcessSurvey
$wgAutoloadClasses['ProcessSurvey'] = "$vgPath/special/ProcessSurvey.php";
$wgSpecialPages['ProcessSurvey'] = 'ProcessSurvey';

//Special page MyPhones
$wgAutoloadClasses['MyPhones'] = "$vgPath/special/MyPhones.php";
$wgSpecialPages['MyPhones'] = 'MyPhones';

//Special page SmsReport
$wgAutoloadClasses['SmsReport'] = "$vgPath/special/SmsReport.php";
$wgSpecialPages['SmsReport'] = 'SmsReport';

//Special page LatestIncoming
$wgAutoloadClasses['LatestIncoming'] = "$vgPath/special/LatestIncoming.php";
$wgSpecialPages['LatestIncoming'] = 'LatestIncoming';

//Special page LatestIncoming
$wgAutoloadClasses['VotapediaStats'] = "$vgPath/special/VotapediaStats.php";
$wgAutoloadClasses['VotapediaStatsUpdate'] = "$vgPath/special/VotapediaStats.php";
$wgSpecialPages['VotapediaStats'] = 'VotapediaStats';

//Special page SmsReport
$wgAutoloadClasses['SurveysList'] = "$vgPath/special/SurveysList.php";
$wgSpecialPages['SurveysList'] = 'SurveysList';

//Special page Crowd
$wgAutoloadClasses['Crowd'] = "$vgPath/special/Crowd.php";
$wgSpecialPages['Crowd'] = 'Crowd';

//Special page Crowd
$wgAutoloadClasses['ProcessCrowd'] = "$vgPath/special/ProcessCrowd.php";
$wgSpecialPages['ProcessCrowd'] = 'ProcessCrowd';

//Tag <Survey />
//Survey view options
$wgAutoloadClasses['SurveyView'] = "$vgPath/survey/SurveyView.php";

$wgAjaxExportList[] = 'SurveyView::getChoice';
$wgAjaxExportList[] = 'RealSurveyBody::getChoices';
$wgAjaxExportList[] = 'RealSurveyBody::ajaxgraph';

$wgHooks['ParserFirstCallInit'][] = 'vfParserFirstCallInit';
function vfParserFirstCallInit( &$parser )
{
    $parser->setHook( 'SurveyChoice', 'SurveyView::executeTag' );
    $parser->setFunctionHook( 'Survey', 'SurveyView::executeMagic' );
    $parser->setFunctionHook( 'vpSmsNumber', 'vfGetSmsNumber' );
    return true;
}

function vfGetSmsNumber($parser = null)
{
    global $vgSmsNumber;
    return $vgSmsNumber;
}

//API
$wgAPIModules['vpAutoUser'] = 'vpAutocreateUsers';
$wgAutoloadClasses['vpAutocreateUsers'] = "$vgPath/API/AutocreateUsers.php";

// Magic words (tags)
define('vtagSIMPLE_SURVEY',    'Survey');
define('vtagQUIZ',             'Quiz');
define('vtagRANK_EXPOSITIONS', 'Rankexpo');
define('vtagQUESTIONNAIRE',    'Questionnaire');
define('vtagTEXT_RESPONSE',    'TextResponse');

// Category names for surveys
define('vcatSIMPLE_SURVEY',    'Simple surveys');
define('vcatQUIZ',             'Quizzes');
define('vcatRANK_EXPOSITIONS', 'Rank expositions');
define('vcatQUESTIONNAIRE',    'Questionnaires');
define('vcatTEXT_RESPONSE',    'Text Responses');

$wgHooks['LanguageGetMagic'][]       = 'vfLanguageGetMagic';
function vfLanguageGetMagic(&$magicWords, $langCode)
{
    $magicWords['Survey'] = array(0,
            vtagSIMPLE_SURVEY, vtagQUIZ, vtagRANK_EXPOSITIONS,
            vtagQUESTIONNAIRE, vtagTEXT_RESPONSE);
    $magicWords['vpSmsNumber'] = array(0, 'vpSmsNumber');
    return true;
}

//change password hook
$wgAutoloadClasses['UserDAO'] = "$vgPath/DAO/UserDAO.php";
$wgHooks['PrefsPasswordAudit'][] = 'UserDAO::PrefsPasswordAudit';

//add personal URL for "my phones"
$wgHooks['PersonalUrls'][] = 'vfPersonalUrlsHook';
function vfPersonalUrlsHook( &$personal_urls, &$title )
{
    global $wgUser;
    if($wgUser->isLoggedIn())
    {
        $keys = array_keys($personal_urls);
        $pageurl = $title->getLocalURL();

        $hrefphones = Skin::makeSpecialUrl( 'MyPhones' );
        $hrefcrowd = Skin::makeSpecialUrl( 'Crowd' );
        $add_urls = array(
                $keys[0] => $personal_urls[$keys[0]],
                $keys[1] => $personal_urls[$keys[1]],
                'crowd' => array(
                        'text' => 'My crowd',
                        'href' => $hrefcrowd,
                        'active' => ( $hrefcrowd == $pageurl )
                ),
                'phones' => array(
                        'text' => 'My phones',
                        'href' => $hrefphones,
                        'active' => ( $hrefphones == $pageurl )
                ),
        );
        array_shift($personal_urls);
        array_shift($personal_urls);
        $personal_urls = $add_urls + $personal_urls;
    }
    return true;
}

//Credits
$wgExtensionCredits['other'][] = array(
        'name' => 'Votapedia.net',
        'author' => 'Emir Habul',
        'url' => 'http://www.votapedia.net/',
        'description' => 'Votapedia - Audience Response System',
        'descriptionmsg' => 'votapedia-desc',
        'version' => '0.2.5',
);

