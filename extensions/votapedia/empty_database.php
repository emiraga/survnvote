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

global $vgDB, $vgDBPrefix;

foreach($tables as $name)
{
	$sql ="TRUNCATE {$vgDBPrefix}$name";
	$vgDB->SetFetchMode(ADODB_FETCH_ASSOC);
	assert( $rs = &$vgDB->Execute($sql) );
	$rs->Close();
}
?>