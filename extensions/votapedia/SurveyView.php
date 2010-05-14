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
	private $frame;
	private $parser;
	
	private $page; /** PageVO */
	private $username; /** String */
	private $page_id; /** Integer */
	protected $wikititle; /** Title */
	
	/**
	 * Function called for the &lt;SurveyChoice&gt; tag 
	 * @param  $input String text between tags
	 * @param  $args Array tag arguments
	 * @param  $parser Parser of Mediawiki
	 * @param  $frame
	 */
	static function executeTag( $input, $args, $parser, $frame = NULL )
	{
		$page_id = intval(trim($input));
		try
		{
			#var_dump($parser);
			$mwparser = new MwParser($parser);
			$mwparser->setTag();
			$tag = new SurveyView($page_id, $mwparser);
			return $tag->getHTMLBody();
		}
		catch(Exception $e)
		{
			return vfErrorBox($e->getMessage());
		}
	}
	/**
	 * Function called for the Survey magic tag.
	 * 
	 * @param $parser Parser mediawiki type
	 * @param $page_id Integer page identifier
	 */
	static function executeMagic($parser, $page_id)
	{
		wfLoadExtensionMessages('Votapedia');
		$page_id = intval(trim($page_id));
		$output =  "<SurveyChoice>$page_id</SurveyChoice>[[".wfMsg('cat-survey-name',$page_id)."]]";
		return array($output, 'noparse' => false);
	}
	
	protected function __construct($page_id, $parser, $frame = NULL)
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
	
	/**
	 * AJAX call, get the buttons of user which can edit the survey.
	 * 
	 * @param $page_id identifier of a survey
	 * @param $title title of a current page
	 * @param $status status of a survey
	 */
	static function getButtons($page_id, $title, $status='ready')
	{
		global $wgUser;
		if ($wgUser->isAnon()) { return ''; } //just in case
		
		wfLoadExtensionMessages('Votapedia');
		
		$prosurv = Title::newFromText('Special:ProcessSurvey');
		$cresurv = Title::newFromText('Special:CreateSurvey');
		
		$output = '<tr>';
		$output .= '<td>';
		$output .= '<form id="page'.$page_id.'" action="'.$prosurv->escapeLocalURL().'" method="POST">'
	    .'<input type="hidden" name="id" value="'.$page_id.'">'
		.'<input type="hidden" name="returnto" value="'.htmlspecialchars($title).'" />'
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
		if($status == 'ready')
		{
			$output .='<form id="editpage'.$page_id.'" action="'.$cresurv->escapeLocalURL().'" method="POST">'
			.'<input type="hidden" name="id" value="'.$page_id.'">'
			.'<input type="submit" name="wpEditButton" value="'.wfMsg('edit-survey').'">'
			.'<input type="hidden" name="returnto" value="'.htmlspecialchars($title).'" />'
			.'</form>';
		}
		return $output;
	}
	
	/**
	 * Similar to getButtons function, but this is used when scripting 
	 * is not enabled in browser. Get limited buttons for a user.
	 * 
	 * @return HTML code
	 */
	function noscriptButtons()
	{
		$viewsurv = Title::newFromText('Special:ViewSurvey');
		$cresurv = Title::newFromText('Special:CreateSurvey');
		
		return '<tr><td><form id="page'.$this->page_id.'" action="'.$viewsurv->escapeLocalURL().'" method="POST">'
		.'<input type="hidden" name="id" value="'.$this->page_id.'">'
		.'<input type="submit" name="wpSubmit" value="'.wfMsg('control-survey').'" />'
		.'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle->getDBkey()).'" />'
		.'</form>'
		.'<td><form id="editpage'.$this->page_id.'" action="'.$cresurv->escapeLocalURL().'" method="POST">'
		.'<input type="hidden" name="id" value="'.$this->page_id.'">'
		.'<input type="submit" name="wpEditButton" value="'.wfMsg('edit-survey').'">'
		.'<input type="hidden" name="returnto" value="'.htmlspecialchars($this->wikititle->getDBkey()).'" />'
		.'</form>';
	}

	/**
	 * Get body of survey
	 * @return html code
	 */
	function getHTMLBody()
	{
		global $gvScript;
		$output = '';
		
		$output.= '<a name="survey_id_'.$this->page_id.'"></a>';
		
		$output.= '<h2>'.wfMsg('survey-question', htmlspecialchars($this->page->getTitle())).'</h2>';
		$output.='<table cellspacing="0" style="font-size:large">';
		
		$output.= '<tr><td valign="top" colspan="2"><img src="'.$gvScript.'/images/spacer.gif" />';

		$survey = $this->page->getSurveys();
		$choices = $survey[0]->getChoices();
		
		if($this->page->getStatus()=='ready')
		{
			$output.='<tr><td colspan="2">';
			$output.='<ul>';
			$i=0;
			foreach ($choices as $choice)
			{
				$i++;
				global $wgOut;
				$choice = $this->parser->run($choice->getChoice());
				if($choice)
				{
					$output.="<li STYLE=\"list-style-image: url(".vfGetColorImage().
						")\"><label id=\"q$i\">$i. $choice</label></li>";
				}
			}
			$output.='</ul>';
		}
		elseif($this->page->getStatus() == 'active')
		{
			;
		}
		elseif($this->page->getStatus() == 'ended')
		{
			;
		}
		$output .= $this->getHTMLButtons();
		
		#$output.='<td valign="top"><div style="margin:0px 0px 0px 40px">
		#<img src="./utkgraph/displayGraph.php?pageTitle='.$encodedTitle.'&background='.$background.'" 
		#alt="sample graph" /></div></td></tr>';

		$output .= '</table>';
		return $output;
	}
	/**
	 * Get HTML buttons for a page that is cacheable
	 * 
	 * @return HTML code
	 */
	function getHTMLButtons()
	{
		//control button for those that don't have javascript
		$output = '<noscript>'.SurveyView::noscriptButtons().'</noscript>';
		
		$divname = "btnsSurvey$page_id-".rand();
		$output.= ""
		."<script type='text/javascript'>"
		."document.write('<div id=$divname></div>');"
		."if(wgUserName=='{$this->page->getAuthor()}')"
		."sajax_do_call('SurveyView::getButtons',[{$this->page_id},wgPageName,'{$this->page->getStatus()}'],function(o){"
		."document.getElementById('$divname').innerHTML=o.responseText;});</script>";
		return $output;
	}
	
	/**
	 * Current PageVO object
	 * 
	 * @return PageVO
	 */
	function &getPage()
	{
		return $this->page;
	}
}

class SurveyViewNocache extends SurveyView
{
	public function __construct($page_id, $parser, $frame = NULL)
	{
		parent::__construct($page_id, $parser, $frame);
	}
	
	/**
	 * Get HTML buttons for a page that is not cacheable
	 * 
	 * @return HTML code
	 */
	function getHTMLButtons()
	{
		$divname = "btnsSurvey$page_id-".rand();
		$output = "<div id='$divname'>";
		$output .= $this->getButtons($this->getPage()->getPageID(), 
			$this->wikititle->getDBkey(),$this->getPage()->getStatus());
		$output .= '</div>';
		return $output;
	}
}

?>