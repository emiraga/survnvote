<?php
if (!defined('MEDIAWIKI')) die();

$wgExtensionFunctions[] = "wfExtensionSpEmirTest";
require_once( "$IP/extensions/votapedia/FormGenerator.php" );
require_once( "$IP/includes/Article.php" );
require_once( "$IP/extensions/votapedia/DAO/surveyDAO.php" );

function wfExtensionSpEmirTest() {
	global $IP, $wgMessageCache;

	require_once( "$IP/includes/SpecialPage.php" );

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
			global $wgRequest, $wgOut, $wgUser;
			$wgOut->setArticleFlag(false);
			$wgOut->setPageTitle("Emir Test");

			$userName=$wgUser->getName();
			$this->skin = $wgUser->getSkin();
			
			/*for($i=1;$i<=99;$i++)
			{
				$article = new Article( Title::newFromText( "Test question 1 ($i)" ) );
				$article->doDeleteArticle('Not needed');
			}*/
			
			$wgOut->addHTML('Emir test '.$userName.'<br><div id="response"></div>');
			$wgOut->addHTML(
			"<script><!-- 
			sajax_do_call( 'vp_Test1', ['jedan', 'dva'], function(o) { document.getElementById('response').innerHTML = o.responseText; } );
			//--></script>");
			
			$article = new Article( Title::newFromText( "Sandbox" ) );
			$editor = new EditPage( $article );
			#$wgOut->addHTML('<script type="text/javascript" src="/new/skins/common/edit.js?207"></script>');
			#$editor->showEditForm();
		}
	}//end class SpEmirTestPage

	SpecialPage::addPage( new SpEmirTestPage );
}

$wgAjaxExportList[] = 'vp_Test1';
function vp_Test1($a, $b)
{
	return "This is from PHP. ".$a.' + '.$b;
}

?>