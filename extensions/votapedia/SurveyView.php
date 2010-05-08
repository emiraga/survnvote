<?php
if (!defined('MEDIAWIKI')) die();

/**
 * Class used to display parts of HTML related to the viewing of survey
 * 
 * @author Emir Habul
 *
 */
class SurveyView
{
	/**
	 * AJAX call, get the buttons of user which can edit the survey.
	 * 
	 * @param $page_id identifier of a survey
	 * @param $wikititle title of a current page
	 */
	function getButtons($page_id, $wikititle)
	{
		global $wgUser;
		if ($wgUser->isAnon()) { return ''; } //just in case
		
		wfLoadExtensionMessages('Votapedia');
		
		$prosurv = Title::newFromText('Special:ProcessSurvey');
		$cresurv = Title::newFromText('Special:CreateSurvey');
		
		$output = '';
		$output .= '<form id="page'.$page_id.'" action="'.$prosurv->escapeLocalURL().'" method="POST">'
			.'<input type="hidden" name="id" value="'.$page_id.'">'
			.'<input type="hidden" name="wpEditToken" value="'.htmlspecialchars( $wgUser->editToken() ).'">'
			.'<input type="submit" name="wpSubmit" value="'.wfMsg('control-survey').'" />'
			.'</form>';
		return $output;
	}
	
	/**
	 * Similar to getButtons function, but this is used when scripting 
	 * is not enabled in browser. Get limited buttons for a user.
	 * 
	 * @param $page_id identifier of a survey
	 * @param $wikititle title of a current page
	 */
	function noscriptButtons($page_id, $wikititle)
	{
		$prosurv = Title::newFromText('Special:ProcessSurvey');
		$cresurv = Title::newFromText('Special:CreateSurvey');
		
		return '<form id="page'.$page_id.'" action="'.$prosurv->escapeLocalURL().'" method="POST">'
			.'<input type="hidden" name="id" value="'.$page_id.'">'
			.'<input type="submit" name="wpSubmit" value="'.wfMsg('control-survey').'" />'
			.'</form>'
			.'<form id="editpage'.$page_id.'" action="'.$cresurv->escapeLocalURL().'" method="POST">'
			.'<input type="hidden" name="id" value="'.$page_id.'">'
			.'<input type="submit" name="wpEditButton" value="'.wfMsg('edit-survey').'">'
			.'<input type="hidden" name="returnto" value="'.htmlspecialchars($wikititle).'" />'
			.'</form>';
	}
}
