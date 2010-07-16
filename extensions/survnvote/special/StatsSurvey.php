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
require_once("$vgPath/misc/DataWriter.php" );

/**
 * Special page Correlate Survey
 *
 * @author Emir Habul
 * @package ControlSurvey
 */
class CorrelateSurvey extends SpecialPage
{
    /**
     * Constructor for CorrelateSurvey
     */
    function __construct()
    {
        parent::__construct('CorrelateSurvey');
        wfLoadExtensionMessages('Survnvote');
        $this->includable( false );
        $this->setGroup('CorrelateSurvey', 'survnvote');
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
            
            if($par == 'xls')
            {
                $presID = intval($wgRequest->getVal('presid',0));
                if($page->getStatus($presID) != 'ended')
                {
                    throw new Exception('Results are available only for finished surveys');
                }
                $writer = new ExcelWrite('Survnvote_corr_'.$page_id.'_pres_'.$presID.'.xls');
                $data = new SurveyCorrelateData($page, $presID);
                $writer->addSource($data);
                $data = new UsersCorrelateData($page, $presID);
                $writer->addSource($data);
                $writer->write();
                $wgOut->disable();
            }
            else
            {
                $buttons = new SurveyNoButtons();
                $body = new SurveyCorrelations($user, $page, $parser, $page->getCurrentPresentationID());
                $tag = new SurveyView($user, $page, $parser, $buttons, $body);
                $buttons->setType($page->getTypeName());
                $wgOut->addHTML('<i>Note</i>: <a href="http://en.wikipedia.org/wiki/Correlation_does_not_imply_causation">Correlation does not imply causation.</a>');
                $wgOut->addHTML($tag->getHTML(true));
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox('Error: '.$e->getMessage()));
        }
        $wgOut->returnToMain();
        wfProfileOut( __METHOD__ );
    }
}

/**
 * Special page Cross Tabulate survey
 *
 * @author Emir Habul
 * @package ControlSurvey
 */
class CrossTabSurvey extends SpecialPage
{
    /**
     * Constructor for CrossTabSurvey
     */
    function __construct()
    {
        parent::__construct('CrossTabSurvey');
        wfLoadExtensionMessages('Survnvote');
        $this->includable( false );
        $this->setGroup('CrossTabSurvey', 'survnvote');
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
        $wgOut->setPageTitle( wfMsg('title-crosstab-survey') );
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

           
            if($par == 'xls')
            {
                $presID = intval($wgRequest->getVal('presid',0));
                
                if($page->getStatus($presID) != 'ended')
                {
                    throw new Exception('Results are available only for finished surveys');
                }
                
                $writer = new ExcelWrite('Survnvote_crosstab_'.$page_id.'_pres_'.$presID.'.xls');
                $data = CrossTabData::generate($page, $presID);
                foreach ($data as $source)
                {
                    $writer->addSource($source);
                }
                $writer->write();
                $wgOut->disable();
            }
            else
            {
                $buttons = new SurveyNoButtons();
                $body = new SurveyCrossTab($user, $page, $parser, $page->getCurrentPresentationID());
                $tag = new SurveyView($user, $page, $parser, $buttons, $body);
                $buttons->setType($page->getTypeName());
                $wgOut->addHTML($tag->getHTML(true));
            }
        }
        catch(Exception $e)
        {
            $wgOut->addHTML(vfErrorBox('Error: '.$e->getMessage()));
        }
        $wgOut->returnToMain();
        wfProfileOut( __METHOD__ );
    }
}

