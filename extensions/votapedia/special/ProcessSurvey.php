<?php
if (!defined('MEDIAWIKI')) die();

global $gvPath;
require_once("$gvPath/Common.php" );
require_once("$gvPath/FormControl.php");
require_once("$gvPath/VO/PageVO.php");
require_once("$gvPath/DAO/SurveyDAO.php");

/**
 * Special page Create Survey
 * 
 * @author Emir Habul
 */
class ProcessSurvey extends SpecialPage {
	/**
	 * Constructor for ProcessSurvey
	 */
	function __construct() {
		parent::__construct('ProcessSurvey');
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
		global $wgUser, $wgTitle, $wgOut, $wgRequest;
		if ( $wgUser->isAnon() ) {
			$wgOut->showErrorPage( 'surveynologin', 'surveynologin-desc', array($wgTitle->getPrefixedDBkey()) );
			return;
		}
		$action = $wgRequest->getVal( 'wpSubmit' );
		echo $action;
		if($action == wfMsg('start-survey'))
		{
			$wgOut->addHTML('start survey<br>');
		}
	}
}