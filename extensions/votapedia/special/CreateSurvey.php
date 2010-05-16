<?php
if (!defined('MEDIAWIKI')) die();

global $gvPath;
require_once("$gvPath/Common.php" );
require_once("$gvPath/FormControl.php");
require_once("$gvPath/VO/PageVO.php");
require_once("$gvPath/DAO/SurveyDAO.php");

/**
 */
class spCreateSurvey extends SpecialPage
{
	private $obj;
	public function __construct()
	{
		parent::__construct('CreateSurvey');
		$this->obj = new CreateSurvey();
		$this->includable( true ); //we can include this from other pages
	}
	function execute( $par = null )
	{
		$this->obj->execute($par);
	}
}

/**
 * Special page Create Survey
 * 
 * @author Emir Habul
 */
class CreateSurvey
{
	protected $formitems;
	/**
	 * Constructor for CreateSurvey
	 */
	function __construct() {
		wfLoadExtensionMessages('Votapedia');
		$this->setFormItems();
		$this->form = new FormControl($this->formitems);
	}
	
	function setFormItems()
	{
		global $gvCountry, $gvScript;
		$this->formitems = array (
		'titleorquestion' => array(
			'type' => 'input',
			'name' => 'Title or question',
			'default' => '',
			'valid' => function ($v,$i,$js){ if($js) return ""; return strlen($v) > 10; },
			'explanation' => 'e.g. "What is the capital of '.$gvCountry.'?". This will be the title of your survey page.',
			'learn_more' => 'Details of Title or Survey Question',
			'process' => function($v) { return FormControl::RemoveSpecialChars($v); },
		),
		'choices' => array(
			'type' => 'textarea',
			'name' => 'Choices',
			'textbefore' => 'Type choices here, one per line.<br />',
			'valid' => function($v,$i,$js){ if($js) return ""; return strlen($v) > 1; },
			'explanation' => 'The choices can contain wiki markup language and you can add, delete or modify them later.',
			'learn_more' => 'Details of Editing Surveys',
			'textafter' => '<script>document.write("<b><a href=\'\' onClick=\\" previewdiv=$(\'#previewChoices\'); previewdiv.html(\'Loading...\'); sajax_do_call( \'SurveyView::getChoices\', [document.getElementById(\'choices\').value], function(o) { previewdiv.html(o.responseText); previewdiv.show(); });return false;\\"><img src=\\"'.$gvScript.'/icons/magnify.png\\" /> Preview choices</a></b><div id=previewChoices class=pBody style=\\"display: none\\"></div>");</script>',
		),
		'category' => array(
			'type' => 'select',
			'name' => 'Category',
			'default' => 'General',
			'valid' => function($v,$i,$js){ if($js) return ""; return true; },
			'explanation' => 'Your survey then would be added into the chosen category, and would be listed under that category.',
			'learn_more' => 'Details of Survey Category',
			'options' => array()
		),
		'label-details' => array(
			'type' => 'null',
			'explanation' => 'Once you start the survey, each choice will be assigned with a telephone number, audiences can ring this number, send SMS or visit the survey page to enter their vote.',
			'learn_more' => 'Details of Survey Procedure',
		),
		'privacy' => array(
			'type' => 'radio',
			'name' => 'Survey Privacy',
			'default' => 'low',
			'valid' => function($v,$i,$js){ if($js) return ""; return true; },
			'options' => array(
				  "Low - Public survey (anyone can vote) "=>"low",
				  "Medium - No information (Information about voting is not publicly available)"=>"medium",
				  "High - Restricted survey (Voting is restricted to the group of people) "=>"high",
			),
			'explanation' => 'This option determines who will be able to participate in your survey.',
			'learn_more' => 'Details of Survey Privacy',
			'icon' => $gvScript.'/icons/lock.png',
		),
		'duration' => array(
			'type' => 'input',
			'name' => 'Duration',
			'default' => '60',
			'width' => '10',
			'textafter' => ' minutes.',
			'valid' => function($v,$i,$js){ if($js) return ""; $v=intval($v); return $v > 0 && $v < 11*60; },
			'explanation' => 'Once you start the survey, it will run for this amount of time and stop automatically.',
			'learn_more' => 'Details of Duration',
			'process' => function($v) { return intval($v); },
		),
		'phonevoting' => array(
			'type' => 'radio',
			'name' => 'Phone voting',
			'default' => 'anon',
			'valid' => function($v,$i,$js){ if($js) return ""; return true; },
			'explanation' => '',
			'learn_more' => 'Details of Phone Voting',
			'options' => array(
				  "Enable unidentified voters. Recommended for phone surveys from outside of $gvCountry."=>"anon",
				  "Enable phone voting (only for identified callers - CallerID)"=>"yes",
				  "Disable phone voting"=>"no",),
			'icon' => $gvScript.'/icons/phone.png',
		),
		'webvoting' => array(
			'type' => 'radio',
			'name' => 'Web voting',
			'default' => 'anon',
			'valid' => function($v,$i,$js){ if($js) return ""; return true; },
			'explanation' => '',
			'learn_more' => 'Details of Web Voting',
			'options' => array(
				  "Enable anonymous web voting"=>"anon",
				  "Enable web voting (only for registered users)"=>"yes",
				  "Disable web voting"=>"no",),
			'icon' => $gvScript.'/icons/laptop.png',
		),
		'showresultsend' => array(
			'type' => 'checkbox',
			'name' => 'Graph Options',
			'default' => 'on',
			'checklabel' => ' Show results of voting only at the end. ',
			'valid' => function($v,$i,$js){ if($js) return ""; return true; },
			'explanation' => 'If checked, the survey result will only be shown after the survey finishes. Otherwise, voters will see the partial result after they vote.',
			//'learn_more' => 'Details_of_Anonymous_Voting',
		),
		'showtop' => array(
			'type' => 'input',
			'name' => 'Show only top',
			'default' => '',
			'width' => '10',
			'textbefore' => 'Show only top ',
			'textafter' => ' choices on the graph.',
			'valid' => function($v,$i,$js){ if($js) return ""; $v=intval($v); return $v >= 0 and $v < 1000; },
			'explanation' => 'If a number is specified, the graph will only display the top few choices on the graph. Otherwise, voters will see all the choices no matter how many votes they have got.',
			//'learn_more' => 'Details_of_Duration',
			'process' => function($v) { return intval($v); },
		),
		'voteridentity' => array(
			'type' => 'checkbox',
			'name' => 'Voter Identity',
			'default' => 'on',
			'checklabel' => ' Enable unidentified voters. Compulsory for phone surveys from outside '.$gvCountry.'.',
			'valid' => function($v,$i,$js){ if($js) return ""; return true; },
			'explanation' => 'CallerID is used to stop multiple voting. Only the calls with a CallerID is regarded as a valid vote. Phones with CallerID disabled or calling from outside Australia will not be able to vote if unchecked.',
			'learn_more' => 'Details of Multiple Voting',
		),
		'titlewarning' => array(
			'type' => 'infobox',
			'explanation' => 'If you decide to change the Title or question of this survey, it is recommended that you Rename/Move the corresponding wiki page in order to prevent any confusion.',
			'learn_more' => 'Changing Title of a survey',
		),
		);
		$subcat = vfAdapter()->getSubCategories('Category:Survey Categories');
		$subcat = $this->removePrefSufCategories($subcat);
		$this->formitems['category']['options'] = $subcat;
	}
	/**
	 * Remove prefix and suffix from category list
	 * $gvCatRemovePrefix, $gvCatRemoveSuffix
	 * 
	 * @param $cats array of category names
	 * @return array without prefixes and suffixes
	 */
	function removePrefSufCategories($cats)
	{
		global $gvCatRemovePrefix, $gvCatRemoveSuffix;
		
		$result = array();
		foreach($cats as $cat)
		{
			$name = $cat;
			foreach($gvCatRemovePrefix as $prefix)
				if(strcasecmp(substr($name,0,strlen($prefix)),$prefix) == 0)
					$name = substr($name, strlen($prefix));

			foreach($gvCatRemoveSuffix as $suffix)
				if(strcasecmp(substr($name,strlen($name) - strlen($suffix)),$suffix) == 0)
					$name = substr($name, 0, strlen($name) - strlen($suffix));

			$result[$name] = $cat;
		}
		return $result;
	}
	/**
	 * Generate PageVO object from the values
	 * 
	 * @param $values associative array with values
	 */
	private function generatePageVO($values)
	{
		global $wgUser;
		$author = $wgUser->getName();

		$page = new PageVO();
		$page->setType(vSIMPLE_SURVEY);
		$page->setTitle($values['titleorquestion']);
		$page->setAuthor($author);
		$page->setDisplayTop($values['showtop']);
		$page->setShowGraph(! (bool) $values['showresultsend']);
		$page->setDuration( $values['duration'] );
		$page->setVotesAllowed(1);
		$page->setSMSRequired(false); //@todo SMS sending to the users
		$page->setPrivacyByName($values['privacy']);
		$page->setPhoneVoting($values['phonevoting']);
		$page->setWebVoting($values['webvoting']);
		
		$surveyVO = new SurveyVO();
		$surveyVO->generateChoices( split("\n", $values['choices']) );
		$surveyVO->setQuestion('#see page title');
		$surveyVO->setType(vSIMPLE_SURVEY);
		$surveyVO->setVotesAllowed(1);
		$surveyVO->setPoints(0);
		
		$page->setSurveys(array($surveyVO));
		return $page;
	}
	/**
	 * Insert new page in mediawiki and votapedia database
	 * 
	 * @param $values associative array with values from form
	 */
	function insertPage($values)
	{
		global $wgRequest, $wgUser;
		$author = $wgUser->getName();
		$wikititle = vfGetPageTitle($values['titleorquestion']);

		try
		{
			$surveyDAO = new SurveyDAO();
			$page = $this->generatePageVO($values);
			$databaseWritten= $surveyDAO->insertPage($page, true);
			if(! $databaseWritten)
				throw new Exception("Error while writing to voting database.");
		}
		catch( Exception $e )
		{
			return '<li>'.$e->getMessage().'</li>';
		}
		
		global $gvSurveyTemplate;
		$wikiText.='{{#'.$gvSurveyTemplate.':'. $page->getPageID() .'}}';
		$wikiText.="\n*Created by ~~~~\n[[Category:Surveys]]\n";
		$wikiText.="[[Category:Surveys by $author]]\n[[Category:Simple Surveys]]\n";
		
		if(strlen($values[category]) > 5)
			$wikiText.="[[Category:$values[category]]]\n";
		
		$this->insertWikiPage($wikititle, $wikiText, true);
		
		//Add an appropriate hidden category, don't show in recent changes
		$category = new CategoryPage( Title::newFromText(wfMsg('cat-survey-name', $page->getPageID())));
		$category->doEdit('__HIDDENCAT__','Hidden category.', EDIT_NEW | EDIT_SUPPRESS_RC);
	}
	/**
	 * Insert wiki page, optionaly resolve duplicates
	 * 
	 * @param $newtitle Title of wiki page
	 * @param $wikiText text which will be written to wiki page
	 * @param $resolveDuplicates Should script rename page if it already exists
	 * @return error string if there are duplicates
	 */
	function insertWikiPage($newtitle, $wikiText, $resolveDuplicates = false)
	{
		if($resolveDuplicates)
		{
			$i = 1;
			$this->wikiPageTitle = $newtitle;
			$error = $this->insertWikiPage($this->wikiPageTitle, $wikiText, false);
			while($error)
			{
				$i++; //$this->wikiPageTitle 
				$this->wikiPageTitle = $newtitle." ($i)";
				$error = $this->insertWikiPage($this->wikiPageTitle, $wikiText, false);
			}
			return;
		}
		
		$article = new Article( Title::newFromText( $newtitle ) );
		$status = $article->doEdit($wikiText,'Creating a new simple survey', EDIT_NEW);
		if($status->hasMessage('edit-already-exists'))
		{
			return '<li>Wiki page / Article already exists</li>';
		}
		if(!$status->isGood())
		{
			throw new Exception('Error has occured while creating a new page');
		}
	}
	/**
	 * Mandatory execute function for a Special Page
	 * 
	 * @param $par
	 */
	function execute( $par = null )
	{
		global $wgUser, $wgTitle, $wgOut;
		$wgOut->setArticleBodyOnly(false);
		if ( $wgUser->isAnon() ) {
			$wgOut->showErrorPage( 'surveynologin', 'surveynologin-desc', array($wgTitle->getPrefixedDBkey()) );
			return;
		}
		
		global $wgRequest, $wgParser;
		if($wgRequest->getVal('wpSubmit') == wfMsg('create-survey'))
		{
			//user has submitted to add new page or edit existing one
			//form originates from the CreateSurvey special page
			if ( !$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) ) )
				die('Edit token is wrong, please try again. Edit token is missing');
			$this->form->loadValuesFromRequest();
			$error = $this->Validate();
			if(! $error)
			{
				$error = $this->insertPage($this->form->getValuesArray());
				if(! $error)
				{
					$titleObj = Title::newFromText( $this->wikiPageTitle );					
					$wgOut->redirect($titleObj->getLocalURL(), 302);
					return;
				}
			}
			$this->preDrawForm();
			$this->drawFormNew($error);
		}
		else if( $wgRequest->getVal('wpEditButton') == wfMsg('edit-survey'))
		{
			//user wants to edit the existing survey
			$this->returnTo = htmlspecialchars_decode( $wgRequest->getVal('returnto') );
			$page_id = intval($wgRequest->getVal('id'));
			
			try
			{
				$surveydao = new SurveyDAO();
				$page = $surveydao->findByPageID( $page_id );
			}
			catch(SurveyException $e)
			{
				$wgOut->setPageTitle("Error");
				$wgOut->addWikiText( vfErrorBox( 'No such page identifier (id)') );
				$wgOut->returnToMain();
				return;
			}
			if($page->getStatus() != 'ready')
			{
				$wgOut->setPageTitle("Error");
				$wgOut->addWikiText( vfErrorBox( 'Survey is either active or finished, therefore cannot be edited.') );
				$wgOut->returnToMain();
				return;
			}

			$this->form->setValue('titleorquestion', $page->getTitle());
			$survey = $page->getSurveys();
			$surchoice = $survey[0]->getChoices();
			$choices='';
			foreach($surchoice as $ch)
			{
				if($choices) $choices .= "\r";
				$choices .= $ch->getChoice();
			}
			$this->form->setValue('choices', $choices);
			$this->form->setValue('duration', $page->getDuration());
			$this->form->setValue('showresultsend', ! (bool) $page->isShowGraph());
			$this->form->setValue('showtop', $page->getDisplayTop());
			$this->form->setValue('privacy', $page->getPrivacyByName());
			$this->form->setValue('phonevoting', $page->getPhoneVoting());
			$this->form->setValue('webvoting', $page->getWebVoting());
			
			$this->preDrawForm();
			$this->drawFormEdit($page_id, $error);
		}
		else //user has submitted to add new page or edit existing one
		if($wgRequest->getVal('wpSubmit') == wfMsg('edit-survey'))
		{
		    if ( !$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) ) ) {
				die('Edit token is wrong, please try again.');
			}
			$this->returnTo = htmlspecialchars_decode( $wgRequest->getVal('returnto') );
			$page_id = intval($wgRequest->getVal('id'));
			
			$this->form->loadValuesFromRequest();
			
			$error = $this->Validate();
			if(! $error)
			{
				$page = $this->generatePageVO($this->form->getValuesArray());
				$page->setPageID($page_id);
				
				try{
					$surveydao = new SurveyDAO();
					$surveydao->updatePage($page);
				}
				catch(SurveyException $e)
				{
					$wgOut->addWikiText( vfErrorBox( $e->getMessage() ) );
					return;
				}
				
				//Purge all pages that have this survey included.
				vfAdapter()->purgeCategoryMembers(wfMsg('cat-survey-name', $page_id));
		
				$title = Title::newFromText($this->returnTo);
				$wgOut->redirect($title->escapeLocalURL(), 302);
				return;
			}
			$this->preDrawForm();
			$this->drawFormEdit($page_id, $error);
		}
		else
		{
			//fresh new form
			$this->preDrawForm();
			$this->drawFormNew();
		}
	}
	function Validate()
	{
		$error = $this->form->Validate();
		if($this->form->getValue('phonevoting') == 'no' && $this->form->getValue('webvoting') == 'no')
			$error .= '<li>Users cannot vote, enable either web or phone voting</li>';
		return $error;
	}
	function preDrawForm()
	{
		global $wgOut, $gvScript;
		$wgOut->setArticleFlag(false);
		$wgOut->addHTML('<script type="text/javascript" src="'.$gvScript.'/prefs.js"></script>');
		$wgOut->addHTML('<script type="text/javascript" src="'.$gvScript.'/jquery-1.4.2.min.js"></script>');
	}
	/**
	 * Draw form for new survey using FormControl
	 * 
	 * @param $errors string containing potential errors
	 */
	function drawFormNew( $errors=null )
	{
		global $wgOut, $wgUser;
		$wgOut->setPageTitle("Create New Simple Survey");
		if($errors)	$wgOut->addWikiText( vfErrorBox( '<ul>'.$errors.'</ul>') );
		$crform = Title::newFromText('Special:CreateSurvey');
		$this->form->StartForm( $crform->escapeLocalURL(), 'mw-preferences-form' );
		
		$this->form->AddPage ( 'New Survey', array('titleorquestion', 'choices', 'category', 'label-details') );
		$this->form->AddPage ( 'Voting options', array('privacy', 'duration', 'phonevoting','webvoting' ) );
		$this->form->AddPage ( 'Graphing', array('showresultsend', 'showtop') );
		$this->form->EndForm(wfMsg('create-survey'));
	}
	/**
	 * Draw form for editing surveys using FormControl
	 * 
	 * @param $page_id
	 * @param $errors string containing potential errors
	 */
	function drawFormEdit( $page_id, $errors=null )
	{
		$this->formitems['titleorquestion']['explanation'] = '';
		$this->formitems['titleorquestion']['learn_more'] = '';

		global $wgOut, $wgUser, $gvScript;
		$wgOut->setPageTitle(wfMsg('title-edit-survey'));
		
		$wgOut->returnToMain();
		if($errors)	$wgOut->addWikiText( vfErrorBox( '<ul>'.$errors.'</ul>') );
		
		$crform = Title::newFromText('Special:CreateSurvey');
		$this->form->StartForm( $crform->escapeLocalURL(), 'mw-preferences-form' );
		
		$wgOut->addHTML('<input type="hidden" name="id" value="'.$page_id.'">');
		$wgOut->addHTML('<input type="hidden" name="returnto" value="'.htmlspecialchars($this->returnTo).'">');
		
		$this->form->AddPage ( 'Edit Survey', array('titleorquestion', 'titlewarning' , 'choices', 'label-details') );
		$this->form->AddPage ( 'Voting options', array('privacy', 'duration', 'phonevoting','webvoting' ) );
		$this->form->AddPage ( 'Graphing', array('showresultsend', 'showtop') );
		$this->form->EndForm(wfMsg('edit-survey'));
		
	}
}// End of class CreateSurvey

?>