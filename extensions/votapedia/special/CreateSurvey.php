<?php
if (!defined('MEDIAWIKI')) die();

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'CreateSurvey',
	'author' => 'Emir Habul',
	'url' => 'http://210.48.222.71/',
	'description' => 'Create New Survey',
	'descriptionmsg' => 'createsurvey-desc',
	'version' => '1.0.0',
);

$wgAutoloadClasses['CreateSurvey'] = "$gvPath/special/CreateSurvey_body.php"; # Tell MediaWiki to load the extension body.
$wgExtensionMessagesFiles['CreateSurvey'] = "$gvPath/special/CreateSurvey.i18n.php";
$wgExtensionAliasesFiles['CreateSurvey'] = "$gvPath/special/CreateSurvey.alias.php";
$wgSpecialPages['CreateSurvey'] = 'CreateSurvey';

?>