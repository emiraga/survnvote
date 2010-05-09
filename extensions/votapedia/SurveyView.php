<?php
if (!defined('MEDIAWIKI')) die();

global $gvPath;
require_once("$gvPath/Common.php");

/**
 * Class used to display parts of HTML related to the viewing of survey
 * 
 * @author Emir Habul
 *
 */
class SurveyView
{
	private $parser;
	private $frame;
	
	private $page; /** PageVO */
	private $status; /** String */
	private $username; /** String */
	private $page_id; /** Integer */
	private $wikititle; /** Title */

	static function execute( $input, $args, $parser, $frame = NULL )
	{
		try{
			$page_id = intval(trim($input));
			$tag = new SurveyView($page_id, $parser, $frame);
			return $tag->getHTMLBody();
		}
		catch(Exception $e)
		{
			return vfErrorBox($e->getMessage());
		}
	}
	
	function __construct($page_id, $parser, $frame)
	{
		wfLoadExtensionMessages('Votapedia');
		global $wgUser, $wgTitle;
		$this->parser = $parser;
		$this->frame = $frame;
		
		$this->page_id=$page_id;
		$this->username = $wgUser->getName();
		
		if(! $this->page_id)
			throw new Exception( wfMsg('id-not-present', htmlspecialchars($page_id)) );
		
		$surveydao = new SurveyDAO();
		$this->page = $surveydao->findByPageID( $page_id );
		$this->calcStatus();
		
		$this->wikititle = $wgTitle;
		if( $this->wikititle->getDBkey() == 'CreateSurvey')
		{
			//Specialpage:CreateSurvey may automatically call the renderer of a page
			//we are trying to get this global variable for actual generated wiki page
			global $gvWikiPageTitle;
			if(! isset($gvWikiPageTitle))
				throw new Exception('global variable $gvWikiPageTitle was not found');

			$this->wikititle = Title::newFromText( $gvWikiPageTitle );
		}
	}
	
	private function calcStatus()
	{
		$starttime = mktime($this->page->getStartTime());
		$endtime = mktime($this->page->getEndTime());
		$now = time();

		$this->status = 'ready';
		if ($starttime == $endtime)
			$this->status = 'ready';
		else if ($starttime <= $now && $now <= $endtime)
			$this->status = 'active';
		else if ($endtime < $now)
			$this->status = 'ended';
	}
	/**
	 * AJAX call, get the buttons of user which can edit the survey.
	 * 
	 * @param $page_id identifier of a survey
	 * @param $wikititle title of a current page
	 */
	static function getButtons($page_id, $wikititle, $status='ready')
	{
		global $wgUser;
		if ($wgUser->isAnon()) { return ''; } //just in case
		
		wfLoadExtensionMessages('Votapedia');
		
		$prosurv = Title::newFromText('Special:ProcessSurvey');
		$cresurv = Title::newFromText('Special:CreateSurvey');
		
		$output = '<tr>';
		$output .= '<td>';
		$output .= '<form id="page'.$page_id.'" action="'.$prosurv->escapeLocalURL().'" method="POST">'
		    . '<input type="hidden" name="id" value="'.$page_id.'">'
			.'<input type="hidden" name="wpEditToken" value="'.htmlspecialchars( $wgUser->editToken() ).'">';
		if($status == 'ready')
		{
			$output.='<input type="submit" name="wpSubmit" value="'.wfMsg('start-survey').'" />';
		}
		elseif($status == 'active')
		{
			$output.='<input type="submit" name="wpSubmit" value="'.wfMsg('stop-survey').'" />';
		}
		else
		{
			;
		}
		$output .= '</form>';
		$output .= '<td>';
		$output .='<form id="editpage'.$page_id.'" action="'.$cresurv->escapeLocalURL().'" method="POST">'
			.'<input type="hidden" name="id" value="'.$page_id.'">'
			.'<input type="submit" name="wpEditButton" value="'.wfMsg('edit-survey').'">'
			.'<input type="hidden" name="returnto" value="'.htmlspecialchars($wikititle).'" />'
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
	function noscriptButtons()
	{
		$prosurv = Title::newFromText('Special:ProcessSurvey');
		$cresurv = Title::newFromText('Special:CreateSurvey');
		
		return '<tr><td><form id="page'.$this->page_id.'" action="'.$prosurv->escapeLocalURL().'" method="POST">'
			.'<input type="hidden" name="id" value="'.$this->page_id.'">'
			.'<input type="submit" name="wpSubmit" value="'.wfMsg('control-survey').'" />'
			.'</form>'
			.'<form id="editpage'.$this->page_id.'" action="'.$cresurv->escapeLocalURL().'" method="POST">'
			.'<input type="hidden" name="id" value="'.$this->page_id.'">'
			.'<input type="submit" name="wpEditButton" value="'.wfMsg('edit-survey').'">'
			.'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle).'" />'
			.'</form>';
	}

	function getHTMLBody()
	{
		global $gvScript, $gvAllowedTags;
		$output = '';
		
		$output.= '<h2>'.wfMsg('survey-question', htmlspecialchars($this->page->getTitle())).'</h2>';
		$output.='<table cellspacing="0" style="font-size:large">';
		
		$output.= '<tr><td valign="top" colspan="2"><img src="'.$gvScript.'/images/spacer.gif" />';
		// put an 250*1 spacer image above the choices so that the text doesn't get 
		// squashed by the graph when browser is less than full screen.
		
		$survey = $this->page->getSurveys();
		$choices = $survey[0]->getChoices();
		
		if($this->status=='ready')
		{
			$output.='<tr><td colspan="2">';
			$output.='<ul>';
			$i=0;
			foreach ($choices as $choice)
			{
				$i++;
				$choice = $this->parser->recursiveTagParse(
					strip_tags( $choice->getChoice(), $gvAllowedTags));
				if($choice)
				{
					$output.="<li STYLE=\"list-style-image: url(".vfGetColorImage().
						")\"><label id=\"q$i\">$i. $choice</label></li>";
				}
			}
			$output.='</ul>';
		}
		elseif($surveyStatus == 'active')
		{
			;
		}
		elseif($surveyStatus == 'ended')
		{
			;
		}
		
		//control button for those that don't have javascript
		$output.= '<noscript>'.SurveyView::noscriptButtons().'</noscript>';
		
		$divname = "btnsSurvey$page_id";
		$output.= "<div id='$divname'><input type=button value='Loading...'/></div>"
		."<script>if(wgUserName=='{$this->page->getAuthor()}')"
		."sajax_do_call('SurveyView::getButtons',[$page_id,wgPageName,'$this->status'],function(o){"
		."document.getElementById('$divname').innerHTML=o.responseText;});</script>";
			
		#$output.='<td valign="top"><div style="margin:0px 0px 0px 40px">
		#<img src="./utkgraph/displayGraph.php?pageTitle='.$encodedTitle.'&background='.$background.'" 
		#alt="sample graph" /></div></td></tr>';

		$output .= '</table>';
		return $output;
	}
}
