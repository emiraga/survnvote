<?php
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpEmirTest";
require_once( "$IP/extensions/votapedia/FormFunctions.php" );
require_once( "$IP/includes/Article.php" );

function wfExtensionSpEmirTest() {
	global $IP, $wgMessageCache;

	require_once( "$IP/includes/SpecialPage.php" );

	// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
	// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
	// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
	// 'allpages' => 'All Pages';
	// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('emirtest' => 'Emir Test'));

class SpEmirTestPage extends SpecialPage {
	public function __construct() {
		parent::__construct( 'EmirTest' );
		$formpages = array();
		//first page
		$formpages[] = array( 'New survey',
			array (
				array(
					'type' => 'input',
					'name' => 'Title or question',
					'default' => '',
					'valid' => function($v,$js){ if($js) return "alert(document.location);"; return strlen($v) > 1; },
					'explanation' => 'e.g. "What is the capital of Australia?". This will be the title of your survey page.'
					.'The following characters are not allowed in the title: #, +, &, <, >, [, ], {, }, |, / .',
					'learn_more' => 'Details_of_Title_or_Survey_Question',
					'process' => FormGenerator::RemoveSpecialChars,
				),
				array(
					'type' => 'select',
					'name' => 'Category',
					'default' => 'General',
					'valid' => function($v,$js){ if($js) return "alert(document.location);"; return $v != 'General'; },
					'explanation' => 'Your survey then would be added into the chosen category, and would be listed under that category.',
					'learn_more' => 'Details_of_Survey_Category',
					'options' => array(
						  "Select"=>"General",  "Engineering"=>"Engineering",
						  "Science"=>"Science",  "Health"=>"Health",
						  "Environment"=>"Environment",  "Politics"=>"Politics",
						  "Economy"=>"Economy",  "Art"=>"Art",
						  "Sport" => "Sport", )
				),
				array(
					'type' => 'textarea',
					'name' => 'Choices',
					'textbefore' => 'Type choices here, one per line.<br />',
					'valid' => function($v,$js){ if($js) return "alert(document.location);"; return strlen($v) > 1; },
					'explanation' => 'The choices can contain wiki markup language and you can add, delete or modify them later in the survey page.',
					'learn_more' => 'Details_of_Editing_Surveys',
				),
				array(
					'type' => 'null',
					'explanation' => 'Once you start the survey, each choice will be assigned with a telephone number, audiences can ring this number, send SMS or visit the survey page to enter their vote.',
					'learn_more' => 'Details_of_Survey_Procedure',
				),
			)
		);
		//second page
		$formpages[] = array( 'Timing information',
			array (
				array(
					'type' => 'input',
					'name' => 'Duration',
					'default' => '1',
					'width' => '10',
					'textafter' => ' hours.',
					'valid' => function($v,$js){ if($js) return "alert(document.location);"; $v=intval($v); return $v > 0 && $v < 11; },
					'explanation' => 'Once you start the survey, it will run for this amount of time and stop automatically.',
					'learn_more' => 'Details_of_Duration',
					'process' => FormGenerator::ConvertToInt,
				)
			)
		);
		//third page
		$formpages[] = array( 'Voting options',
			array (
				array(
					'type' => 'select',
					'name' => 'Phone voting',
					'default' => 'Enable anonymous phone voting',
					'valid' => function($v,$js){ if($js) return ""; return $v == "yes-anon" or $v == "yes-local" or $v == "no"; },
					'explanation' => '',
					'learn_more' => 'Details_of_Phone_Voting',
					'options' => array(
						  "Enable anonymous phone voting"=>"yes-anon",
						  "Enable phone voting (only local callers)"=>"yes-local",
						  "Disable phone voting"=>"no",)
				),
				array(
					'type' => 'select',
					'name' => 'SMS voting',
					'default' => 'Enable anonymous SMS voting',
					'valid' => function($v,$js){ if($js) return ""; return $v == "yes-anon" or $v == "yes-local" or $v == "no"; },
					'explanation' => '',
					'learn_more' => 'Details_of_SMS_Voting',
					'options' => array(
						  "Enable anonymous SMS voting"=>"yes-anon",
						  "Enable SMS voting (only local callers)"=>"yes-local",
						  "Disable SMS voting"=>"no",)
				),
				array(
					'type' => 'select',
					'name' => 'Web voting',
					'default' => 'Enable anonymous WEB voting',
					'valid' => function($v,$js){ if($js) return ""; return $v == "yes-anon" or $v == "yes-local" or $v == "no"; },
					'explanation' => '',
					'learn_more' => 'Details_of_Web_Voting',
					'options' => array(
						  "Enable anonymous Web voting"=>"yes-anon",
						  "Enable Web voting (only for registered users)"=>"yes-local",
						  "Disable Web voting"=>"no",)
				),
			),
		);
		//fourth page
		$formpages[] = array( 'Graphing',
			array (
				array(
					'type' => 'checkbox',
					'name' => 'Graph Options',
					'default' => 'on',
					'checklabel' => ' Show results of voting only at the end. ',
					'valid' => function($v,$js){ if($js) return ""; return true; },
					'explanation' => 'If checked, the survey result will only be shown after the survey finishes. Otherwise, voters will see the partial result after they vote.',
					//'learn_more' => 'Details_of_Anonymous_Voting',
				),
				array(
					'type' => 'input',
					'name' => 'Show only top',
					'default' => '',
					'width' => '10',
					'textbefore' => 'Show only top ',
					'textafter' => ' choices on the graph.',
					'valid' => function($v,$js){ if($js) return "alert(document.location);"; $v=intval($v); return $v >= 0 and $v < 1000; },
					'explanation' => 'If a number is specified, the graph will only display the top few choices on the graph. Otherwise, voters will see all the choices no matter how many votes they have got.',
					//'learn_more' => 'Details_of_Duration',
					'process' => FormGenerator::ConvertToInt,
				)
			)
		);
		$this->form = new FormGenerator($formpages);
	}
	
	function SpEmirTestPage() {
		SpecialPage::SpecialPage( 'EmirTest' );
		$this->includable( true );
	}
	
	function insertPage()
	{
		$article = new Article( Title::newFromText( 'Test' ) );
		$status = $article->doEdit("new text\n== title ==\nha ha ha? ~~~~",'from the extension',EDIT_NEW);
		echo '<pre>';
		if($status->hasMessage('edit-already-exists'))
			die('Already exists');
		if(!$status->isGood())
			die('Something is wrong');
	}
	
	function execute( $par = null )
	{
		global $wgRequest;
		if($wgRequest->getVal('wpSubmit'))
		{
			$error = $this->form->Validate();
			if(! $error)
			{
				$this->insertPage();
				die('');
			}
			$this->form->getDefaultsFromRequest();
		}
		$this->drawForm($error);
	}
	
	function drawForm( $errors=null )
	{
		global $wgOut, $wgTitle;
		global $wgUser, $wgLang;
		$wgOut->setArticleFlag(false);
		$wgOut->setPageTitle("Emir Test");
		$wgOut->addScriptFile('prefs.js');

		$userName=$wgUser->getName();
		$this->skin = $wgUser->getSkin();

		if ( $wgUser->isAnon() ) {
			$wgOut->showErrorPage( 'prefsnologin', 'prefsnologintext', array($wgTitle->getPrefixedDBkey()) );
			return;
		}

		if($errors)
		{
			$wgOut->addWikiText( '<div class="errorbox"><strong><ul>' . $errors . '</ul></strong></div>' );
		}
		$titleObj = SpecialPage::getTitleFor( 'EmirTest' );
		$this->form->StartForm( $titleObj->getLocalUrl(), 'mw-preferences-form' );
		
		if(!$wgUser->isLoggedIn())
			$userName="NULL";
		$this->form->AddPages($this->formpages);
		$this->form->EndForm('Create Survey');
	}//end function execute
}//end class SpEmirTestPage

SpecialPage::addPage( new SpEmirTestPage );
}
?>