<?php
$tables = array(
	'csiro_number',
	'incomingcall',
	'incomingsms',
	'outgoingsms',
	'page',
	'presentation',
	'quizresultsms',
	'survey',
	'surveychoice',
	'surveyrecord',
	'test',
	'textresponsesms',
);

global $gDB;

foreach($tables as $name)
{
	$sql ="TRUNCATE ".$name;
	$gDB->SetFetchMode(ADODB_FETCH_ASSOC);
	assert( $rs = &$gDB->Execute($sql) );
	$rs->Close();
}
?>