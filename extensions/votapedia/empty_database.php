<?php
if (!defined('MEDIAWIKI')) die();

$tables = array(
	//'csiro_number',
	//'incomingcall',
	//'incomingsms',
	//'outgoingsms',
	'page',
	'presentation',
	//'quizresultsms',
	'survey',
	'surveychoice',
	'surveyrecord',
	//'test',
	//'textresponsesms',
);

global $gvDB, $gvDBPrefix;

foreach($tables as $name)
{
	$sql ="TRUNCATE {$gvDBPrefix}$name";
	$gvDB->SetFetchMode(ADODB_FETCH_ASSOC);
	assert( $rs = &$gvDB->Execute($sql) );
	$rs->Close();
}
?>