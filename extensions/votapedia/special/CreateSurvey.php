<?php
if (!defined('MEDIAWIKI')) die();

$wgAutoloadClasses['CreateSurvey'] = "$gvPath/special/CreateSurvey_body.php"; # Tell MediaWiki to load the extension body.
$wgSpecialPages['CreateSurvey'] = 'CreateSurvey';

?>