<?php
if (!defined('MEDIAWIKI')) die();
/**
 * @package ControlSurvey
 */

/** Include dependencies */
global $vgPath;
require_once("$vgPath/misc/Common.php" );
require_once("$vgPath/misc/UserPermissions.php");
require_once("$vgPath/DAO/UserDAO.php" );
require_once("$vgPath/survey/SurveyBody.php" );
require_once("$vgPath/survey/SurveyButtons.php" );

/**
 * Special page View Survey
 *
 * @author Emir Habul
 * @package ControlSurvey
 */
class CorrelateSurvey extends SpecialPage
{
    /**
     * Constructor for ProcessSurvey
     */
    function __construct()
    {
        parent::__construct('CorrelateSurvey');
        wfLoadExtensionMessages('Votapedia');
        $this->includable( false );
        $this->setGroup('CorrelateSurvey', 'votapedia');
    }
    /**
     * Mandatory execute function for a Special Page
     *
     * @param String $par
     */
    function execute( $par = null )
    {
        wfProfileIn( __METHOD__ );

        global $wgOut, $wgParser, $wgRequest, $vgScript;
        $wgOut->setPageTitle( wfMsg('title-correlate-survey') );
        $wgOut->setArticleFlag(false);

        $userdao = new UserDAO();
        try
        {
            $page_id = intval($wgRequest->getVal('id'));
            $parser = new MwParser($wgParser, $wgOut->ParserOptions());
            $wgOut->addStyle($vgScript.'/survey.css');
            $user = vfUser()->getUserVO();
            $pagedao = new PageDAO();
            $page =& $pagedao->findByPageID( $page_id );
            $buttons = new SurveyNoButtons();
            $body = new SurveyCorrelations($user, $page, $parser, $page->getCurrentPresentationID());
            $tag = new SurveyView($user, $page, $parser, $buttons, $body);
            $buttons->setType($page->getTypeName());
            $wgOut->addHTML($tag->getHTML(true));
            $wgOut->returnToMain();
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox('Error: '.$e->getMessage()));
        }
        wfProfileOut( __METHOD__ );
    }
}

