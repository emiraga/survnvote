<?php
if (!defined('MEDIAWIKI')) die();
$wgExtensionFunctions[] = "wfExtensionSpEmirTest";
require_once( "$IP/extensions/votapedia/FormGenerator.php" );
require_once( "$IP/includes/Article.php" );
require_once( "$IP/extensions/votapedia/survey/surveyDAO.php" );

function wfExtensionSpEmirTest() {
	global $IP, $wgMessageCache;

	require_once( "$IP/includes/SpecialPage.php" );

	// Here you should define the article name that contains the Special Page's Title as shown in [[Special:Specialpages]]
	// Where 'specialpagename' will be MediaWiki:<specialpagename> eg. Special:Allpages might be 'allpages'
	$wgMessageCache->addMessages(array('emirtest' => 'Emir Test'));

	class SpEmirTestPage extends SpecialPage {
	
		public function __construct() {
			parent::__construct( 'EmirTest' );
		}
		
		function SpEmirTestPage() {
			SpecialPage::SpecialPage( 'EmirTest' );
			$this->includable( true );
		}
		
		function execute( $par = null )
		{
			global $wgUser, $wgOut, $wgTitle;
			if ( $wgUser->isAnon() ) {
				$wgOut->showErrorPage( 'prefsnologin', 'prefsnologintext', array($wgTitle->getPrefixedDBkey()) );
				return;
			}
			global $wgHooks;
			
			echo '<pre>';
			var_dump($wgHooks);exit;
			
			global $wgRequest;
			$wgOut->setArticleFlag(false);
			$wgOut->setPageTitle("Emir Test");

			$userName=$wgUser->getName();
			$this->skin = $wgUser->getSkin();
			
			for($i=1;$i<=30;$i++)
			{
				$article = new Article( Title::newFromText( "Test Question ".$i ) );
				$article->doDeleteArticle('Not needed');
			}
			
			$wgOut->addHTML('Emir test '.$userName.'<br><div id="response"></div>');
			$wgOut->addHTML(
			"<script><!-- 
			sajax_do_call( 'vp_Test1', ['jedan', 'dva'], function(o) { document.getElementById('response').innerHTML = o.responseText; } );
			//--></script>");
		}

	}//end class SpEmirTestPage

	SpecialPage::addPage( new SpEmirTestPage );
}

$wgAjaxExportList[] = 'vp_Test1';
function vp_Test1($a, $b)
{
	return "This is from PHP.".$a.$b;
}

?>