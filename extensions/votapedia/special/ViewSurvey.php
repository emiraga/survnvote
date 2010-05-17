<?php
if (!defined('MEDIAWIKI')) die();

global $gvPath;
require_once("$gvPath/Common.php" );

/**
 * Special page View Survey
 * 
 * @author Emir Habul
 */
class ViewSurvey extends SpecialPage {
	/**
	 * Constructor for ProcessSurvey
	 */
	function __construct() {
		parent::__construct('ViewSurvey');
		wfLoadExtensionMessages('Votapedia');
		$this->includable( false );
	}
	/**
	 * Mandatory execute function for a Special Page
	 * 
	 * @param $par
	 */
	function execute( $par = null )
	{
		global $wgOut, $wgParser, $wgRequest;
		$wgOut->setPageTitle( wfMsg('title-view-survey') );
		$wgOut->setArticleFlag(false);
		vfGetColorImage(true);
                
		try
		{
			$page_id = intval($wgRequest->getVal('id'));
			$parser = new MwParser($wgParser, $wgOut->ParserOptions());
                        
                        $buttons =& new SurveyButtonsNocache();
			$tag = new SurveyView($page_id, $parser, $buttons);

			$wgOut->addHTML($tag->getHTMLBody());
			$wgOut->returnToMain();
			
			$author = $tag->getPage()->getAuthor();
			$text = '';
			$text .= "== More information ==\n";
			$text .= "* Author: [[User:$author|$author]]\n";
			$text .= "* Use following text to include this survey into a wiki page:\n";
			$text .= " <code><nowiki>{{#Survey:$page_id}}</nowiki></code>\n";
			$text .= "\n== ".wfMsg('page-links')." ==\n";
			$text .= wfMsg('pages-include')."\n";
			$pages = vfAdapter()->getSubCategories( wfMsg('cat-survey-name', $page_id) );
			
			foreach($pages as $name)
			{
				$text.="* [[$name#survey_id_$page_id|$name]]\n";
			}
			$wgOut->addWikiText($text);
		}
		catch(Exception $e)
		{
			$wgOut->addHTML(vfErrorBox($e->getMessage()));
		}
	}
}
