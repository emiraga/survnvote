<?php
if (!defined('MEDIAWIKI')) die();

$wgHooks['ParserFirstCallInit'][] = 'vfSurveyChoicesInit';

function vfSurveyChoicesInit( &$parser )
{
	$parser->setHook( 'SurveyChoices', 'vfSurveyChoices' );
	return true;
}

function vfSurveyChoices( $input, $args, $parser, $frame = NULL )
{
	$parser->disableCache();
	$output = '';
	$output .= $parser->recursiveTagParse('=Ham=');
	$output .= 'Tets<br /><br />';
	
	foreach( $args as $name => $value )
	{
		$output .= '<strong>' . htmlspecialchars( $name ) . '</strong> = ' . htmlspecialchars( $value ). '<br />';
	}
	$output .= "\n\n" . nl2br(htmlspecialchars( $input ));
	return $output;
}

?>