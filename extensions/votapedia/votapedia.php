<?php
if (!defined('MEDIAWIKI')) die('Cannot access this file.');

/**
 * @package VotapediaCommon
 */

/** Configure votapedia */
require_once("config.php");

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

//Special page CreateQuestionnaire
$wgAutoloadClasses['spCreateQuestionnaire'] = "$vgPath/special/CreateQuestionnaire.php";
$wgSpecialPages['CreateQuestionnaire'] = 'spCreateQuestionnaire';

//Special page CreateQuiz
$wgAutoloadClasses['spCreateQuiz'] = "$vgPath/special/CreateQuiz.php";
$wgSpecialPages['CreateQuiz'] = 'spCreateQuiz';

//Special page ViewSurvey
$wgAutoloadClasses['ViewSurvey'] = "$vgPath/special/ViewSurvey.php";
$wgAutoloadClasses['SurveyButtons'] = "$vgPath/survey/SurveyButtons.php";
$wgAutoloadClasses['SurveyBody'] = "$vgPath/survey/SurveyBody.php";
$wgSpecialPages['ViewSurvey'] = 'ViewSurvey';

//Special page ProcessSurvey
$wgAutoloadClasses['ProcessSurvey'] = "$vgPath/special/ProcessSurvey.php";
$wgSpecialPages['ProcessSurvey'] = 'ProcessSurvey';

//Special page MyPhones
$wgAutoloadClasses['MyPhones'] = "$vgPath/special/MyPhones.php";
$wgSpecialPages['MyPhones'] = 'MyPhones';

//Special page SmsReport
$wgAutoloadClasses['SmsReport'] = "$vgPath/special/SmsReport.php";
$wgSpecialPages['SmsReport'] = 'SmsReport';

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
$wgAjaxExportList[] = 'SurveyBody::getChoices';
$wgAjaxExportList[] = 'SurveyBody::ajaxgraph';

$wgHooks['ParserFirstCallInit'][] = 'vfParserFirstCallInit';
function vfParserFirstCallInit( &$parser )
{
    $parser->setHook( 'SurveyChoice', 'SurveyView::executeTag' );
    $parser->setFunctionHook( 'Survey', 'SurveyView::executeMagic' );
    return true;
}

//API
$wgAPIModules['vpAutoUser'] = 'vpAutocreateUsers';
$wgAutoloadClasses['vpAutocreateUsers'] = "$vgPath/API/AutocreateUsers.php";

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
                'phones' => array(
                        'text' => 'My phones',
                        'href' => $hrefphones,
                        'active' => ( $hrefphones == $pageurl )
                ),
                'crowd' => array(
                        'text' => 'My crowd',
                        'href' => $hrefcrowd,
                        'active' => ( $hrefcrowd == $pageurl )
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
        'version' => '0.2.2',
);

