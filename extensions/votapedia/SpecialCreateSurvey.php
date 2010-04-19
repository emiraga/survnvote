<?php
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpCreateSurvey";


function wfExtensionSpCreateSurvey() {
	global $IP, $wgMessageCache;
	require_once( "$IP/includes/SpecialPage.php" );

	// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
	// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
	// The part after '=>' is the default value of the title so again, using Special:Allpages as an example you would have...
	// 'allpages' => 'All Pages';
	// the part BEFORE the => must be all Lowercase.
	$wgMessageCache->addMessages(array('createsurvey' => 'Create Survey'));

	class SpCreateSurveyPage extends SpecialPage {
	
		public function __construct() {
			parent::__construct( 'CreateSurvey' );
			$this->formitems = array (
				'title' => array(
					'type' => 'input',
					'name' => 'Title or question',
					'default' => '',
					'valid' => function($v,$i,$js){ if($js) return "alert(document.location);"; return strlen($v) > 1; },
					'explanation' => 'e.g. "What is the capital of Australia?". This will be the title of your survey page.'
					.'The following characters are not allowed in the title: #, +, &, <, >, [, ], {, }, |, / .',
					'learn_more' => 'Details_of_Title_or_Survey_Question',
					'process' => function($v) { return FormControl::RemoveSpecialChars($v); },
				),
				'choices' => array(
					'type' => 'textarea',
					'name' => 'Choices',
					'textbefore' => 'Type choices here, one per line.<br />',
					'valid' => function($v,$i,$js){ if($js) return "alert(document.location);"; return strlen($v) > 1; },
					'explanation' => 'The choices can contain wiki markup language and you can add, delete or modify them later in the survey page.',
					'learn_more' => 'Details_of_Editing_Surveys',
				),
				'category' => array(
					'type' => 'select',
					'name' => 'Category',
					'default' => 'General',
					'valid' => function($v,$i,$js){ if($js) return "alert(document.location);"; return in_array( $v, $i['options'] ); },
					'explanation' => 'Your survey then would be added into the chosen category, and would be listed under that category.',
					'learn_more' => 'Details_of_Survey_Category',
					'options' => array(
						  "General"=>"General",  "Engineering"=>"Engineering",
						  "Science"=>"Science",  "Health"=>"Health",
						  "Environment"=>"Environment",  "Politics"=>"Politics",
						  "Economy"=>"Economy",  "Art"=>"Art",
						  "Sport" => "Sport", )
				),
				'label1' => array(
					'type' => 'null',
					'explanation' => 'Once you start the survey, each choice will be assigned with a telephone number, audiences can ring this number, send SMS or visit the survey page to enter their vote.',
					'learn_more' => 'Details_of_Survey_Procedure',
				),
				'duration' => array(
					'type' => 'input',
					'name' => 'Duration',
					'default' => '1',
					'width' => '10',
					'textafter' => ' hours.',
					'valid' => function($v,$i,$js){ if($js) return "alert(document.location);"; $v=intval($v); return $v > 0 && $v < 11; },
					'explanation' => 'Once you start the survey, it will run for this amount of time and stop automatically.',
					'learn_more' => 'Details_of_Duration',
					'process' => function($v) { return intval($v); },
				)
				'phonevoting' => array(
					'type' => 'select',
					'name' => 'Phone voting',
					'default' => 'Enable anonymous phone voting',
					'valid' => function($v,$i,$js){ if($js) return ""; return $v == "yes-anon" or $v == "yes-local" or $v == "no"; },
					'explanation' => '',
					'learn_more' => 'Details_of_Phone_Voting',
					'options' => array(
						  "Enable anonymous phone voting"=>"yes-anon",
						  "Enable phone voting (only local callers)"=>"yes-local",
						  "Disable phone voting"=>"no",)
				),
				'smsvoting' => array(
					'type' => 'select',
					'name' => 'SMS voting',
					'default' => 'Enable anonymous SMS voting',
					'valid' => function($v,$i,$js){ if($js) return ""; return $v == "yes-anon" or $v == "yes-local" or $v == "no"; },
					'explanation' => '',
					'learn_more' => 'Details_of_SMS_Voting',
					'options' => array(
						  "Enable anonymous SMS voting"=>"yes-anon",
						  "Enable SMS voting (only local callers)"=>"yes-local",
						  "Disable SMS voting"=>"no",)
				),
				'webvoting' => array(
					'type' => 'select',
					'name' => 'Web voting',
					'default' => 'Enable anonymous WEB voting',
					'valid' => function($v,$i,$js){ if($js) return ""; return $v == "yes-anon" or $v == "yes-local" or $v == "no"; },
					'explanation' => '',
					'learn_more' => 'Details_of_Web_Voting',
					'options' => array(
						  "Enable anonymous Web voting"=>"yes-anon",
						  "Enable Web voting (only for registered users)"=>"yes-local",
						  "Disable Web voting"=>"no",)
				),
				'show_results_end' => array(
					'type' => 'checkbox',
					'name' => 'Graph Options',
					'default' => 'on',
					'checklabel' => ' Show results of voting only at the end. ',
					'valid' => function($v,$i,$js){ if($js) return ""; return true; },
					'explanation' => 'If checked, the survey result will only be shown after the survey finishes. Otherwise, voters will see the partial result after they vote.',
					//'learn_more' => 'Details_of_Anonymous_Voting',
				),
				'show_top' => array(
					'type' => 'input',
					'name' => 'Show only top',
					'default' => '',
					'width' => '10',
					'textbefore' => 'Show only top ',
					'textafter' => ' choices on the graph.',
					'valid' => function($v,$i,$js){ if($js) return "alert(document.location);"; $v=intval($v); return $v >= 0 and $v < 1000; },
					'explanation' => 'If a number is specified, the graph will only display the top few choices on the graph. Otherwise, voters will see all the choices no matter how many votes they have got.',
					//'learn_more' => 'Details_of_Duration',
					'process' => function($v) { return intval($v); },
				),
			);
			$this->form = new FormControl($this->formitems);
		}
		
		function SpCreateSurveyPage() {
			SpecialPage::SpecialPage( 'CreateSurvey' );
			$this->includable( true ); //we can include this from other pages
		}
		
		function insertPage($values)
		{
			global $wgRequest;
			global $wgUser;
			
			$title =  $values['Titleorquestion'];
			$Category = $values['Category'];
			$Choices = $values['Choices'];
			$Duration = $values['Duration'];
			$Phonevoting = $values['Phonevoting'];
			$SMSvoting = $values['SMSvoting'];
			$Webvoting = $values['Webvoting'];
			$GraphOptions = $values['GraphOptions'];
			$Showonlytop = $values['Showonlytop'];
	
			$wikiText='';
			$title = stripslashes($title);
			$wikiText.="===$title===\n";
			if(strlen($title)>60)
			{
				$title=substr($title,0,60);
				$title.='...';
			}
			$encodedTitle=urlencode($title);
			
			/* TODO
			$mobilePhone = 'null';
			if(isset($_POST["mobileNumber"]))
			{
				$mobilePhone = $_POST["mobileNumber"];
				setcookie ('mobileNumber', $mobilePhone, time() + (365*60*60*24),'/');
			}
	
			$isSMSRequired = 'no';
			if(isset($_POST["SMSRequired"]))
			{
				if($_POST["SMSRequired"]!='no')
					$isSMSRequired = 'yes';
			}*/
			$author = $wgUser->getName();
			
			/*$article = new Article( Title::newFromText( 'Test' ) );
			$status = $article->doEdit("new text\n== title ==\nha ha ha? ~~~~",'from the extension',EDIT_NEW);
			echo '<pre>';
			if($status->hasMessage('edit-already-exists'))
				return 'Article Already exists';
			if(!$status->isGood())
				return 'Error has occured while creating a new page';
			*/
			//create a new Page
			$page = new PageVO();
			$page->setTitle($encodedTitle);
			$page->setAuthor($author);
			//Write data into Database
			$surveyDAO = new SurveyDAO();
			$databaseWritten=true;
			if(!$surveyDAO->insertPage($page))
			{   $databaseWritten=false;
			}
			echo $databaseWritten;
			print '<pre>';
				print_r($page);
				die('');
		}
		
		function execute( $par = null )
		{
			global $wgUser;
			if ( $wgUser->isAnon() ) {
				$wgOut->showErrorPage( 'prefsnologin', 'prefsnologintext', array($wgTitle->getPrefixedDBkey()) );
				return;
			}
			global $wgRequest;
			if($wgRequest->getVal('wpSubmit'))
			{
			    if ( !$wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) ) ) {
					die('Something is wrong, please try again.');
				}
				$this->form->getValuesFromRequest();
				
				$error = $this->form->Validate();
				if(! $error)
				{
					$error = $this->insertPage($this->form->values);
					if(! $error)
					{
						die('success');
					}
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
			$wgOut->setPageTitle("Create New Simple Survey");
			$wgOut->addScriptFile('prefs.js');
	
			$userName=$wgUser->getName();
			$this->skin = $wgUser->getSkin();
	
			if($errors)
			{
				$wgOut->addWikiText( '<div class="errorbox"><strong><ul>' . $errors . '</ul></strong></div>' );
			}
			$titleObj = SpecialPage::getTitleFor( 'CreateSurvey' );
			$this->form->StartForm( $titleObj->getLocalUrl(), 'mw-preferences-form' );

			$this->form->AddPage ( 'New Survey', array() );
			$this->form->AddPage ( 'Timing', array() );
			$this->form->AddPage ( 'Voting', array() );
			$this->form->AddPage ( 'Graphing', array() );

			$this->form->EndForm('Create Survey');
		}//end function execute
	}//end class SpCreateSurveyPage
	SpecialPage::addPage( new SpCreateSurveyPage );
}
?>